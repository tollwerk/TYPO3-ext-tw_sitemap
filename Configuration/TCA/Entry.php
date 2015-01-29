<?php

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

if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$GLOBALS['TCA']['tx_twsitemap_domain_model_entry'] = array(
	'ctrl' => $GLOBALS['TCA']['tx_twsitemap_domain_model_entry']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'domain, origin, loc, lastmod, changefreq, priority',
	),
	'types' => array(
		'1' => array('showitem' => 'domain, origin, source, language, loc, lastmod, changefreq, priority'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
	'columns' => array(
		'domain' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_entry.domain',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,required'
			),
		),
		'origin' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_entry.origin',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,required'
			),
		),
		'loc' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_entry.loc',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,required'
			),
		),
		'lastmod' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_entry.lastmod',
			'config' => array(
				'type' => 'input',
				'size' => 12,
				'max' => 20,
				'eval' => 'datetime,required',
				'checkbox' => 1,
				'default' => time()
			),
		),
		'changefreq' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_entry.changefreq',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_entry.changefreq.I.0', 0),
					array('LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_entry.changefreq.I.1', 1),
					array('LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_entry.changefreq.I.2', 2),
					array('LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_entry.changefreq.I.3', 3),
					array('LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_entry.changefreq.I.4', 4),
					array('LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_entry.changefreq.I.5', 5),
					array('LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_entry.changefreq.I.6', 6),
				),
				'size' => 1,
				'maxitems' => 1,
				'eval' => 'required'
			),
		),
		'priority' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_entry.priority',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'double2,required',
				'range' => array(
					'lower' => 0,
					'upper' => 1,
				),
			),
		),
		'language' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_entry.language',
			'config' => array(
				'type' => 'input',
				'size' => 4,
				'max' => 5,
				'eval' => 'trim',
				'checkbox' => 1,
			),
		),
		'source' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:tx_twsitemap_domain_model_entry.source',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'max' => 32,
				'eval' => 'trim',
				'checkbox' => 1,
			),
		),
	),
);

?>