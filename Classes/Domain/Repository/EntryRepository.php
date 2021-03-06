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

namespace Tollwerk\TwSitemap\Domain\Repository;

use DateTimeInterface;
use Tollwerk\TwSitemap\Domain\Model\Entry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * XML sitemap entry repository
 *
 * @package tw_sitemap
 * @author  Dipl.-Ing. Joschi Kuphal <joschi@tollwerk.de>
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class EntryRepository extends Repository
{
    /**
     * Default ordering
     *
     * @var array
     */
    protected $defaultOrderings = array(
        'priority' => QueryInterface::ORDER_DESCENDING,
        'loc'      => QueryInterface::ORDER_ASCENDING
    );

    /**
     * Add objects from an array
     *
     * @param array $objectArray Object array
     *
     * @throws IllegalObjectTypeException
     */
    public function addObjectsFromArray(array $objectArray): void
    {
        // Run through all objects
        foreach ($objectArray as $objectData) {
            $object = new $this->objectType();
            $object->setFromArray($objectData);
            $this->add($object);
        }

        $persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
        $persistenceManager->persistAll();
    }

    /**
     * Find an entry for a domain and URL
     *
     * @param string $domain Domain
     * @param string $loc    URL
     *
     * @return Entry|null Sitemap entry
     */
    public function findOneByDomainLoc(string $domain, string $loc): ?Entry
    {
        $query = $this->createQuery();
        $query->matching($query->logicalAnd(array(
            $query->equals('domain', $domain),
            $query->equals('loc', $loc),
        )));
        $result = $query->execute();

        return count($result) ? $result->getFirst() : null;
    }

    /**
     * Return the last modification date (all sites / sitemaps)
     *
     * @param string|null $domain Optional: Domain
     *
     * @return DateTimeInterface|null Last modification Date
     */
    public function findLastModificationDate(string $domain = null): ?DateTimeInterface
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        if ($domain) {
            $query->matching($query->equals('domain', $domain));
        }
        $query->setOrderings(['lastmod' => QueryInterface::ORDER_DESCENDING])->setLimit(1);
        $lastModified = $query->execute()->getFirst();

        return $lastModified ? $lastModified->getLastmod() : null;
    }
}
