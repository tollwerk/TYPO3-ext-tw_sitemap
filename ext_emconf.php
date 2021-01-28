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
    'title'            => 'tollwerk XML Sitemap',
    'description'      => 'Advanced Google XML Sitemap generation incl. various entry sources and multiple sitemap file support',
    'category'         => 'plugin',
    'author'           => 'Dipl.-Ing. Joschi Kuphal',
    'author_email'     => 'joschi@tollwerk.de',
    'author_company'   => 'tollwerk GmbH',
    'dependencies'     => 'cms,extbase,fluid',
    'shy'              => '',
    'priority'         => '',
    'module'           => '',
    'state'            => 'stable',
    'internal'         => '',
    'uploadfolder'     => 0,
    'createDirs'       => 'typo3temp/tw_sitemap',
    'modify_tables'    => '',
    'clearCacheOnLoad' => 0,
    'lockType'         => '',
    'version'          => '1.0.1',
    'constraints'      =>
        array(
            'depends'   =>
                array(
                    'extbase' => '9.5.0-',
                    'fluid'   => '9.5.0-',
                    'php'     => '7.0.0-',
                    'typo3'   => '9.5.0-',
                ),
            'conflicts' =>
                array(),
            'suggests'  =>
                array(),
        ),
    'suggests'         =>
        array(),
    'conflicts'        => '',
);
