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

$EM_CONF[$_EXTKEY] = array(
    'title' => 'tollwerk XML Sitemap',
    'description' => 'Advanced Google XML Sitemap generation incl. various entry sources and multiple sitemap file support',
    'category' => 'plugin',
    'author' => 'Dipl.-Ing. Joschi Kuphal',
    'author_email' => 'joschi@tollwerk.de',
    'author_company' => 'tollwerk GmbH',
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
    'version' => '0.2.0',
    'constraints' =>
        array(
            'depends' =>
                array(
                    'extbase' => '6.0.0-0.0.0',
                    'fluid' => '6.0.0-0.0.0',
                    'php' => '5.3.0-0.0.0',
                    'typo3' => '6.0.0-8.99.99',
                ),
            'conflicts' =>
                array(),
            'suggests' =>
                array(),
        ),
    'suggests' =>
        array(),
    'conflicts' => '',
);
