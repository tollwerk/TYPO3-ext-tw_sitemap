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
 * Planer-Task zur Erzeugung von XML-Sitemaps
 *
 * @package tw_sitemap
 * @author Dipl.-Ing. Joschi Kuphal <joschi@tollwerk.de>
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class Sitemap extends \TYPO3\CMS\Scheduler\Task\AbstractTask  {
	/**
	 * Maximale Anzahl von URLs je Sitemap
	 * 
	 * @var int
	 */
	const URL_LIMIT = 50000;
	/**
	 * Maximale Dateigröße je Sitemap
	 * 
	 * @var int
	 */
	const SIZE_LIMIT = 50000000;
	/**
	 * Sitemap-Startsequenz
	 * 
	 * @var string
	 */
	const SITEMAP_PRIMER = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns:xhtml="http://www.w3.org/1999/xhtml">';
	/**
	 * Sitemap-Endsequenz
	 * 
	 * @var string
	 */
	const SITEMAP_FOOTER = '</urlset>';
	/**
	 * Sitemap-Index-Startsequenz
	 * 
	 * @var unknown_type
	 */
	const SITEMAP_INDEX_PRIMER = '<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
	/**
	 * Sitemap-Endsequenz
	 * 
	 * @var string
	 */
	const SITEMAP_INDEX_FOOTER = '</sitemapindex>';
	
	/************************************************************************************************
	 * ÖFFENTLICHE METHODEN
	 ***********************************************************************************************/
	
	/**
	 * Ausführen der Synchronisation
	 * 
	 * @see tx_scheduler_Task::execute()
	 */
	public function execute() {
		$objectManager			= \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('\TYPO3\CMS\Extbase\Object\ObjectManager');
		
		/* @var $sitemapModel \Tollwerk\TwSitemap\Domain\Repository\SitemapRepository */
		/* @var $sitemapEntryModel \Tollwerk\TwSitemap\Domain\Repository\EntryRepository */
		$sitemapModel			= $objectManager->get('\Tollwerk\TwSitemap\Domain\Repository\SitemapRepository');
		$sitemapEntryModel		= $objectManager->get('\Tollwerk\TwSitemap\Domain\Repository\EntryRepository');

		// Durchlaufen aller registrierten XML-Sitemaps
		/* @var $sitemap \Tollwerk\TwSitemap\Domain\Model\Sitemap */
		foreach ($sitemapModel->findAll() as $sitemap) {
			$this->_generateSitemap($sitemap);			
		}
		
		return true;
	}
	
	/************************************************************************************************
	 * PRIVATE METHODEN
	 ***********************************************************************************************/
	
	/**
	 * Erzeugen einer einzelnen Sitemap
	 * 
	 * @param \Tollwerk\TwSitemap\Domain\Model\Sitemap $sitemap		XML-Sitemap
	 * @return boolean											Erfolg
	 * @throws Exception										Wenn ein ungültiger Sitemap-Dateiname gegeben ist
	 */
	protected function _generateSitemap(\Tollwerk\TwSitemap\Domain\Model\Sitemap $sitemap) {
		$sitemapDomain					= trim($sitemap->getDomain(), '/ ');
		$sitemapTargetDomain			= trim($sitemap->getTargetDomain(), '/ ');
		$sitemapDirectory				= PATH_site.'typo3temp/tw_sitemap/'.$sitemap->getUid().'/';
		$sitemapTmpDirectory			= PATH_site.'typo3temp/tw_sitemap/'.$sitemap->getUid().'.tmp/';
		
		// Ggf. Entfernen eines bereits vorhandenen Temporärverzeichnisses
		if (@is_dir($sitemapTmpDirectory)) {
			if (!$this->_deleteDirectory($sitemapTmpDirectory)) {
				throw new Exception('Sitemap temporary directory could not be deleted');
			}
		}
		
		// Anlegen des Temporärverzeichnisses
		if (!@mkdir($sitemapTmpDirectory, 0777, true) || !@chmod($sitemapTmpDirectory, 0777)) {
			throw new Exception('Sitemap temporary directory could not be created');
		}
		
		// Abrufen und Durchlaufen aller Sitemap-Einträge
		/* @var $db \TYPO3\CMS\Core\Database\DatabaseConnection */
		$db								= $GLOBALS['TYPO3_DB'];
		$sitemapEntriesResult			= $db->exec_SELECTquery('GROUP_CONCAT(loc) AS loc,MAX(lastmod) AS lastmod,MIN(changefreq) AS changefreq,MAX(priority) as priority,GROUP_CONCAT(language) AS language', 'tx_twsitemap_domain_model_entry', 'domain='.$db->fullQuoteStr($sitemapDomain, 'tx_twsitemap_domain_model_entry').' AND deleted=0', 'CONCAT(origin, "~", source)', 'priority DESC, lastmod DESC');
		if ($sitemapEntriesResult && $db->sql_num_rows($sitemapEntriesResult)) {
			// Vorbereitungen
			$sitemapGzip				= (boolean)intval($sitemap->getGz());
			$sitemapFooterLength		= strlen(self::SITEMAP_FOOTER);
			$sitemapDomain				= (strlen($sitemapTargetDomain) ? $sitemapTargetDomain : $sitemapDomain).'/';
			$sitemapScheme				= $sitemap->getScheme();
			$sitemapFiles				= array();
			$currentSitemapSize			= 0;
			$currentSitemapResource		= $this->_startSitemapFile($sitemapTmpDirectory, $sitemapFiles, $sitemapGzip, $currentSitemapSize);
			$sitemapURLs				= 0;
			
			// Abrufen und Verarbeiten aller Sitemap-Einträge
			while($sitemapEntryRecord = $db->sql_fetch_assoc($sitemapEntriesResult)) {
				$languages				= strlen($sitemapEntryRecord['language']) ? explode(',', $sitemapEntryRecord['language']) : array();
				$locs					= explode(',', $sitemapEntryRecord['loc']);
				
				// Generieren des XML-Sitemap-Eintrages
				$sitemapEntry			= '<url><loc>'.htmlspecialchars($sitemapScheme.$sitemapDomain.ltrim($locs[0], '/')).'</loc><lastmod>'.date('Y-m-d', $sitemapEntryRecord['lastmod']).'T'.date('H:i:s', $sitemapEntryRecord['lastmod']).'Z</lastmod><changefreq>'.\Tollwerk\TwSitemap\Domain\Model\Entry::$changefreqs[$sitemapEntryRecord['changefreq']].'</changefreq><priority>'.number_format($sitemapEntryRecord['priority'], 1).'</priority>';
				if ($languages > 1) {
					foreach ($languages as $index => $language) {
						$sitemapEntry	.= '<xhtml:link rel="alternate" hreflang="'.htmlspecialchars($language).'" href="'.htmlspecialchars($sitemapScheme.$sitemapDomain.ltrim($locs[$index], '/')).'"/>';
					}
				}
				$sitemapEntry			.= '</url>';
				$sitemapEntryLength		= strlen($sitemapEntry);
				$sitemapProvLength		= ($currentSitemapSize + $sitemapEntryLength + $sitemapFooterLength);
				
				// Wenn die aktuelle Sitemap mit diesem Eintrag das Größenlimit überschreiben würde ...
				if (($sitemapProvLength > self::SIZE_LIMIT) || (($sitemapURLs + 1) > self::URL_LIMIT)) {
					
					// Starten einer neuen Sitemap
					$this->_stopSitemapFile($currentSitemapResource, $sitemapGzip);
					$currentSitemapResource			= $this->_startSitemapFile($sitemapTmpDirectory, $sitemapFiles, $sitemapGzip, $currentSitemapSize);
					$sitemapProvLength				= ($currentSitemapSize + $sitemapEntryLength + $sitemapFooterLength);
					$sitemapURLs					= 0;
				}
				
				// Schreiben des Eintrages in die Sitemap
				$currentSitemapSize					+= $sitemapGzip ? fwrite($currentSitemapResource, $sitemapEntry) : gzwrite($currentSitemapResource, $sitemapEntry);
				++$sitemapURLs;
			}
			
			// Schließen der letzten Sitemap-Datei
			$this->_stopSitemapFile($currentSitemapResource, $sitemapGzip);
			
			// Setzen der Schreibberechtigung aller erzeugten Dateien
			foreach ($sitemapFiles as $sitemapFile) {
				chmod($sitemapTmpDirectory.DIRECTORY_SEPARATOR.$sitemapFile, 0777);
			}
			
			// Wenn mehr als eine Sitemap-Datei generiert wurde: Erzeugen eines Sitemap-Index
			if (count($sitemapFiles) > 1) {
				$success							= $this->_createSitemapIndex($sitemapTmpDirectory, $sitemapFiles, $sitemapGzip, $sitemapScheme.$sitemapDomain.'typo3temp/tw_sitemap/'.$sitemap->getUid().'/');
				
			// Ansonsten: Umbenennen der einen Datei
			} else {
				$success							= rename($sitemapTmpDirectory.DIRECTORY_SEPARATOR.$sitemapFiles[0], $sitemapTmpDirectory.DIRECTORY_SEPARATOR.'sitemap.xml'.($sitemapGzip ? '.gz' : ''));
			}
			
			// Wenn die Sitemap(s) erfolgreich erzeugt wurden
			if ($success) {
				
				// Löschen eines ggf. bereits vorhandenen Sitemap-Verzeichnisses
				if (@is_dir($sitemapDirectory) && !$this->_deleteDirectory($sitemapDirectory)) {
					throw new Exception('Previous sitemap version could not be removed');					
				}
				
				return (boolean)rename($sitemapTmpDirectory, $sitemapDirectory);
				
			// Ansonsten: Fehler
			} else {
				throw new Exception('Sitemap generation could not be finalized');
			}
		}
		
		return true;
	}
	
	/**
	 * Öffnen einer neuen Sitemap-Datei
	 * 
	 * @param string $directory			Verzeichnis
	 * @param array $sitemapFiles		Sitemap-Dateien
	 * @param boolean $gz				GZIP-Kompression verwenden
	 * @return resource					Sitemap-Datei
	 */
	protected function _startSitemapFile($directory, array &$sitemapFiles, $gz, &$bytes) {
		$sitemapName					= 'sitemap-'.(count($sitemapFiles) + 1).'.xml';
		$sitemapPath					= rtrim($directory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
		if ((boolean)$gz) {
			$sitemapName				.= '.gz';
			$sitemap					= gzopen($sitemapPath.$sitemapName, 'wb');
			$bytes						= gzwrite($sitemap, self::SITEMAP_PRIMER);
		} else {
			$sitemap					= fopen($sitemapPath.$sitemapName, 'wb');
			$bytes						= fwrite($sitemap, self::SITEMAP_PRIMER);
		}
		$sitemapFiles[]					= $sitemapName;
		return $sitemap;
	}
	
	/**
	 * Schließen einer Sitemap-Datei
	 * 
	 * @param resource $sitemap			Sitemap-Resource
	 * @param boolean $gz				GZIP-Kompression verwendet
	 * @return void
	 */
	protected function _stopSitemapFile($sitemap, $gz) {
		if ((boolean)$gz) {
			gzwrite($sitemap, self::SITEMAP_FOOTER);
			gzclose($sitemap);
		} else {
			fwrite($sitemap, self::SITEMAP_FOOTER);
			fclose($sitemap);
		}
	}
	
	/**
	 * Erzeugen eines Sitemap-Index
	 * 
	 * @param string $directory			Verzeichnis
	 * @param array $sitemapFiles		Sitemap-Dateien
	 * @param boolean $gz				GZIP-Kompression verwenden
	 * @param string $urlbase			Basis-URL für einzelne Sitemap-Dateien
	 * @return boolean					Erfolg
	 */
	protected function _createSitemapIndex($directory, array $sitemapFiles, $gz, $urlbase) {
		$sitemapIndex					= self::SITEMAP_INDEX_PRIMER;
		$now							= date('Y-m-d');
		foreach ($sitemapFiles as $sitemapFile) {
			$sitemapIndex				.= '<sitemap><loc>'.$urlbase.$sitemapFile.'</loc><lastmod>'.$now.'</lastmod></sitemap>';
		}
		$sitemapIndex					.= self::SITEMAP_INDEX_FOOTER;
		$sitemapIndexPath				= rtrim($directory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'sitemap.xml';
		if ((boolean)$gz) {
			$sitemapIndexPath			.= '.gz';
			$sitemapIndexResource		= gzopen($sitemapIndexPath, 'wb');
			$bytes						= gzwrite($sitemapIndexResource, $sitemapIndex);
			gzclose($sitemapIndexResource);
		} else {
			$bytes						= file_put_contents($sitemapIndexPath, $sitemapIndex);
		}
		@chmod($sitemapIndexPath, 0777);
		return (boolean)$bytes;
	}
	
	/**
	 * Löschen eines Verzeichnisses samt Inhalt
	 * 
	 * @param string $directory				Verzeichnis
	 * @return boolean						Erfolg
	 */
	protected function _deleteDirectory($directory) {
		clearstatcache();
		$directory					= rtrim($directory, DIRECTORY_SEPARATOR);
		if (@is_dir($directory)) {
			foreach (scandir($directory) as $filedir) {
				if (($filedir == '.') || ($filedir == '..')) {
					continue;
				} elseif (@is_dir($directory.DIRECTORY_SEPARATOR.$filedir)) {
					if (!$this->_deleteDirectory($directory.DIRECTORY_SEPARATOR.$filedir)) {
						return false;
					}
				} elseif (!unlink($directory.DIRECTORY_SEPARATOR.$filedir)) {
					return false;
				}
			}
			if (!rmdir($directory)) {
				return false;
			}
		}
		
		return true;
	}
	
	public function getAdditionalFields(array &$taskInfo, $task, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject) {
		$additionalFields				= array();
	
		// Language parameter
		$fieldName						= 'tx_scheduler[tw_sitemap_lang]';
		$fieldId						= 'task_sitemap_lang';
		$fieldHTML						= '<input type="text" name="'.$fieldName.'" id="'.$fieldId.'" value="'.htmlspecialchars(empty($task->lang) ? 'L' : $task->lang).'"/>';
		$additionalFields[$fieldId]		= array(
			'code'						=> $fieldHTML,
			'label'						=> 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:scheduler.entries.lang',
			'cshKey'					=> '_MOD_system_txschedulerM1',
			'cshLabel'					=> $fieldId
		);
	
		$fieldName						= 'tx_scheduler[tw_sitemap_baseurl]';
		$fieldId						= 'task_sitemap_baseurl';
		$fieldOptions					= $this->_getSitemapOptions($taskInfo['scheduler_cachingFrameworkGarbageCollection_selectedBackends']);
		$fieldHtml = '<select name="' . $fieldName . '" id="' . $fieldId . '" class="wide" size="10" multiple="multiple">' . $fieldOptions . '</select>';
		$additionalFields[$fieldId] = array(
			'code' => $fieldHtml,
			'label' => 'LLL:EXT:scheduler/Resources/Private/Language/locallang.xlf:label.cachingFrameworkGarbageCollection.selectBackends',
			'cshKey' => '_MOD_system_txschedulerM1',
			'cshLabel' => $fieldId
		);
	
		return $additionalFields;
	}
	public function validateAdditionalFields(array &$submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject) {
		return true;
	}
	
	public function saveAdditionalFields(array $submittedData, \TYPO3\CMS\Scheduler\Task\AbstractTask $task) {
		$task->lang						= $submittedData['tw_sitemap_lang'];
		// 		$task->lang						= $submittedData['tw_sitemap_lang'];
	}
	

	/**
	 * Return all available sitemaps as options
	 *
	 * @param \int $selectedSitemap		Selected sitemap
	 * @return \string					Sitemap options
	 */
	protected function _getSitemapOptions($selectedSitemap = 0) {
		$options						= array();
	
		return implode('', $options);
	}
}