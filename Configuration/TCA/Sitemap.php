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

if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$GLOBALS['TCA']['tx_twsitemap_domain_model_sitemap'] = array(
	'ctrl' => $GLOBALS['TCA']['tx_twsitemap_domain_model_sitemap']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'domain, target_domain, scheme, gz',
	),
	'types' => array(
		'1' => array('showitem' => 'domain, target_domain, scheme, gz'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
	'columns' => array(
		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config' => array(
				'type' => 'check',
			),
		),
		'starttime' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),
		'endtime' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),
		'domain' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_sitemap.domain',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,required'
			),
		),
		'target_domain' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_sitemap.target_domain',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'scheme' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_sitemap.scheme',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_sitemap.scheme.I.0', 'http://'),
					array('LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_sitemap.scheme.I.1', 'https://'),
				),
				'size' => 1,
				'maxitems' => 1,
				'eval' => 'required'
			),
		),
		'gz' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_sitemap.gz',
			'config' => array(
				'type' => 'check',
			),
		),
	),
);
?>