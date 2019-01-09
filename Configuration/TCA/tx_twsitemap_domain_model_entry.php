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

return [
    'ctrl'      => [
        'title'         => 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_entry',
        'label'         => 'loc',
        'tstamp'        => 'tstamp',
        'crdate'        => 'crdate',
        'cruser_id'     => 'cruser_id',
        'delete'        => 'deleted',
        'enablecolumns' => [],
        'iconfile'      => 'EXT:tw_sitemap/Resources/Public/Icons/tx_twsitemap_domain_model_entry.gif',
    ],
    'interface' => [
        'showRecordFieldList' => 'domain, origin, loc, lastmod, changefreq, priority',
    ],
    'types'     => [
        '1' => ['showitem' => 'domain, origin, source, language, loc, lastmod, changefreq, priority, position'],
    ],
    'palettes'  => [
        '1' => ['showitem' => ''],
    ],
    'columns'   => [
        'domain'     => [
            'exclude' => 0,
            'label'   => 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_entry.domain',
            'config'  => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ],
        ],
        'origin'     => [
            'exclude' => 0,
            'label'   => 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_entry.origin',
            'config'  => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ],
        ],
        'loc'        => [
            'exclude' => 0,
            'label'   => 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_entry.loc',
            'config'  => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ],
        ],
        'lastmod'    => [
            'exclude' => 0,
            'label'   => 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_entry.lastmod',
            'config'  => [
                'type'     => 'input',
                'size'     => 12,
                'max'      => 20,
                'eval'     => 'datetime,required',
                'checkbox' => 1,
                'default'  => time()
            ],
        ],
        'changefreq' => [
            'exclude' => 0,
            'label'   => 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_entry.changefreq',
            'config'  => [
                'type'       => 'select',
                'renderType' => 'selectSingle',
                'items'      => [
                    [
                        'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_entry.changefreq.I.0',
                        0
                    ],
                    [
                        'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_entry.changefreq.I.1',
                        1
                    ],
                    [
                        'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_entry.changefreq.I.2',
                        2
                    ],
                    [
                        'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_entry.changefreq.I.3',
                        3
                    ],
                    [
                        'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_entry.changefreq.I.4',
                        4
                    ],
                    [
                        'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_entry.changefreq.I.5',
                        5
                    ],
                    [
                        'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_entry.changefreq.I.6',
                        6
                    ],
                ],
                'size'       => 1,
                'maxitems'   => 1,
                'eval'       => 'required'
            ],
        ],
        'priority'   => [
            'exclude' => 0,
            'label'   => 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_entry.priority',
            'config'  => [
                'type'  => 'input',
                'size'  => 30,
                'eval'  => 'double2,required',
                'range' => [
                    'lower' => 0,
                    'upper' => 1,
                ],
            ],
        ],
        'language'   => [
            'exclude' => 0,
            'label'   => 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_entry.language',
            'config'  => [
                'type'     => 'input',
                'size'     => 4,
                'max'      => 5,
                'eval'     => 'trim',
                'checkbox' => 1,
            ],
        ],
        'position'   => [
            'exclude' => 0,
            'label'   => 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_entry.position',
            'config'  => [
                'type' => 'input',
                'size' => 7,
                'eval' => 'int,required',
            ],
        ],
        'source'     => [
            'exclude' => 0,
            'label'   => 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_entry.source',
            'config'  => [
                'type'     => 'input',
                'size'     => 30,
                'max'      => 32,
                'eval'     => 'trim',
                'checkbox' => 1,
            ],
        ],
    ],
];
