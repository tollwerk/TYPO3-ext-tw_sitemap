<?php

/***************************************************************
 *  Copyright notice
 *
 *  © 2012 Dipl.-Ing. Joschi Kuphal <joschi@tollwerk.de>, tollwerk® GmbH
 *  
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

// Registrieren des Sitemap-Plugins
Tx_Extbase_Utility_Extension::registerPlugin(
	$_EXTKEY,
	'Sitemap',
	'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xml:feplugin'
);

// Registrieren der Typoscript-Konfiguration
t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'tollwerk® XML Sitemap');

// Registrieren der Sitemap-Einträge
t3lib_extMgm::addLLrefForTCAdescr('tx_twsitemap_domain_model_entry', 'EXT:tw_sitemap/Resources/Private/Language/locallang_csh_tx_twsitemap_domain_model_entry.xml');
t3lib_extMgm::allowTableOnStandardPages('tx_twsitemap_domain_model_entry');
$GLOBALS['TCA']['tx_twsitemap_domain_model_entry'] = array(
	'ctrl'					=> array(
		'title'				=> 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xml:tx_twsitemap_domain_model_entry',
		'label'				=> 'loc',
		'tstamp'			=> 'tstamp',
		'crdate'			=> 'crdate',
		'cruser_id'			=> 'cruser_id',
		'dividers2tabs'		=> TRUE,
		'delete'			=> 'deleted',
		'enablecolumns'		=> array(),
		'dynamicConfigFile'	=> t3lib_extMgm::extPath($_EXTKEY).'Configuration/TCA/Entry.php',
		'iconfile'			=> t3lib_extMgm::extRelPath($_EXTKEY).'Resources/Public/Icons/tx_twsitemap_domain_model_entry.gif'
	),
);

// Registrieren der Sitemaps
t3lib_extMgm::addLLrefForTCAdescr('tx_twsitemap_domain_model_sitemap', 'EXT:tw_sitemap/Resources/Private/Language/locallang_csh_tx_twsitemap_domain_model_sitemap.xml');
t3lib_extMgm::allowTableOnStandardPages('tx_twsitemap_domain_model_sitemap');
$GLOBALS['TCA']['tx_twsitemap_domain_model_sitemap'] = array(
	'ctrl'					=> array(
		'title'				=> 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xml:tx_twsitemap_domain_model_sitemap',
		'label'				=> 'domain',
		'tstamp'			=> 'tstamp',
		'crdate'			=> 'crdate',
		'cruser_id'			=> 'cruser_id',
		'dividers2tabs'		=> TRUE,
		'delete'			=> 'deleted',
		'enablecolumns'		=> array(
			'disabled'		=> 'hidden',
			'starttime'		=> 'starttime',
			'endtime'		=> 'endtime',
		),
		'dynamicConfigFile'	=> t3lib_extMgm::extPath($_EXTKEY).'Configuration/TCA/Sitemap.php',
		'iconfile'			=> t3lib_extMgm::extRelPath($_EXTKEY).'Resources/Public/Icons/tx_twsitemap_domain_model_sitemap.gif'
	),
);

?>