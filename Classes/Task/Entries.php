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

namespace Tollwerk\TwSitemap\Task;

use Tollwerk\TwBase\Utility\CurlUtility;
use Tollwerk\TwSitemap\Domain\Model\Entry;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;
use TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Scheduler\Task\AbstractTask as Typo3AbstractTask;

/**
 * Scheduler task for creating XML sitemap entries
 *
 * @package tw_sitemap
 * @author  Dipl.-Ing. Joschi Kuphal <joschi@tollwerk.de>
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class Entries extends AbstractTask implements AdditionalFieldProviderInterface
{
    /**
     * Root page
     *
     * @var int
     */
    protected $root;
    /**
     * Base URL for sitemap entry requests
     *
     * @var array
     */
    protected $baseUrl;
    /**
     * TypoScript service
     *
     * @var TypoScriptService
     */
    protected $typoscriptService = null;
    /**
     * Configuration types
     *
     * @var array
     */
    protected static $_configTypes = array(
        self::CONFIG_TYPE_FILE       => 'generateFileEntries',
        self::CONFIG_TYPE_PLUGIN     => 'generatePluginEntries',
        self::CONFIG_TYPE_TYPOSCRIPT => 'generateTyposcriptEntries',
    );
    /**
     * Current cycle timestamp
     *
     * @var int
     */
    protected $cycle = null;
    /**
     * Timeout for file requests
     *
     * @var int
     */
    protected $timeout = 600;
    /**
     * Context for file requests
     *
     * @var resource
     */
    protected $httpContext = null;
    /**
     * Locale locale indices
     *
     * @var array
     */
    protected $localeIndices = array();
    /**
     * Enforce the HTTPS scheme on sitemap links
     *
     * @var bool
     */
    protected $enforceHttps = false;
    /**
     * Debug mode
     *
     * @var bool
     */
    protected $debug = false;
    /**
     * Configuration type: Typoscript
     *
     * @var string
     */
    const CONFIG_TYPE_TYPOSCRIPT = 'typoscript';
    /**
     * Configuration type: File
     *
     * @var string
     */
    const CONFIG_TYPE_FILE = 'file';
    /**
     * Configuration type: Plugin
     *
     * @var string
     */
    const CONFIG_TYPE_PLUGIN = 'plugin';
    /**
     * Page types
     */
    const TYPENUMS = [
        self::CONFIG_TYPE_TYPOSCRIPT => 1212,
        self::CONFIG_TYPE_PLUGIN     => 1213,
    ];

    /**
     * Run the entry generation
     *
     * @return bool Success
     * @throws Exception
     */
    public function execute(): bool
    {
        // Determine the TypoScript setup for sitemap entries
        $_GET['id']   = intval($this->root);
        $setup        = GeneralUtility::makeInstance(BackendConfigurationManager::class)->getTypoScriptSetup();
        $settings     = $setup['plugin.']['tx_twsitemap.']['settings.'];
        $entriesSetup = $settings['entries.'];

        // Make sure there's a language parameter defined
        if (!array_key_exists('lang', $settings) || !strlen(trim($settings['lang']))) {
            $this->addMessage(
                'Invalid language parameter definition — please check your constant settings.',
                FlashMessage::ERROR
            );

            return false;
        }

        // Make sure there's a base URL defined
        if (!array_key_exists('baseUrl', $settings) || !strlen(trim($settings['baseUrl']))) {
            $this->addMessage(
                'Invalid base URL definition — please check your constant settings.',
                FlashMessage::ERROR
            );

            return false;
        }

        if (count($entriesSetup)) {
            $this->typoscriptService = GeneralUtility::makeInstance(TypoScriptService::class);
            $this->cycle             = time();
            $this->enforceHttps      = (boolean)$settings['https'];
            $this->httpContext       = stream_context_create([
                'http' => ['timeout' => $this->timeout],
                'ssl'  => ['verify_peer' => false],
            ]);

            // Refine the base URL
            $this->baseUrl = (array)parse_url($settings['baseUrl']);
            if (!array_key_exists('scheme', $this->baseUrl)) {
                $this->baseUrl['scheme'] = 'http';
            }
            $this->baseUrl['path'] = '/index.php';
            parse_str(array_key_exists('query', $this->baseUrl) ? $this->baseUrl['query'] : '',
                $this->baseUrl['query']);
            $this->baseUrl['query']['no_cache'] = 1;

            // Run through all sitemap entry setups
            foreach ($entriesSetup as $key => $value) {
                $this->generateEntries(trim($key, '.'), $value, $setup['plugin.']['tx_twsitemap.']['settings.']);
            }
        }

        return true;
    }

    /**
     * Customized error handling for XML loading
     *
     * @param int $errno      Error number
     * @param string $errstr  Error description
     * @param string $errfile File
     * @param int $errline    Line
     *
     * @return bool FALSE if regular handling should succeed
     * @throws \DOMException If the XML document is invalid
     */
    public function loadError(int $errno, string $errstr, string $errfile, int $errline)
    {
        if (($errno == E_WARNING) && ((substr_count($errstr, "\DOMDocument::loadXML()") > 0) || (substr_count($errstr,
                        "\DOMDocument::load()") > 0))
        ) {
            throw new \DOMException($errstr, DOM_VALIDATION_ERR);
        } else {
            return false;
        }
    }

    /**
     * Creation of XML sitemap entries based on a configuration
     *
     * @param string $key     Configuration key
     * @param array $config   Configuration definition
     * @param array $settings Settings
     *
     * @throws Exception
     */
    protected function generateEntries(string $key, array $config, array $settings): void
    {
        // Find the rendering base page
        $pid = array_key_exists('pid', $config) ? intval($config['pid']) : 0;

        // Determine the entry domain
        $domain = array_key_exists('domain', $config) ? trim($config['domain']) : null;

        // Stop in case of missing parameters
        if (($pid <= 0) || !strlen($domain)) {
            return;
        }

        // Language setup
        $langParam           = trim($settings['lang']);
        $languages           = (array_key_exists('languages', $config) && strlen(trim($config['languages']))) ?
            GeneralUtility::trimExplode(',', trim($config['languages'])) : [''];
        $locales             = (array_key_exists('locales', $config) && strlen(trim($config['locales']))) ?
            GeneralUtility::trimExplode(',', trim($config['locales'])) : [];
        $locales             = array_pad($locales, count($languages), '');
        $this->localeIndices = array_flip($locales);

        // Origin setup
        $origin = array_key_exists('origin', $config) ? strval($config['origin']) : md5(serialize($config));

        // Change frequency setup
        $changefreq = array_key_exists('changefreq', $config) ?
            strtolower($config['changefreq']) : Entry::$changefreqs[Entry::CHANGEFREQ_NEVER];
        if (!in_array($changefreq, Entry::$changefreqs)) {
            $changefreq = Entry::$changefreqs[Entry::CHANGEFREQ_NEVER];
        }

        // Priority setup
        $priority = array_key_exists('priority', $config) ? floatval($config['priority']) : 0.5;
        $priority = max(0, min(1, $priority));

        // Entry setup
        $entryType = array_key_exists('entries', $config) ? strtolower($config['entries']) : null;
        if (strlen($entryType)
            && array_key_exists($entryType, self::$_configTypes)
            && array_key_exists('entries.', $config)
        ) {
            $baseUrl = $this->baseUrl;

            if (array_key_exists('baseUrl', $config)) {
                if (is_string($config['baseUrl'])) {
                    ArrayUtility::mergeRecursiveWithOverrule($baseUrl, parse_url($config['baseUrl']));
                }
            } elseif (array_key_exists('baseUrl.', $config)) {
                ArrayUtility::mergeRecursiveWithOverrule(
                    $baseUrl,
                    $this->typoscriptService->convertTypoScriptArrayToPlainArray($config['baseUrl.'])
                );
            }

            $baseUrl['query']['id'] = $pid;

            // Run through all languages
            foreach ($languages as $languageIndex => $language) {
                if (strlen($language)) {
                    $baseUrl['query'][$langParam] = $language;
                }

                // Delegate to entry generation method
                call_user_func(
                    [$this, self::$_configTypes[$entryType]],
                    $key,
                    $config['entries.'],
                    $baseUrl,
                    $domain,
                    $origin,
                    $changefreq,
                    $priority,
                    $locales[$languageIndex]
                );
            }
        }

        // Delete old entries for the same origins which haven't been updated
        $entryConnection = GeneralUtility::makeInstance(ConnectionPool::class)
                                         ->getConnectionForTable('tx_twsitemap_domain_model_entry');
        $entryQuery      = $entryConnection->createQueryBuilder();
        $entryQuery->getRestrictions()->removeAll();
        $entryQuery->delete('tx_twsitemap_domain_model_entry')
                   ->where($entryQuery->expr()->eq('origin', $entryQuery->createNamedParameter($origin)))
                   ->andWhere($entryQuery->expr()->lt('tstamp', $entryQuery->createNamedParameter($this->cycle)));
        $entryQuery->execute();
    }

    /**
     * Creation of XML sitemap entries from an XML file
     *
     * @param string $key               Configuration key
     * @param array $config             TypoScript configuration
     * @param array $baseUrl            Request base URL
     * @param string $domain            Sitemap domain
     * @param string $defaultOrigin     Default entry origin
     * @param string $defaultChangefreq Default change frequency
     * @param float $defaultPriority    Defailt priority
     * @param string $defaultLocale     Default locale
     *
     * @throws Exception
     */
    protected function generateFileEntries(
        string $key,
        array $config,
        array $baseUrl,
        string $domain,
        string $defaultOrigin,
        string $defaultChangefreq,
        float $defaultPriority,
        string $defaultLocale
    ): void {
        if (array_key_exists('path', $config) && strlen(trim($config['path']))) {
            $path = PATH_site.trim($config['path']);
            if (@is_file($path) && @is_readable($path)) {
                $this->generateEntriesByXML(file_get_contents($path, 0, $this->httpContext), $domain, $defaultOrigin,
                    $defaultChangefreq, $defaultPriority, $defaultLocale);
            }
        }
    }

    /**
     * Creation of XML sitemap entries from a plugin
     *
     * @param string $key               Configuration key
     * @param array $config             TypoScript configuration
     * @param array $baseUrl            Request base URL
     * @param string $domain            Sitemap domain
     * @param string $defaultOrigin     Default entry origin
     * @param string $defaultChangefreq Default change frequency
     * @param float $defaultPriority    Defailt priority
     * @param string $defaultLocale     Default locale
     *
     * @throws Exception
     */
    protected function generatePluginEntries(
        string $key,
        array $config,
        array $baseUrl,
        string $domain,
        string $defaultOrigin,
        string $defaultChangefreq,
        float $defaultPriority,
        string $defaultLocale
    ): void {
        $this->generateEntriesViaRequest(
            self::CONFIG_TYPE_PLUGIN,
            $key,
            $baseUrl,
            $domain,
            $defaultOrigin,
            $defaultChangefreq,
            $defaultPriority,
            $defaultLocale
        );
    }

    /**
     * Creation of XML sitemap entries from a TypoScript configuration
     *
     * @param string $key               Configuration key
     * @param array $config             TypoScript configuration
     * @param array $baseUrl            Request base URL
     * @param string $domain            Sitemap domain
     * @param string $defaultOrigin     Default entry origin
     * @param string $defaultChangefreq Default change frequency
     * @param float $defaultPriority    Defailt priority
     * @param string $defaultLocale     Default locale
     *
     * @throws Exception
     */
    protected function generateTyposcriptEntries(
        string $key,
        array $config,
        array $baseUrl,
        string $domain,
        string $defaultOrigin,
        string $defaultChangefreq,
        float $defaultPriority,
        string $defaultLocale
    ): void {
        $this->generateEntriesViaRequest(
            self::CONFIG_TYPE_TYPOSCRIPT,
            $key,
            $baseUrl,
            $domain,
            $defaultOrigin,
            $defaultChangefreq,
            $defaultPriority,
            $defaultLocale
        );
    }

    /**
     * Creation of XML sitemap entries via HTTP request
     *
     * @param string $type              Request type
     * @param string $key               Configuration key
     * @param array $baseUrl            Request base URL
     * @param string $domain            Sitemap domain
     * @param string $defaultOrigin     Default entry origin
     * @param string $defaultChangefreq Default change frequency
     * @param float $defaultPriority    Defailt priority
     * @param string $defaultLocale     Default locale
     *
     * @throws Exception
     */
    protected function generateEntriesViaRequest(
        string $type,
        string $key,
        array $baseUrl,
        string $domain,
        string $defaultOrigin,
        string $defaultChangefreq,
        float $defaultPriority,
        string $defaultLocale
    ): void {
        $url                                  = $baseUrl;
        $urlCredentials                       = empty($url['user']) ?
            '' : rawurlencode($url['user']).(empty($url['pass']) ? '' : ':'.rawurlencode($url['pass'])).'@';
        $url['query']['type']                 = self::TYPENUMS[$type];
        $url['query']['tx_twsitemap_sitemap'] = array($type => $key);
        $url                                  = $url['scheme'].'://'.$urlCredentials.$url['host'].
                                                (array_key_exists('port', $url) ? ':'.$url['port'] : '').
                                                '/'.ltrim($url['path'], '/').'?'.http_build_query($url['query']);

        if ($this->debug) {
            $this->addMessage(sprintf('Fetching URL: %s', $url), FlashMessage::INFO);
        }

        $this->generateEntriesByXML(
            CurlUtility::httpRequest($url),
            $domain,
            $defaultOrigin,
            $defaultChangefreq,
            $defaultPriority,
            $defaultLocale
        );
    }

    /**
     * Creation of sitemap entries from XML source
     *
     * @param string $xml               XML
     * @param string $domain            Sitemap domain
     * @param string $defaultOrigin     Default entry origin
     * @param string $defaultChangefreq Default change frequency
     * @param float $defaultPriority    Defailt priority
     * @param string $defaultLocale     Default locale
     *
     * @throws Exception
     */
    protected function generateEntriesByXML(
        string $xml,
        string $domain,
        string $defaultOrigin,
        string $defaultChangefreq,
        float $defaultPriority,
        string $defaultLocale
    ): void {
        $xml = '<entries>'.preg_replace("%^\<\?[^\<]*?%", '', trim($xml)).'</entries>';

        // Instantiate as DOM document
        set_error_handler(array($this, 'loadError'));
        try {
            $entries                     = new \DOMDocument();
            $entries->formatOutput       = true;
            $entries->preserveWhiteSpace = true;
            $entries->loadXML($xml, LIBXML_NSCLEAN);
        } catch (\DOMException $e) {
            trigger_error($e->getCode());
            restore_error_handler();

            return;
        }
        restore_error_handler();

        // Create XPath operator
        $xpath = new \DOMXPath($entries);

        // Prepare change frequencies
        $changefreqs = array_flip(Entry::$changefreqs);

        // Run through all <a> elements
        /* @var $entry \DOMElement */
        foreach ($xpath->query('//a[@href]') as $entryIndex => $entry) {

            // Extract & normalize entry URL
            $loc      = trim($entry->getAttribute('href'));
            $locParts = parse_url($loc);
            $loc      = empty($locParts['port']) ? '' : ':'.$locParts['port'];
            $loc      .= empty($locParts['path']) ? '/' : $locParts['path'];
            $loc      .= empty($locParts['query']) ? '' : '?'.$locParts['query'];
            $loc      .= empty($locParts['fragment']) ? '' : '#'.$locParts['fragment'];

            // Extract other parameters
            $origin     = trim($entry->getAttribute('data-origin'));
            $origin     = strlen($origin) ? $origin : $defaultOrigin;
            $source     = trim($entry->getAttribute('data-source'));
            $source     = strlen($source) ? $source : $entryIndex;
            $lastmod    = trim($entry->getAttribute('data-lastmod'));
            $lastmod    = strlen($lastmod) ? intval($lastmod) : time();
            $changefreq = trim($entry->getAttribute('data-changefreq'));
            $changefreq = strtolower(strlen($changefreq) ? $changefreq : trim($defaultChangefreq));
            $changefreq = (strlen($changefreq) && array_key_exists($changefreq,
                    $changefreqs)) ? $changefreqs[$changefreq] : Entry::CHANGEFREQ_NEVER;
            $priority   = trim($entry->getAttribute('data-priority'));
            $priority   = strlen($priority) ? floatval($priority) : $defaultPriority;
            $locale     = trim($entry->getAttribute('data-locale'));
            $locale     = strlen($locale) ? strval($locale) : $defaultLocale;

            // Prepare entry record
            $entry = array(
                'domain'     => $domain,
                'origin'     => $origin,
                'source'     => $source,
                'loc'        => $loc,
                'changefreq' => $changefreq,
                'priority'   => $priority,
                'language'   => $locale,
                'position'   => $this->localeIndices[$defaultLocale],
                'lastmod'    => $lastmod,
                'tstamp'     => $this->cycle,
                'deleted'    => 0
            );

            // Insert or update the entry
            $entryConnection = GeneralUtility::makeInstance(ConnectionPool::class)
                                             ->getConnectionForTable('tx_twsitemap_domain_model_entry');
            $entryQuery      = $entryConnection->createQueryBuilder();
            $entryQuery->getRestrictions()->removeAll();
            $entryQuery->select('uid')->from('tx_twsitemap_domain_model_entry')
                       ->where($entryQuery->expr()->eq('language', $entryQuery->createNamedParameter($locale)))
                       ->andWhere($entryQuery->expr()->eq('origin', $entryQuery->createNamedParameter($origin)))
                       ->andWhere($entryQuery->expr()->eq('domain', $entryQuery->createNamedParameter($domain)))
                       ->andWhere($entryQuery->expr()->eq('source', $entryQuery->createNamedParameter($source)));
            $entryResult = $entryQuery->execute();
            $entryQuery  = $entryConnection->createQueryBuilder();
            $entryQuery->getRestrictions()->removeAll();
            if ($entryResult->rowCount()) {
                $entryUid = $entryResult->fetchColumn();
                $entryQuery->update('tx_twsitemap_domain_model_entry')
                           ->where($entryQuery->expr()->eq('uid', $entryQuery->createNamedParameter($entryUid)));
                foreach ($entry as $key => $value) {
                    $entryQuery->set($key, $value);
                }
            } else {
                $entryQuery->insert('tx_twsitemap_domain_model_entry')->values($entry);
            }
            $entryQuery->execute();
        }
    }

    /**
     * Return the additional fields
     *
     * @param array $taskInfo Task info
     * @param $task
     * @param SchedulerModuleController $parentObject
     *
     * @return array Return additional fields
     */
    public function getAdditionalFields(array &$taskInfo, $task, SchedulerModuleController $parentObject): array
    {
        $additionalFields = [];

        // Root page
        $fieldName = 'tx_scheduler[tw_sitemap_root]';
        $fieldId   = 'task_sitemap_root';
        $fieldHTML = '<select name="'.$fieldName.'" id="'.$fieldId.'">';

        // Collect root pages
        $pageConnection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');
        $pageQuery      = $pageConnection->createQueryBuilder();
        $pageQuery->getRestrictions()->removeAll();
        $pageQuery->select('*')->from('pages')
                  ->where($pageQuery->expr()->eq('is_siteroot', $pageQuery->createNamedParameter(1)));
        $pageResult = $pageQuery->execute();
        if ($pageResult) {
            while ($page = $pageResult->fetch()) {
                $fieldHTML .= '<option value="'.$page['uid'].'"'.
                              (($page['uid'] == $task->root) ? ' selected="selected"' : '').
                              '>'.htmlspecialchars($page['title']).' ('.$page['uid'].')</option>';
            }
        }

        $fieldHTML                  .= '</select>';
        $additionalFields[$fieldId] = [
            'code'     => $fieldHTML,
            'label'    => 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:scheduler.entries.root',
            'cshKey'   => '_MOD_system_txschedulerM1',
            'cshLabel' => $fieldId
        ];

        // Debug output
        $fieldName                  = 'tx_scheduler[tw_sitemap_debug]';
        $fieldId                    = 'task_sitemap_debug';
        $fieldHTML                  = '<input type="checkbox" value="1" name="'.$fieldName.'" id="'.$fieldId.'"'.((empty($task->debug) || !$task->debug) ? '' : ' checked="checked"').'"/>';
        $additionalFields[$fieldId] = [
            'code'     => $fieldHTML,
            'label'    => 'LLL:EXT:tw_sitemap/Resources/Private/Language/locallang_db.xlf:scheduler.entries.debug',
            'cshKey'   => '_MOD_system_txschedulerM1',
            'cshLabel' => $fieldId
        ];

        return $additionalFields;
    }

    /**
     * Validate the additional fields
     *
     * @param array $submittedData                    Submitted data
     * @param SchedulerModuleController $parentObject Parent controller object
     *
     * @return bool Field validity
     */
    public function validateAdditionalFields(array &$submittedData, SchedulerModuleController $parentObject): bool
    {
        $valid = true;

        $submittedData['tw_sitemap_root'] = trim($submittedData['tw_sitemap_root']);
        if (!intval($submittedData['tw_sitemap_root'])) {
            $parentObject->addMessage('The root page ID must be a valid integer', FlashMessage::ERROR);
            $valid = false;
        } else {
            if (empty(BackendUtility::getRecord('pages', intval($submittedData['tw_sitemap_root'])))) {
                $parentObject->addMessage('The root page ID must refer to a valid page', FlashMessage::ERROR);
                $valid = false;
            }
        }

        return $valid;
    }

    /**
     * Save the additional field values
     *
     * @param array $submittedData    Submitted fields
     * @param Typo3AbstractTask $task Task instance
     */
    public function saveAdditionalFields(array $submittedData, Typo3AbstractTask $task)
    {
        $task->root  = intval(trim($submittedData['tw_sitemap_root']));
        $task->debug = intval(trim($submittedData['tw_sitemap_debug']));
    }

    /**
     * Get additional information about the task
     *
     * @return string
     */
    public function getAdditionalInformation()
    {
        $root = intval($this->root);
        $root = $root ? BackendUtility::getRecord('pages', $root) : null;

        return $root ? 'Root page: '.BackendUtility::getRecordTitle('pages', $root).' ('.$this->root.')' : '';
    }
}
