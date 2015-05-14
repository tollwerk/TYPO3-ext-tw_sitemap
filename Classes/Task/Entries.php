<?php

namespace Tollwerk\TwSitemap\Task;

/***************************************************************
 *  Copyright notice
 *
 *  Copyright © 2015 Dipl.-Ing. Joschi Kuphal (joschi@tollwerk.de)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Planer-Task zur Erzeugung von XML-Sitemap-Einträgen
 *
 * @package tw_sitemap
 * @author Dipl.-Ing. Joschi Kuphal <joschi@tollwerk.de>
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class Entries extends \Tollwerk\TwSitemap\Task\AbstractTask implements \TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface {
	/**
	 * Basis-URL für Sitemap-Eintragsabfrage
	 * 
	 * @var array
	 */
	protected $_baseUrl;
	/**
	 * TypoScript service
	 * 
	 * @var unknown
	 */
	protected $_typoscriptService = null;
	/**
	 * Konfigurationstypen
	 * 
	 * @var array
	 */
	protected static $_configTypes = array(
		self::CONFIG_TYPE_FILE			=> '_generateFileEntries',
		self::CONFIG_TYPE_PLUGIN		=> '_generatePluginEntries',
		self::CONFIG_TYPE_TYPOSCRIPT	=> '_generateTyposcriptEntries',
	);
	/**
	 * Aktueller Durchlaufszeitpunkt
	 * 
	 * @var int
	 */
	protected $_cycle = null;
	/**
	 * Konfigurationstyp: Typoscript
	 *
	 * @var string
	 */
	const CONFIG_TYPE_TYPOSCRIPT = 'typoscript';
	/**
	 * Konfigurationstyp: Datei
	 *
	 * @var string
	 */
	const CONFIG_TYPE_FILE = 'file';
	/**
	 * Konfigurationstyp: Plugin
	 *
	 * @var string
	 */
	const CONFIG_TYPE_PLUGIN = 'plugin';
	
	/************************************************************************************************
	 * ÖFFENTLICHE METHODEN
	 ***********************************************************************************************/
	
	/**
	 * Ausführen der Synchronisation
	 * 
	 * @see tx_scheduler_Task::execute()
	 */
	public function execute() {
		
		// Ermitteln des TypoScript-Setups für Sitemap-Einträge
		$_GET['id']									= intval($this->root);
		$setup										= \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Configuration\\BackendConfigurationManager')->getTypoScriptSetup();
		$settings									= $setup['plugin.']['tx_twsitemap.']['settings.'];
		$entriesSetup								= $settings['entries.'];
		
		// Prüfen, ob ein Sprachparameter gültig definiert ist
		if (!array_key_exists('lang', $settings) || !strlen(trim($settings['lang']))) {
			throw new \Exception('Invalid language parameter definition');
			return false;
		}
		
		// Prüfen, ob ein Basis-URL gültig definiert ist
		if (!array_key_exists('baseUrl', $settings) || !strlen(trim($settings['baseUrl']))) {
			throw new \Exception('Invalid base URL definition');
			return false;
		}
		
		if (count($entriesSetup)) {
			$this->_typoscriptService				= \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Service\\TypoScriptService');
			$this->_cycle							= time();
			
			// Bestimmen des Basis-URL
			$this->_baseUrl							= (array)parse_url($settings['baseUrl']);
			if (!array_key_exists('scheme', $this->_baseUrl)) {
				$this->_baseUrl['scheme']			= 'http';
			}
			$this->_baseUrl['path']					= '/index.php';
			parse_str(array_key_exists('query', $this->_baseUrl	) ? $this->_baseUrl['query'] : '', $this->_baseUrl['query']);
			$this->_baseUrl['query']['no_cache']	= 1;

			// Durchlaufen aller Sitemap-Eintragsdefinitionen
			foreach ($entriesSetup as $key => $value) {
				// Erzeugen von Einträgen anhand dieser Konfiguration
				$this->_generateEntries(trim($key, '.'), $value, $setup['plugin.']['tx_twsitemap.']['settings.']);
			}
		}
		
		return true;
	}
	
	/**
	 * Spezifische Fehlerbehandlung beim fehlgeschlagenen Laden von XML-Daten
	 *
	 * @param int $errno                    Fehlernummer
	 * @param string $errstr                Fehlerbeschreibung
	 * @param string $errfile               Datei
	 * @param int $errline                  Fehlerzeile
	 * @return boolean                      FALSE, wenn mit der normalen Fehlerbehandlung fortgefahren werden soll
	 * @throws DOMException                 Wenn es sich um ein ungültiges Dokument handelt
	 */
	public function loadError($errno, $errstr, $errfile, $errline) {
		if (($errno == E_WARNING) && ((substr_count($errstr, "\DOMDocument::loadXML()") > 0) || (substr_count($errstr, "\DOMDocument::load()") > 0))) {
			throw new DOMException($errstr, DOM_VALIDATION_ERR);
		} else {
			return false;
		}
	}
	
	/************************************************************************************************
	 * PRIVATE METHODEN
	 ***********************************************************************************************/
	
	/**
	 * Erzeugen von Einträgen gemäß einer Eintragsdefinition
	 * 
	 * @param string $key				Konfigurationsschlüssel
	 * @param array $config				Eintragsdefinition
	 * @param array $settings			Einstellungen
	 * @return void
	 */
	protected function _generateEntries($key, array $config, array $settings) {

		// Bestimmen der Rendering-Basisseite
		$pid										= array_key_exists('pid', $config) ? intval($config['pid']) : 0;

		// Bestimmen der Eintragsdomain
		$domain										= array_key_exists('domain', $config) ? trim($config['domain']) : null;

		// Abbruch bei fehlenden Parametern
		if (($pid <= 0) || !strlen($domain)) {
			return;
		}

		// Bestimmen des Sprachparameters sowie der zu durchlaufenden Sprachen
		$langParam									= trim($settings['lang']);
		$languages									= (array_key_exists('languages', $config) && strlen(trim($config['languages']))) ? \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', trim($config['languages'])) : array('');
		$locales									= array_pad((array_key_exists('locales', $config) && strlen(trim($config['locales']))) ? \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', trim($config['locales'])) : array(), count($languages), '');
		
		// Bestimmen der Eintragsherkunft
		$origin										= array_key_exists('origin', $config) ? strval($config['origin']) : md5(serialize($config));

		// Bestimmen der Änderungshäufigkeit
		$changefreq									= array_key_exists('changefreq', $config) ? strtolower($config['changefreq']) : \Tollwerk\TwSitemap\Domain\Model\Entry::$changefreqs[\Tollwerk\TwSitemap\Domain\Model\Entry::CHANGEFREQ_NEVER];
		if (!in_array($changefreq, \Tollwerk\TwSitemap\Domain\Model\Entry::$changefreqs)) {
			$changefreq								= \Tollwerk\TwSitemap\Domain\Model\Entry::$changefreqs[\Tollwerk\TwSitemap\Domain\Model\Entry::CHANGEFREQ_NEVER];
		}
		
		// Bestimmen der Priorität
		$priority									= array_key_exists('priority', $config) ? floatval($config['priority']) : 0.5;
		$priority									= max(0, min(1, $priority));
		
		// Bestimmen der Eintragskonfiguration
		$entryType									= array_key_exists('entries', $config) ? strtolower($config['entries']) : null;
		if (strlen($entryType) && array_key_exists($entryType, self::$_configTypes) && array_key_exists('entries.', $config)) {
			$baseUrl								= $this->_baseUrl;
			
			if (array_key_exists('baseUrl', $config)) {
				if (is_string($config['baseUrl'])) {
					$baseUrl						= \TYPO3\CMS\Core\Utility\GeneralUtility::array_merge_recursive_overrule($baseUrl, parse_url($config['baseUrl']));
				}
			} elseif (array_key_exists('baseUrl.', $config)) {
				$baseUrl							= \TYPO3\CMS\Core\Utility\GeneralUtility::array_merge_recursive_overrule($baseUrl, $this->_typoscriptService->convertTypoScriptArrayToPlainArray($config['baseUrl.']));
			}
			
			$baseUrl['query']['id']					= $pid;

			// Durchlaufen aller Sprachen
			foreach ($languages as $languageIndex => $language) {
				if (strlen($language)) {
					$baseUrl['query'][$langParam]	= $language;
				}

				// Delegation an die jeweilige Generierungsmethode
				call_user_func(array($this, self::$_configTypes[$entryType]), $key, $config['entries.'], $baseUrl, $domain, $origin, $changefreq, $priority, $locales[$languageIndex]);
			}
		}
		
		// Löschen aller Einträge der aktuellen Eintragsherkunft, die nicht im aktuellen Durchlauf angelegt oder aktualisiert wurden
		$db								= $GLOBALS['TYPO3_DB'];
		$GLOBALS['TYPO3_DB']->sql_query('DELETE FROM `tx_twsitemap_domain_model_entry` WHERE `origin` = "'.$db->fullQuoteStr($origin, 'tx_twsitemap_domain_model_entry').'" AND `tstamp` < '.$this->_cycle);
	}
	
	/**
	 * Erzeugen von Sitemap-Einträgen anhand einer XML-Datei
	 * 
	 * @param string $key					Konfigurationsschlüssel
	 * @param array $config					Typoscript-Konfiguration
	 * @param string $domain				Sitemap-Domain
	 * @param array $baseUrl				Anfrage-Basis-URL
	 * @param string $defaultOrigin			Standard-Eintragsherkunft
	 * @param string $defaultChangefreq		Standard-Änderungsfrequenz
	 * @param floatval $defaultPriority		Standard-Priorität
	 * @param string $defaultLocale			Standard-Locale
	 * @return void
	 */
	protected function _generateFileEntries($key, array $config, array $baseUrl, $domain, $defaultOrigin, $defaultChangefreq, $defaultPriority, $defaultLocale) {
		if (array_key_exists('path', $config) && strlen(trim($config['path']))) {
			$path				= PATH_site.trim($config['path']);
			if (@is_file($path) && @is_readable($path)) {
				$this->_generateEntriesByXML(@file_get_contents($path), $domain, $defaultOrigin, $defaultChangefreq, $defaultPriority, $defaultLocale);
			}
		}
	}
	
	/**
	 * Erzeugen von Sitemap-Einträgen anhand eines Plugins
	 * 
	 * @param string $key					Konfigurationsschlüssel
	 * @param array $config					Typoscript-Konfiguration
	 * @param string $domain				Sitemap-Domain
	 * @param array $baseUrl				Anfrage-Basis-URL
	 * @param string $defaultOrigin			Standard-Eintragsherkunft
	 * @param string $defaultChangefreq		Standard-Änderungsfrequenz
	 * @param floatval $defaultPriority		Standard-Priorität
	 * @param string $defaultLocale			Standard-Locale
	 * @return void
	 */
	protected function _generatePluginEntries($key, array $config, array $baseUrl, $domain, $defaultOrigin, $defaultChangefreq, $defaultPriority, $defaultLocale) {
		$url									= $baseUrl;
		$url['query']['type']					= 1213;
		$url['query']['tx_twsitemap_sitemap']	= array('plugin' => $key);
		$url									= $url['scheme'].'://'.$url['host'].(array_key_exists('port', $url) ? ':'.$url['port'] : '').'/'.ltrim($url['path'], '/').'?'.http_build_query($url['query']);
		
		if ($this->debug) {
			$this->addMessage(sprintf('Fetching URL: <a href="%s" target="_blank">%s</a>', $url, $url), \TYPO3\CMS\Core\Messaging\FlashMessage::INFO);
		}
		
		$this->_generateEntriesByXML(strval(@file_get_contents($url)), $domain, $defaultOrigin, $defaultChangefreq, $defaultPriority, $defaultLocale);
	}
	
	/**
	 * Erzeugen von Sitemap-Einträgen anhand einer Typoscript-Konfiguration
	 * 
	 * @param string $key					Konfigurationsschlüssel
	 * @param array $config					Typoscript-Konfiguration
	 * @param string $domain				Sitemap-Domain
	 * @param array $baseUrl				Anfrage-Basis-URL
	 * @param string $defaultOrigin			Standard-Eintragsherkunft
	 * @param string $defaultChangefreq		Standard-Änderungsfrequenz
	 * @param floatval $defaultPriority		Standard-Priorität
	 * @param string $defaultLocale			Standard-Locale
	 * @return void
	 */
	protected function _generateTyposcriptEntries($key, array $config, array $baseUrl, $domain, $defaultOrigin, $defaultChangefreq, $defaultPriority, $defaultLocale) {
		$url									= $baseUrl;
		$url['query']['type']					= 1212;
		$url['query']['tx_twsitemap_sitemap']	= array('typoscript' => $key);
		$url									= $url['scheme'].'://'.$url['host'].(array_key_exists('port', $url) ? ':'.$url['port'] : '').'/'.ltrim($url['path'], '/').'?'.http_build_query($url['query']);
		
		if ($this->debug) {
			$this->addMessage(sprintf('Fetching URL: <a href="%s" target="_blank">%s</a>', $url, $url), \TYPO3\CMS\Core\Messaging\FlashMessage::INFO);
		}
		
		$this->_generateEntriesByXML(strval(@file_get_contents($url)), $domain, $defaultOrigin, $defaultChangefreq, $defaultPriority, $defaultLocale);
	}
	
	/**
	 * Erzeugen von Sitemap-Einträgen aus XML-Quelltext
	 * 
	 * 
	 * 
	 * @param array $config					Typoscript-Konfiguration
	 * @param string $domain				Sitemap-Domain
	 * @param string $defaultOrigin			Standard-Eintragsherkunft
	 * @param string $defaultChangefreq		Standard-Änderungsfrequenz
	 * @param floatval $defaultPriority		Standard-Priorität
	 * @param string $defaultLocale			Standard-Locale
	 * @return void
	 */
	protected function _generateEntriesByXML($xml, $domain, $defaultOrigin, $defaultChangefreq, $defaultPriority, $defaultLocale) {
		$xml								= '<entries>'.preg_replace("%^\<\?[^\<]*?%", '', trim($xml)).'</entries>';
		
		// Instanziieren als DOM-Objekt
		set_error_handler(array($this, 'loadError'));
		try {
			$entries						= new \DOMDocument();
			$entries->formatOutput			= true;
			$entries->preserveWhiteSpace	= true;
			$entries->loadXML($xml, LIBXML_NSCLEAN);
			// Wenn ein Fehler auftritt
		} catch(DOMException $e) {
			trigger_error($e->getCode());
			restore_error_handler();
			return;
		}
		restore_error_handler();
		
		// Gründen eines XPath-Prozessors
		$xpath								= new \DOMXPath($entries);
		
		// Vorbereiten der Änderungsfrequenzen
		$changefreqs						= array_flip(\Tollwerk\TwSitemap\Domain\Model\Entry::$changefreqs);
		
		// Durchlaufen aller enthaltenen A-Elemente
		/* @var $entry DOMElement */
		foreach ($xpath->query('//a[@href]') as $entryIndex => $entry) {
			
			// Extrahieren & Normalisieren des Eintrags-URL
			$loc						= trim($entry->getAttribute('href'));
			$locParts					= parse_url($loc);
			$loc						= empty($locParts['port']) ? '' : ':'.$locParts['port'];
			$loc						.= empty($locParts['path']) ? '/' : $locParts['path'];
			$loc						.= empty($locParts['query']) ? '' : '?'.$locParts['query'];
			$loc						.= empty($locParts['fragment']) ? '' : '#'.$locParts['fragment'];
			
			// Extrahieren der übrigen Parameter
			$origin						= trim($entry->getAttribute('data-origin'));
			$origin						= strlen($origin) ? $origin : $defaultOrigin;
			$source						= trim($entry->getAttribute('data-source'));
			$source						= strlen($source) ? $source : $entryIndex;
			$lastmod					= trim($entry->getAttribute('data-lastmod'));
			$lastmod					= strlen($lastmod) ? intval($lastmod) : time();
			$changefreq					= trim($entry->getAttribute('data-changefreq'));
			$changefreq					= strtolower(strlen($changefreq) ? $changefreq : trim($defaultChangefreq));
			$changefreq					= (strlen($changefreq) && array_key_exists($changefreq, $changefreqs)) ? $changefreqs[$changefreq] : \Tollwerk\TwSitemap\Domain\Model\Entry::CHANGEFREQ_NEVER;
			$priority					= trim($entry->getAttribute('data-priority'));
			$priority					= strlen($priority) ? floatval($priority) : $defaultPriority;
			$locale						= trim($entry->getAttribute('data-locale'));
			$locale						= strlen($locale) ? floatval($locale) : $defaultLocale;
			
			// Vorbereiten des Eintragsdatensatzes
			$entry						= array(
				'domain'				=> $domain,
				'origin'				=> $origin,
				'source'				=> $source,
				'loc'					=> $loc,
				'changefreq'			=> $changefreq,
				'priority'				=> $priority,
				'language'				=> $locale,
				'lastmod'				=> $lastmod,
				'tstamp'				=> $this->_cycle,
				'deleted'				=> 0
			);
			
			// Datenbank-Eintrag
			$data = $GLOBALS['TYPO3_DB']->fullQuoteArray($entry, 'tx_twsitemap_domain_model_entry');
			$GLOBALS['TYPO3_DB']->sql_query('REPLACE INTO `tx_twsitemap_domain_model_entry` (`'.implode('`, `', array_keys($entry)).'`) VALUES ('.implode(', ', $data).')');
		}
	}
	
	/************************************************************************************************
	 * STATISCHE METHODEN
	 ***********************************************************************************************/
	
	/**
	 * Absetzen eines HTTP-Aufrufs per CURL
	 *
	 * @param string $url				Endpunkt / URL
	 * @param array $header				Header
	 * @param string $method			Methode
	 * @param string $body				Body
	 * @param boolean $debug			Debugging-Ausgaben
	 * @return string					Daten
	 * @deprecated
	 */
	public static function httpRequest($url, array $header, $method, $body = null, $debug = false) {
		if (!$method) {
			$method						= 'GET';
		};
	
		$curl							= curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_MAXREDIRS, 5);
	
		if ($body) {
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
			curl_setopt($curl, CURLOPT_HTTPHEADER, array_merge($header, 'Content-Type: text/xml;charset=utf-8'));
		}
	
		$data							= curl_exec($curl);
	
		// Ggf. Debugging-Ausgabe
		if ($debug) {
			print_r(curl_getinfo($curl));
		}
	
		curl_close($curl);
	
		return $data;
	}
	
	/**
	 * Return the additional fields
	 * 
	 * @param \array $taskInfo
	 * @param unknown $task
	 * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject
	 * @return multitype:multitype:string
	 */
	public function getAdditionalFields(array &$taskInfo, $task, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject) {
		$additionalFields				= array();
		
		// Root page
		$fieldName						= 'tx_scheduler[tw_sitemap_root]';
		$fieldId						= 'task_sitemap_root';
		$fieldHTML						= '<input type="text" size="3" name="'.$fieldName.'" id="'.$fieldId.'" value="'.htmlspecialchars(empty($task->root) ? 1 : $task->root).'"/>';
		$additionalFields[$fieldId]		= array(
			'code'						=> $fieldHTML,
			'label'						=> 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:scheduler.entries.root',
			'cshKey'					=> '_MOD_system_txschedulerM1',
			'cshLabel'					=> $fieldId
		);
		
		// Debug output
		$fieldName						= 'tx_scheduler[tw_sitemap_debug]';
		$fieldId						= 'task_sitemap_debug';
		$fieldHTML						= '<input type="checkbox" value="1" name="'.$fieldName.'" id="'.$fieldId.'"'.((empty($task->debug) || !$task->debug) ? '' : ' checked="checked"').'"/>';
		$additionalFields[$fieldId]		= array(
			'code'						=> $fieldHTML,
			'label'						=> 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:scheduler.entries.debug',
			'cshKey'					=> '_MOD_system_txschedulerM1',
			'cshLabel'					=> $fieldId
		);

		return $additionalFields;
	}
	
	/**
	 * Validate the additional fields
	 * 
	 * @param \array $submittedData														Submitted data
	 * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject	Parent controller object
	 * @return \boolean																	Field validity
	 */
	public function validateAdditionalFields(array &$submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject) {
		$valid							= true;
		
		$submittedData['tw_sitemap_root']		= trim($submittedData['tw_sitemap_root']);
		if (!intval($submittedData['tw_sitemap_root'])) {
			$parentObject->addMessage('The root page ID must be a valid integer', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
			$valid						= false;
		} else {
			$rootPage					= $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('uid', 'pages', 'deleted=0 AND hidden=0 AND uid='.intval($submittedData['tw_sitemap_root']));
			if (empty($rootPage)) {
				$parentObject->addMessage('The root page ID must refer to a valid page', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
				$valid					= false;
			}
		}
		
		return $valid;
	}
	
	/**
	 * Save the additional field values
	 * 
	 * @param \array $submittedData														Submitted fields
	 * @param \TYPO3\CMS\Scheduler\Task\AbstractTask $task								Task instance
	 */
	public function saveAdditionalFields(array $submittedData, \TYPO3\CMS\Scheduler\Task\AbstractTask $task) {
		$task->root						= intval(trim($submittedData['tw_sitemap_root']));
		$task->debug					= intval(trim($submittedData['tw_sitemap_debug']));
	}
	
	/**
	 * Get additional information about the task
	 * 
	 * @return string
	 */
	public function getAdditionalInformation() {
		$root							= intval($this->root);
		$root							= $root ? \TYPO3\CMS\Backend\Utility\BackendUtility::getRecordRaw('pages', 'uid='.$root) : null;
		return $root ? 'Root page: '.\TYPO3\CMS\Backend\Utility\BackendUtility::getRecordTitle('pages', $root).' ('.$this->root.')' : '';
	}
}