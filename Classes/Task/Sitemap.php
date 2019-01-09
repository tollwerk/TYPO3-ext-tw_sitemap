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

use Tollwerk\TwSitemap\Domain\Model\Entry;
use Tollwerk\TwSitemap\Domain\Model\Sitemap as SitemapRecord;
use Tollwerk\TwSitemap\Domain\Repository\SitemapRepository;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Scheduler task for creating an XML sitemap
 *
 * @package tw_sitemap
 * @author  Dipl.-Ing. Joschi Kuphal <joschi@tollwerk.de>
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class Sitemap extends AbstractTask
{
    /**
     * Maximum number of URLs per sitemap
     *
     * @var int
     */
    const URL_LIMIT = 50000;
    /**
     * Maximum file size per sitemap
     *
     * @var int
     */
    const SIZE_LIMIT = 50000000;
    /**
     * Sitemap intro
     *
     * @var string
     */
    const SITEMAP_PRIMER = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns:xhtml="http://www.w3.org/1999/xhtml">';
    /**
     * Sitemap outro
     *
     * @var string
     */
    const SITEMAP_FOOTER = '</urlset>';
    /**
     * Sitemap index intro
     *
     * @var string
     */
    const SITEMAP_INDEX_PRIMER = '<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    /**
     * Sitemap index outro
     *
     * @var string
     */
    const SITEMAP_INDEX_FOOTER = '</sitemapindex>';

    /**
     * Run the sitemap creation
     *
     * @return bool Success
     * @throws Exception
     */
    public function execute(): bool
    {
        /* @var $sitemapModel SitemapRepository */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $sitemapModel  = $objectManager->get(SitemapRepository::class);
        $success       = true;

        // Run through all registered sitemaps
        /* @var $sitemap SitemapRecord */
        foreach ($sitemapModel->findAll() as $sitemap) {
            $success = $success && $this->generateSitemap($sitemap);
        }

        return $success;
    }

    /**
     * Create a single sitemap
     *
     * @param SitemapRecord $sitemap XML sitemap
     *
     * @return bool Success
     * @throws Exception
     */
    protected function generateSitemap(SitemapRecord $sitemap): bool
    {
        $sitemapDomain       = trim($sitemap->getDomain(), '/ ');
        $sitemapTargetDomain = trim($sitemap->getTargetDomain(), '/ ');
        $sitemapDirectory    = PATH_site.'typo3temp/tw_sitemap/'.$sitemap->getUid().'/';
        $sitemapTmpDirectory = PATH_site.'typo3temp/tw_sitemap/'.$sitemap->getUid().'.tmp/';

        // Remove existing temporary directory
        if (@is_dir($sitemapTmpDirectory)) {
            if (!$this->deleteDirectory($sitemapTmpDirectory)) {
                $this->addMessage('Sitemap temporary directory could not be deleted', FlashMessage::ERROR);

                return false;
            }
        }

        // Create temporary directory
        if (!@mkdir($sitemapTmpDirectory, 0777, true) || !@chmod($sitemapTmpDirectory, 0777)) {
            $this->addMessage('Sitemap temporary directory could not be created', FlashMessage::ERROR);

            return false;
        }

        // Run through all entries
        $entryConnection = GeneralUtility::makeInstance(ConnectionPool::class)
                                         ->getConnectionForTable('tx_twsitemap_domain_model_entry');
        $entryQuery      = $entryConnection->createQueryBuilder();
        $entryQuery->getRestrictions()->removeAll();
        $queryBuilder = $entryQuery->getConcreteQueryBuilder();

        $queryBuilder->select(
            'GROUP_CONCAT('.$entryConnection->quoteIdentifier('loc').' ORDER BY '.$entryConnection->quoteIdentifier('position').' ASC) AS '.$entryConnection->quoteIdentifier('loc'),
            'MAX('.$entryConnection->quoteIdentifier('lastmod').') AS '.$entryConnection->quoteIdentifier('lastmod'),
            'MIN('.$entryConnection->quoteIdentifier('changefreq').') AS '.$entryConnection->quoteIdentifier('changefreq'),
            'MAX('.$entryConnection->quoteIdentifier('priority').') as '.$entryConnection->quoteIdentifier('priority'),
            'GROUP_CONCAT('.$entryConnection->quoteIdentifier('language').' ORDER BY '.$entryConnection->quoteIdentifier('position').' ASC) AS '.$entryConnection->quoteIdentifier('language')
        );
        $entryQuery->from('tx_twsitemap_domain_model_entry')
                   ->where($entryQuery->expr()->eq('deleted', $entryQuery->createNamedParameter(0)))
                   ->andWhere($entryQuery->expr()->eq('domain', $entryQuery->createNamedParameter($sitemapDomain)));
        $queryBuilder->groupBy(
            'CONCAT('.$entryConnection->quoteIdentifier('origin').','.
            $entryQuery->expr()->literal('~').','.
            $entryConnection->quoteIdentifier('source').')'
        );
        $entryQuery->orderBy('priority', 'DESC')->addOrderBy('lastmod', 'DESC');
        $entryResult = $entryQuery->execute();
        if ($entryResult && $entryResult->rowCount()) {
            $sitemapSchemePath      = $sitemap->getScheme().(strlen($sitemapTargetDomain) ? $sitemapTargetDomain : $sitemapDomain);
            $sitemapGzip            = (boolean)intval($sitemap->getGz());
            $sitemapFooterLength    = strlen(self::SITEMAP_FOOTER);
            $sitemapFiles           = [];
            $currentSitemapSize     = 0;
            $currentSitemapResource = $this->startSitemapFile(
                $sitemapTmpDirectory,
                $sitemapFiles,
                $sitemapGzip,
                $currentSitemapSize
            );
            $sitemapURLs            = 0;

            // Retrieve all entries
            while ($sitemapEntryRecord = $entryResult->fetch()) {
                $languages = GeneralUtility::trimExplode(',', $sitemapEntryRecord['language'], true);
                $locs      = explode(',', $sitemapEntryRecord['loc']);

                // Create the XML sitemap entry
                $sitemapEntry = '<url><loc>'.htmlspecialchars($sitemapSchemePath.$locs[0]).'</loc><lastmod>'.
                                date('Y-m-d', $sitemapEntryRecord['lastmod']).'T'.
                                date('H:i:s', $sitemapEntryRecord['lastmod']).'Z</lastmod><changefreq>'.
                                Entry::$changefreqs[$sitemapEntryRecord['changefreq']].'</changefreq><priority>'.
                                number_format($sitemapEntryRecord['priority'], 1).'</priority>';

                if (count($languages) > 1) {
                    foreach ($languages as $index => $language) {
                        $sitemapEntry .= '<xhtml:link rel="alternate" hreflang="'.htmlspecialchars($language).
                                         '" href="'.htmlspecialchars($sitemapSchemePath.$locs[$index]).'"/>';
                    }
                }

                $sitemapEntry       .= '</url>';
                $sitemapEntryLength = strlen($sitemapEntry);
                $sitemapProvLength  = ($currentSitemapSize + $sitemapEntryLength + $sitemapFooterLength);

                // If the sitemap would exceed the size limit: Start a new file
                if (($sitemapProvLength > self::SIZE_LIMIT) || (($sitemapURLs + 1) > self::URL_LIMIT)) {
                    $this->_stopSitemapFile($currentSitemapResource, $sitemapGzip);
                    $currentSitemapResource = $this->startSitemapFile(
                        $sitemapTmpDirectory,
                        $sitemapFiles,
                        $sitemapGzip,
                        $currentSitemapSize
                    );
                    $sitemapProvLength      = ($currentSitemapSize + $sitemapEntryLength + $sitemapFooterLength);
                    $sitemapURLs            = 0;
                }

                // Write to file
                $currentSitemapSize += $sitemapGzip ?
                    fwrite($currentSitemapResource, $sitemapEntry) : gzwrite($currentSitemapResource, $sitemapEntry);
                ++$sitemapURLs;
            }

            // Close the sitemap file
            $this->_stopSitemapFile($currentSitemapResource, $sitemapGzip);

            // Fix file privileges
            foreach ($sitemapFiles as $sitemapFile) {
                chmod($sitemapTmpDirectory.DIRECTORY_SEPARATOR.$sitemapFile, 0777);
            }

            // Create a sitemap index when there are multiple sitemaps
            if (count($sitemapFiles) > 1) {
                $success = $this->_createSitemapIndex($sitemapTmpDirectory, $sitemapFiles, $sitemapGzip,
                    $sitemapSchemePath.'/typo3temp/tw_sitemap/'.$sitemap->getUid().'/');

                // Otherwise: Rename sitemap file
            } else {
                $success = rename(
                    $sitemapTmpDirectory.DIRECTORY_SEPARATOR.$sitemapFiles[0],
                    $sitemapTmpDirectory.DIRECTORY_SEPARATOR.'sitemap.xml'.($sitemapGzip ? '.gz' : '')
                );
            }

            // When the sitemap was created successfully: Remove temporary resources
            if ($success) {
                if (@is_dir($sitemapDirectory) && !$this->deleteDirectory($sitemapDirectory)) {
                    throw new Exception('Previous sitemap version could not be removed');
                }

                return (boolean)rename($sitemapTmpDirectory, $sitemapDirectory);

                // Otherwise: error
            } else {
                throw new Exception('Sitemap generation could not be finalized');
            }
        }

        return true;
    }

    /**
     * Open an existing sitemap file
     *
     * @param string $directory   Directory
     * @param array $sitemapFiles Sitemap files
     * @param bool $gz            Use GZIP compression
     * @param int $bytes          Byte size
     *
     * @return resource Sitemap file
     */
    protected function startSitemapFile(string $directory, array &$sitemapFiles, bool $gz, int &$bytes)
    {
        $sitemapName = 'sitemap-'.(count($sitemapFiles) + 1).'.xml';
        $sitemapPath = rtrim($directory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        if ((boolean)$gz) {
            $sitemapName .= '.gz';
            $sitemap     = gzopen($sitemapPath.$sitemapName, 'wb');
            $bytes       = gzwrite($sitemap, self::SITEMAP_PRIMER);
        } else {
            $sitemap = fopen($sitemapPath.$sitemapName, 'wb');
            $bytes   = fwrite($sitemap, self::SITEMAP_PRIMER);
        }
        $sitemapFiles[] = $sitemapName;

        return $sitemap;
    }

    /**
     * Close a sitemap file
     *
     * @param resource $sitemap Sitemap resource
     * @param boolean $gz       Use GZIP compression
     */
    protected function _stopSitemapFile($sitemap, $gz): void
    {
        if ((boolean)$gz) {
            gzwrite($sitemap, self::SITEMAP_FOOTER);
            gzclose($sitemap);
        } else {
            fwrite($sitemap, self::SITEMAP_FOOTER);
            fclose($sitemap);
        }
    }

    /**
     * Create a sitemap index
     *
     * @param string $directory   Directory
     * @param array $sitemapFiles Sitemap files
     * @param boolean $gz         Use GZIP compression
     * @param string $urlbase     Base URL for single sitemap files
     *
     * @return bool Succes
     */
    protected function _createSitemapIndex(string $directory, array $sitemapFiles, bool $gz, string $urlbase): bool
    {
        $sitemapIndex = self::SITEMAP_INDEX_PRIMER;
        $now          = date('Y-m-d');
        foreach ($sitemapFiles as $sitemapFile) {
            $sitemapIndex .= '<sitemap><loc>'.$urlbase.$sitemapFile.'</loc><lastmod>'.$now.'</lastmod></sitemap>';
        }
        $sitemapIndex     .= self::SITEMAP_INDEX_FOOTER;
        $sitemapIndexPath = rtrim($directory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'sitemap.xml';
        if ((boolean)$gz) {
            $sitemapIndexPath     .= '.gz';
            $sitemapIndexResource = gzopen($sitemapIndexPath, 'wb');
            $bytes                = gzwrite($sitemapIndexResource, $sitemapIndex);
            gzclose($sitemapIndexResource);
        } else {
            $bytes = file_put_contents($sitemapIndexPath, $sitemapIndex);
        }
        @chmod($sitemapIndexPath, 0777);

        return (boolean)$bytes;
    }

    /**
     * Recursively delete a directory
     *
     * @param string $directory Directory
     *
     * @return bool Success
     */
    protected function deleteDirectory($directory)
    {
        clearstatcache();
        $directory = rtrim($directory, DIRECTORY_SEPARATOR);
        if (@is_dir($directory)) {
            foreach (scandir($directory) as $filedir) {
                if (($filedir == '.') || ($filedir == '..')) {
                    continue;
                } elseif (@is_dir($directory.DIRECTORY_SEPARATOR.$filedir)) {
                    if (!$this->deleteDirectory($directory.DIRECTORY_SEPARATOR.$filedir)) {
                        return false;
                    }
                } elseif (!unlink($directory.DIRECTORY_SEPARATOR.$filedir)) {
                    return false;
                }
            }
            if (!rmdir($directory)) {
                return false;
            }
        }

        return true;
    }
}
