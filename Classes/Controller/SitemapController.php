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

namespace Tollwerk\TwSitemap\Controller;

use Tollwerk\TwSitemap\Domain\Model\Sitemap;
use Tollwerk\TwSitemap\Domain\Repository\SitemapRepository;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Frontend\ContentObject\Exception\ContentRenderingException;

/**
 * Sitemap plugin controller
 *
 * @package tw_sitemap
 * @author  Dipl.-Ing. Joschi Kuphal <joschi@tollwerk.de>
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class SitemapController extends ActionController
{
    /**
     * Plugin parameter callbacs
     *
     * @var array
     */
    protected static $_pluginParameterTypeCallbacks = array(
        'repository' => 'getParameterValuesRepository',
    );
    /**
     * Sitemap entry repository
     *
     * @var SitemapRepository
     */
    protected $sitemapRepository;
    /**
     * Entry configuration
     *
     * @var array
     */
    protected $entriesConfiguration = [];

    /**
     * Inject the sitemap repositors
     *
     * @param SitemapRepository $sitemapRepository Sitemap repository
     *
     * @return void
     */
    public function injectSitemapRepository(SitemapRepository $sitemapRepository): void
    {
        $this->sitemapRepository = $sitemapRepository;
    }

    /**
     * Render an XML sitemap
     *
     * The method is called without parameters and will automatically detect the most suitable XML sitemap
     * (with or without GZIP compression).
     */
    public function indexAction(): void
    {
        /**
         * @var ServerRequest $request
         * @var Site $site
         */
        $request  = $GLOBALS['TYPO3_REQUEST'];
        $site     = $request->getAttribute('site');
        $httpHost = trim(parse_url($site->getConfiguration()['base'], PHP_URL_HOST) ?: $site->getBase()->getHost());
        $sitemap  = $this->sitemapRepository->findOneByTargetDomain($httpHost);
        if (!($sitemap instanceof Sitemap)) {
            $sitemap = $this->sitemapRepository->findOneByDomain($httpHost);
            if (!($sitemap instanceof Sitemap)) {
                $domain = $this->settings['domain'];
                if (strlen($domain)) {
                    $sitemap = $this->sitemapRepository->findOneByTargetDomain($domain);
                    if (!($sitemap instanceof Sitemap)) {
                        $sitemap = $this->sitemapRepository->findOneByDomain($domain);
                    }
                }
            }
        }

        // If a matching sitemap entry was found
        if ($sitemap instanceof Sitemap) {
            $sitemapDirectory = Environment::getPublicPath().'/typo3temp/tw_sitemap/'.$sitemap->getUid().'/';
            $sitemapPath      = $sitemapDirectory.'sitemap.xml';
            if (@is_file($sitemapPath) && @is_readable($sitemapPath)) {
                header('Content-Type: application/xml; charset=utf-8');
                if ($gzCompress = $GLOBALS['TSFE']->type == 1211) {
                    ob_start('ob_gzhandler');
                }
                readfile($sitemapPath);
                ob_end_flush();
                exit;
            }
        }
    }

    /**
     * Rendering of sitemap entries based on a TypoScript configuration
     *
     * @param string $typoscript TypoScript key
     *
     * @throws ContentRenderingException
     */
    public function typoscriptAction($typoscript = null): void
    {
        header('Content-Type: text/xml; charset=utf-8');

        $typoscriptService          = GeneralUtility::makeInstance(TypoScriptService::class);
        $this->entriesConfiguration = array_key_exists('entries', $this->settings) ?
            $typoscriptService->convertPlainArrayToTypoScriptArray((array)$this->settings['entries']) : [];
        $typoscript                 = strval($typoscript);
        $tsConfig                   = (
            strlen($typoscript) && array_key_exists("$typoscript.", $this->entriesConfiguration)
            && array_key_exists('entries.', $this->entriesConfiguration["$typoscript."])) ?
            $this->entriesConfiguration["$typoscript."]['entries.'] : null;
        $tsResult                   = is_array($tsConfig) ?
            $GLOBALS['TSFE']->cObj->getContentObject('COA')->render($tsConfig) : '';
        die("<entries>$tsResult</entries>");
    }

    /**
     * Rendering of sitemap entries based on a plugin configuration
     *
     * @param string $plugin Plugin configuration key
     */
    public function pluginAction($plugin = null): void
    {
        header('Content-Type: text/xml; charset=utf-8');

        $typoscriptService          = GeneralUtility::makeInstance(TypoScriptService::class);
        $this->entriesConfiguration = array_key_exists('entries', $this->settings) ?
            $typoscriptService->convertPlainArrayToTypoScriptArray((array)$this->settings['entries']) : [];
        $plugin                     = strval($plugin);
        $pluginConfig               = (strlen($plugin) && array_key_exists("$plugin.",
                $this->entriesConfiguration) && array_key_exists('entries.',
                $this->entriesConfiguration["$plugin."])) ? $this->entriesConfiguration["$plugin."]['entries.'] : null;

        // Determine the parameter basename
        $extensionName = array_key_exists('extension', $pluginConfig) ? trim($pluginConfig['extension']) : null;
        $pluginName    = array_key_exists('plugin', $pluginConfig) ? trim($pluginConfig['plugin']) : null;
        $controller    = array_key_exists('controller', $pluginConfig) ? trim($pluginConfig['controller']) : null;
        $action        = array_key_exists('action', $pluginConfig) ? trim($pluginConfig['action']) : null;
        $parameter     = array_key_exists('parameter.', $pluginConfig) ? (array)$pluginConfig['parameter.'] : [];

        // If the parameter basename is defined and there's an alternating parameter
        if (strlen($extensionName) && strlen($pluginName) && strlen($controller) && strlen($action) && count($parameter)) {
            $this->view->assign('extensionName', $extensionName);
            $this->view->assign('pluginName', $pluginName);
            $this->view->assign('controller', $controller);
            $this->view->assign('action', $action);
            $this->view->assign('pageUid', $GLOBALS['TSFE']->id);

            // Define the parameter name and type
            $parameters    = [];
            $parameterName = array_key_exists('name', $parameter) ? trim($parameter['name']) : null;
            $parameterType = array_key_exists('type', $parameter) ? trim($parameter['type']) : null;

            // If there are reasonable values
            if (strlen($parameterName)
                && strlen($parameterType)
                && array_key_exists($parameterType, self::$_pluginParameterTypeCallbacks)
            ) {
                $parameters[$parameterName] = call_user_func(
                    [$this, self::$_pluginParameterTypeCallbacks[$parameterType]],
                    $parameter,
                    $this->entriesConfiguration["$plugin."]
                );
                $this->view->assign('parameters', $parameters);
                die($this->view->render());

            } else {
                exit;
            }

        } else {
            exit;
        }
    }

    /**
     * Creation of repository related parameter values
     *
     * @param array $config       Parameter configuration
     * @param array $pluginConfig Global configuration
     *
     * @return array Parameter values
     * @throws Exception
     */
    protected function getParameterValuesRepository($config, $pluginConfig): array
    {
        $values        = [];
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $repository    = $objectManager->get($config['repository']);
        if ($repository instanceof Repository) {
            $repositoryConfig = array_key_exists('repository.', $config) ? (array)$config['repository.'] : [];

            // Determine valid storage PIDs
            $storagePids = array_key_exists('storagePid', $repositoryConfig) ?
                (strlen(trim($repositoryConfig['storagePid'])) ? GeneralUtility::trimExplode(',',
                    trim($repositoryConfig['storagePid'])) : []) : array($GLOBALS['TSFE']->id);

            // Create a query
            $query = $repository->createQuery();
            $query->getQuerySettings()->setStoragePageIds($storagePids);

            // TODO: WHERE clauses etc.

            // Run and process the query
            foreach ($query->execute() as $object) {
//                echo gettype($object).PHP_EOL;

                // If only one object property is relevant ...
                if (array_key_exists('column', $repositoryConfig) && strlen(trim($repositoryConfig['column']))) {
                    $values[] = @call_user_func(array($object, 'get'.ucfirst($repositoryConfig['column'])));

                    // Else
                } else {
                    $values[] = $object;
                }
            }
        }

        return $values;
    }
}
