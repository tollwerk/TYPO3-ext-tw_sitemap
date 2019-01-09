<?php

/***************************************************************
 *  Copyright notice
 *
 *  Copyright © 2019 Dipl.-Ing. Joschi Kuphal (joschi@tollwerk.de)
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

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['EXT']['extParams'][$_EXTKEY] = unserialize($_EXTCONF);

// Configure the sitemap plugin
ExtensionUtility::configurePlugin(
    'Tollwerk.'.
    $_EXTKEY,
    'Sitemap',
    array(
        'Sitemap' => 'index,typoscript,plugin',
    ),
    array(
        'Sitemap' => 'index,typoscript,plugin',
    )
);

// Register the scheduler tasks
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Tollwerk\\TwSitemap\\Task\\Sitemap'] = array(
    'extension'   => $_EXTKEY,
    'title'       => 'LLL:EXT:'.$_EXTKEY.'/Resources/Private/Language/locallang.xlf:scheduler.sitemap',
    'description' => 'LLL:EXT:'.$_EXTKEY.'/Resources/Private/Language/locallang.xlf:scheduler.sitemap.description',
);
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Tollwerk\\TwSitemap\\Task\\Entries'] = array(
    'extension'        => $_EXTKEY,
    'title'            => 'LLL:EXT:'.$_EXTKEY.'/Resources/Private/Language/locallang.xlf:scheduler.entries',
    'description'      => 'LLL:EXT:'.$_EXTKEY.'/Resources/Private/Language/locallang.xlf:scheduler.entries.description',
    'additionalFields' => 'Tollwerk\\TwSitemap\\Task\\Entries',
);

$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_twsitemap_sitemap[typoscript]';
$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_twsitemap_sitemap[plugin]';
