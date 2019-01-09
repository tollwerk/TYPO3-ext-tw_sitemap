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
        'title'         => 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_sitemap',
        'label'         => 'domain',
        'tstamp'        => 'tstamp',
        'crdate'        => 'crdate',
        'cruser_id'     => 'cruser_id',
        'delete'        => 'deleted',
        'enablecolumns' => [
            'disabled'  => 'hidden',
            'starttime' => 'starttime',
            'endtime'   => 'endtime',
        ],
        'iconfile'      => 'EXT:tw_sitemap/Resources/Public/Icons/tx_twsitemap_domain_model_sitemap.gif',
    ],
    'interface' => [
        'showRecordFieldList' => 'domain, target_domain, scheme, gz',
    ],
    'types'     => [
        '1' => ['showitem' => 'domain, target_domain, scheme, gz'],
    ],
    'palettes'  => [
        '1' => ['showitem' => ''],
    ],
    'columns'   => [
        'hidden'        => [
            'exclude' => 1,
            'label'   => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.visible',
            'config'  => [
                'type' => 'check',
            ],
        ],
        'starttime'     => [
            'exclude'   => 1,
            'l10n_mode' => 'mergeIfNotBlank',
            'label'     => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
            'config'    => [
                'type'     => 'input',
                'size'     => 13,
                'max'      => 20,
                'eval'     => 'datetime',
                'checkbox' => 0,
                'default'  => 0,
                'range'    => [
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
                ],
            ],
        ],
        'endtime'       => [
            'exclude'   => 1,
            'l10n_mode' => 'mergeIfNotBlank',
            'label'     => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
            'config'    => [
                'type'     => 'input',
                'size'     => 13,
                'max'      => 20,
                'eval'     => 'datetime',
                'checkbox' => 0,
                'default'  => 0,
                'range'    => [
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
                ],
            ],
        ],
        'domain'        => [
            'exclude' => 0,
            'label'   => 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_sitemap.domain',
            'config'  => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ],
        ],
        'target_domain' => [
            'exclude' => 0,
            'label'   => 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_sitemap.target_domain',
            'config'  => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'scheme'        => [
            'exclude' => 0,
            'label'   => 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_sitemap.scheme',
            'config'  => [
                'type'       => 'select',
                'renderType' => 'selectSingle',
                'items'      => [
                    [
                        'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_sitemap.scheme.I.0',
                        'http://'
                    ],
                    [
                        'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_sitemap.scheme.I.1',
                        'https://'
                    ],
                ],
                'size'       => 1,
                'maxitems'   => 1,
                'eval'       => 'required'
            ],
        ],
        'gz'            => [
            'exclude' => 1,
            'label'   => 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_sitemap.gz',
            'config'  => [
                'type' => 'check',
            ],
        ],
    ],
];
