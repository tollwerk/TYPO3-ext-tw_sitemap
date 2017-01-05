<?php

/***************************************************************
 *  Copyright notice
 *
 *  Copyright © 2017 Dipl.-Ing. Joschi Kuphal (joschi@tollwerk.de)
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

if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

// Register the sitemap plugin
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    $_EXTKEY,
    'Sitemap',
    'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:feplugin'
);

// Register the TypoScript setip
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript',
    'tollwerk® XML Sitemap');

// Register the sitemap entry table
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_twsitemap_domain_model_entry',
    'EXT:tw_sitemap/Resources/Private/Language/locallang_csh_tx_twsitemap_domain_model_entry.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_twsitemap_domain_model_entry');
$GLOBALS['TCA']['tx_twsitemap_domain_model_entry'] = array(
    'ctrl' => array(
        'title' => 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_entry',
        'label' => 'loc',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'dividers2tabs' => true,
        'delete' => 'deleted',
        'enablecolumns' => array(),
        'dynamicConfigFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'Configuration/TCA/Entry.php',
        'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY).'Resources/Public/Icons/tx_twsitemap_domain_model_entry.gif'
    ),
);

// Register the sitemap table
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_twsitemap_domain_model_sitemap',
    'EXT:tw_sitemap/Resources/Private/Language/locallang_csh_tx_twsitemap_domain_model_sitemap.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_twsitemap_domain_model_sitemap');
$GLOBALS['TCA']['tx_twsitemap_domain_model_sitemap'] = array(
    'ctrl' => array(
        'title' => 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_sitemap',
        'label' => 'domain',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'dividers2tabs' => true,
        'delete' => 'deleted',
        'enablecolumns' => array(
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ),
        'dynamicConfigFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'Configuration/TCA/Sitemap.php',
        'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY).'Resources/Public/Icons/tx_twsitemap_domain_model_sitemap.gif'
    ),
);

// Load the pages TCA if TYPO3 version < 6.1
if (version_compare(TYPO3_version, '6.1.0', 'lt')) {
    \TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA('pages');
}

// Register the nofollow page property
$TCA['pages']['columns']['tx_twsitemap_nofollow'] = array(
    'label' => 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:pages.tx_twsitemap_nofollow',
    'config' => Array(
        'type' => 'check',
        'items' => array(
            array('LLL:EXT:cms/locallang_tca.xml:pages.no_search_checkbox_1_formlabel', 1),
        )
    )
);

// Register the noindex page property
$TCA['pages']['columns']['tx_twsitemap_noindex'] = array(
    'label' => 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:pages.tx_twsitemap_noindex',
    'config' => Array(
        'type' => 'check',
        'items' => array(
            array('LLL:EXT:cms/locallang_tca.xml:pages.no_search_checkbox_1_formlabel', 1),
        )
    )
);

$GLOBALS['TCA']['pages']['palettes']['searchengines'] = array(
    'showitem' => 'tx_twsitemap_nofollow,tx_twsitemap_noindex',
    'canNotCollapse' => true,
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    '--palette--;LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:pages.palette.searchengines;searchengines',
    '',
    'before:module'
);
