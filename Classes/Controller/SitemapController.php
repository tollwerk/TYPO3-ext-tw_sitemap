<?php

namespace Tollwerk\TwSitemap\Controller;

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
 * Sitemap-Plugin-Controller
 * 
 * @package tw_sitemap
 * @author Dipl.-Ing. Joschi Kuphal <joschi@tollwerk.de>
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class SitemapController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {
	/**
	 * Sitemap-Eintrags-Repository
	 * 
	 * @var \Tollwerk\TwSitemap\Domain\Repository\SitemapRepository
	 */
	protected $sitemapRepository;
	/**
	 * Eintragskonfigurationen
	 * 
	 * @var array
	 */
	protected $_entriesConfiguration = array();
	/**
	 * Plugin-Parameter-Callbacks
	 * 
	 * @var array
	 */
	protected static $_pluginParameterTypeCallbacks = array(
		'repository'						=> '_getParameterValuesRepository',
	);

	/**
	 * Dependecy-Injection des Sitemap-Repositories
	 * 
	 * @param \Tollwerk\TwSitemap\Domain\Repository\SitemapRepository $sitemapRepository		Sitemap-Repository
 	 * @return void
	 */
	public function injectSitemapRepository(\Tollwerk\TwSitemap\Domain\Repository\SitemapRepository $sitemapRepository) {
		$this->sitemapRepository			= $sitemapRepository;
	}
	
	/**
	 * Anzeigen einer XML-Sitemap
	 * 
	 * Diese Aktion wird ohne Parameter aufgerufen. Es wird der Settings-Parameter "domain" ermittelt,
	 * der für die aktuelle Frontend-Seite per TypoScript konfiguriert ist. Sofern für diese Domain eine
	 * XML-Sitemap angelegt ist, wird diese ausgeliefert. Es wird automatisch berücksichtigt, ob für die
	 * Sitemap Gzip-Kompression aktiviert ist.
	 *
	 * @return string						XML-Sitemap
	 */
	public function indexAction() {
		$sitemap							= $this->sitemapRepository->findOneByTargetDomain($_SERVER['HTTP_HOST']);
		if (!($sitemap instanceof \Tollwerk\TwSitemap\Domain\Model\Sitemap)) {
			$sitemap						= $this->sitemapRepository->findOneByDomain($_SERVER['HTTP_HOST']);
			if (!($sitemap instanceof \Tollwerk\TwSitemap\Domain\Model\Sitemap)) {
				$domain						= $this->settings['domain'];
				if (strlen($domain)) {
					$sitemap				= $this->sitemapRepository->findOneByTargetDomain($domain);
					if (!($sitemap instanceof \Tollwerk\TwSitemap\Domain\Model\Sitemap)) {
						$sitemap			= $this->sitemapRepository->findOneByDomain($domain);
					}
				}
			}
		}

		// Wenn ein geeigneter Sitemap-Eintrag gefunden wurde ...
		if ($sitemap instanceof \Tollwerk\TwSitemap\Domain\Model\Sitemap) {
			$sitemapDirectory				= PATH_site.'typo3temp/tw_sitemap/'.$sitemap->getUid().'/';

			if (@is_dir($sitemapDirectory)) {
				$sitemapGzip				= (boolean)intval($sitemap->getGz());
				$sitemapPath				= $sitemapDirectory.'sitemap.xml'.($sitemapGzip ? '.gz' : '');
				if (@is_file($sitemapPath) && @is_readable($sitemapPath)) {
					header('Content-Type: application/xml; charset=utf-8');
					if ($sitemapGzip) {
						header('Content-Encoding: gzip');
					}
					readfile($sitemapPath);
					exit;
				}
			}
		}
	}
	
	/**
	 * Rendern von Sitemap-Einträgen anhand einer TypoScript-Konfiguration
	 * 
	 * @param string $typoscript			Schlüssel der zu rendernden Typoscript-Konfiguration
	 * @return void
	 */
	public function typoscriptAction($typoscript = null) {
		header('Content-Type: text/xml; charset=utf-8');
		
		/*Tx_Extbase_Utility_TypoScript*/
		$typoscriptService					= \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Service\\TypoScriptService');
		$this->_entriesConfiguration		= array_key_exists('entries', $this->settings) ? $typoscriptService->convertPlainArrayToTypoScriptArray((array)$this->settings['entries']) : array();
		$typoscript							= strval($typoscript);
		$tsConfig							= (strlen($typoscript) && array_key_exists("$typoscript.", $this->_entriesConfiguration) && array_key_exists('entries.', $this->_entriesConfiguration["$typoscript."])) ? $this->_entriesConfiguration["$typoscript."]['entries.'] : null;
		$tsResult							= is_array($tsConfig) ? $GLOBALS['TSFE']->cObj->COBJ_ARRAY($tsConfig) : '';
		die("<entries>$tsResult</entries>");
		exit;
	}
	
	/**
	 * Rendern von Sitemap-Einträgen anhand einer Plugin-Konfiguration
	 *
	 * @param string $plugin			Schlüssel der zu rendernden Plugin-Konfiguration
	 * @return void
	 */
	public function pluginAction($plugin = null) {
		header('Content-Type: text/xml; charset=utf-8');
		
		$typoscriptService					= \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Service\\TypoScriptService');
		$this->_entriesConfiguration		= array_key_exists('entries', $this->settings) ? $typoscriptService->convertPlainArrayToTypoScriptArray((array)$this->settings['entries']) : array();
		$plugin								= strval($plugin);
		$pluginConfig						= (strlen($plugin) && array_key_exists("$plugin.", $this->_entriesConfiguration) && array_key_exists('entries.', $this->_entriesConfiguration["$plugin."])) ? $this->_entriesConfiguration["$plugin."]['entries.'] : null;
		
		// Ermitteln des Parameter-Basisnamens
		$extensionName						= array_key_exists('extension', $pluginConfig) ? trim($pluginConfig['extension']) : null;
		$pluginName							= array_key_exists('plugin', $pluginConfig) ? trim($pluginConfig['plugin']) : null;
		$controller							= array_key_exists('controller', $pluginConfig) ? trim($pluginConfig['controller']) : null;
		$action								= array_key_exists('action', $pluginConfig) ? trim($pluginConfig['action']) : null;
		$parameter							= array_key_exists('parameter.', $pluginConfig) ? (array)$pluginConfig['parameter.'] : array();
		
		// Wenn der Parameterbasisname definiert werden kann und ein alternierender Parameter definiert ist
		if (strlen($extensionName) && strlen($pluginName) && strlen($controller) && strlen($action) && count($parameter)) {
			$this->view->assign('extensionName', $extensionName);
			$this->view->assign('pluginName', $pluginName);
			$this->view->assign('controller', $controller);
			$this->view->assign('action', $action);
			$this->view->assign('pageUid', $GLOBALS['TSFE']->id);
				
			// Definieren des Parameterbasisnamens, -namens und -typs
			$parameters						= array();
// 			$parameterBase					= strtolower('tx_'.strtr($pluginName, '_', '').'_'.strtr($controller, '_', ''));
			$parameterName					= array_key_exists('name', $parameter) ? trim($parameter['name']) : null;
			$parameterType					= array_key_exists('type', $parameter) ? trim($parameter['type']) : null;
			
			// Wenn ein sinnvoller Parameter definiert ist ...
			if (strlen($parameterName) && strlen($parameterType) && array_key_exists($parameterType, self::$_pluginParameterTypeCallbacks)) {
				$parameters[$parameterName]			= call_user_func(array($this, self::$_pluginParameterTypeCallbacks[$parameterType]), $parameter, $this->_entriesConfiguration["$plugin."]);
				$this->view->assign('parameters', $parameters);
				die($this->view->render());
				
			} else {
				exit;
			}
			
		} else {
			exit;	
		}
	}
	
	/************************************************************************************************
	 * PRIVATE METHODEN
	 ***********************************************************************************************/
	
	/**
	 * Erzeugen von repositorybezogenen Parameterwerten
	 * 
	 * @param array $config				Parameterkonfiguration
	 * @param array $pluginConfig		Globale Konfiguration
	 * @return array					Parameterwerte
	 */
	protected function _getParameterValuesRepository($config, $pluginConfig) {
		$values						= array();
		$objectManager				= \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		$repository					= $objectManager->get($config['repository']);
		if ($repository instanceof \TYPO3\CMS\Extbase\Persistence\Repository) {
			$repositoryConfig		= array_key_exists('repository.', $config) ? (array)$config['repository.'] : array();
			
			// Ermitten von zulässigen Storage-PIDs
			$storagePids			= array_key_exists('storagePid', $repositoryConfig) ? (strlen(trim($repositoryConfig['storagePid'])) ? \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', trim($repositoryConfig['storagePid'])) : array()): array($GLOBALS['TSFE']->id);
			
			// Erzeugen einer Abfrage
			$query					= $repository->createQuery();
			$query->getQuerySettings()->setStoragePageIds($storagePids);
			
			// TODO: WHERE-Klauseln etc. 

			// Ausführen und Durchlaufen der Abfrage
			foreach ($query->execute() as $object) {
				
				// Wenn nur eine einzelne Objekteigenschaft als Wert in Frage kommt ...
				if (array_key_exists('column', $repositoryConfig) && strlen(trim($repositoryConfig['column']))) {
					$values[]		= @call_user_func(array($object, 'get'.ucfirst($repositoryConfig['column'])));
					
				// Ansonsten
				} else {
					$values[]		= $object;
				}
			}
		}
		
		return $values;
	}
}

?>