<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "tw_sitemap".
 *
 * Auto generated 15-08-2013 13:20
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
	'title' => 'tollwerk® XML Sitemap',
	'description' => 'Advanced Google XML Sitemap generation incl. various entry sources and multiple sitemap file support',
	'category' => 'plugin',
	'author' => 'Dipl.-Ing. Joschi Kuphal',
	'author_email' => 'joschi@tollwerk.de',
	'author_company' => 'tollwerk® GmbH',
	'dependencies' => 'cms,extbase,fluid',
	'shy' => '',
	'priority' => '',
	'module' => '',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => 'typo3temp/tw_sitemap',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'version' => '0.1.0',
	'constraints' => 
	array (
		'depends' => 
		array (
			'extbase' => '6.0.0-0.0.0',
			'fluid' => '6.0.0-0.0.0',
			'php' => '5.3.0-0.0.0',
			'typo3' => '6.0.0-7.9.99',
		),
		'conflicts' => 
		array (
		),
		'suggests' => 
		array (
		),
	),
	'suggests' => 
	array (
	),
	'conflicts' => '',
	'_md5_values_when_last_written' => 'a:26:{s:12:"ext_icon.gif";s:4:"88db";s:17:"ext_localconf.php";s:4:"475b";s:14:"ext_tables.php";s:4:"1b9f";s:14:"ext_tables.sql";s:4:"d863";s:40:"Classes/Controller/SitemapController.php";s:4:"090e";s:30:"Classes/Domain/Model/Entry.php";s:4:"366f";s:32:"Classes/Domain/Model/Sitemap.php";s:4:"5f5d";s:45:"Classes/Domain/Repository/EntryRepository.php";s:4:"a9ed";s:47:"Classes/Domain/Repository/SitemapRepository.php";s:4:"f42c";s:24:"Classes/Task/Entries.php";s:4:"eaa4";s:24:"Classes/Task/Sitemap.php";s:4:"ea04";s:47:"Classes/ViewHelpers/Array/CombineViewHelper.php";s:4:"e03a";s:27:"Configuration/TCA/Entry.php";s:4:"a2db";s:29:"Configuration/TCA/Sitemap.php";s:4:"3c49";s:38:"Configuration/TypoScript/constants.txt";s:4:"e840";s:34:"Configuration/TypoScript/setup.txt";s:4:"21cc";s:40:"Resources/Private/Language/locallang.xml";s:4:"c52f";s:76:"Resources/Private/Language/locallang_csh_tx_twsitemap_domain_model_entry.xml";s:4:"c869";s:78:"Resources/Private/Language/locallang_csh_tx_twsitemap_domain_model_sitemap.xml";s:4:"75a5";s:43:"Resources/Private/Language/locallang_db.xml";s:4:"a4a7";s:46:"Resources/Private/Templates/Sitemap/Index.html";s:4:"9fb0";s:47:"Resources/Private/Templates/Sitemap/Plugin.html";s:4:"3bf5";s:51:"Resources/Private/Templates/Sitemap/Typoscript.html";s:4:"d41d";s:35:"Resources/Public/Icons/relation.gif";s:4:"e615";s:58:"Resources/Public/Icons/tx_twsitemap_domain_model_entry.gif";s:4:"50ff";s:60:"Resources/Public/Icons/tx_twsitemap_domain_model_sitemap.gif";s:4:"905a";}',
);

?>