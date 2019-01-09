<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$fields = [
    'tx_twsitemap_nofollow' => [
        'label'  => 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:pages.tx_twsitemap_nofollow',
        'config' => [
            'type'  => 'check',
            'renderType' => 'checkboxToggle',
        ]
    ],
    'tx_twsitemap_noindex'  => [
        'label'  => 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:pages.tx_twsitemap_noindex',
        'config' => [
            'type'  => 'check',
            'renderType' => 'checkboxToggle',
        ]
    ]
];

// Add new fields & palette
ExtensionManagementUtility::addTCAcolumns('pages', $fields);
ExtensionManagementUtility::addFieldsToPalette('pages', 'searchengines', 'tx_twsitemap_nofollow,tx_twsitemap_noindex');
ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    '--palette--;LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:pages.palette.searchengines;searchengines',
    '',
    'before:module'
);
