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

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

call_user_func(
    function() {
        // Register the TypoScript setup
        ExtensionManagementUtility::addStaticFile('tw_sitemap', 'Configuration/TypoScript', 'tollwerk XML Sitemap');

        // Allow records on standard files
        ExtensionManagementUtility::allowTableOnStandardPages('tx_twsitemap_domain_model_entry');
        ExtensionManagementUtility::allowTableOnStandardPages('tx_twsitemap_domain_model_sitemap');

        // Register the sitemap entry table
        ExtensionManagementUtility::addLLrefForTCAdescr(
            'tx_twsitemap_domain_model_entry',
            'EXT:tw_sitemap/Resources/Private/Language/locallang_csh_tx_twsitemap_domain_model_entry.xlf'
        );

        // Register the sitemap table
        ExtensionManagementUtility::addLLrefForTCAdescr(
            'tx_twsitemap_domain_model_sitemap',
            'EXT:tw_sitemap/Resources/Private/Language/locallang_csh_tx_twsitemap_domain_model_sitemap.xlf'
        );

        // Register the sitemap plugin
        ExtensionUtility::registerPlugin(
            'Tollwerk.TwSitemap',
            'Sitemap',
            'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:feplugin'
        );
    }
);
