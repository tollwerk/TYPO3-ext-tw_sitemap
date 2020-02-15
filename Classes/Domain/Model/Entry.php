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

namespace Tollwerk\TwSitemap\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Annotation as Extbase;

/**
 * XML sitemap entries
 *
 * @package tw_sitemap
 * @author  Dipl.-Ing. Joschi Kuphal <joschi@tollwerk.de>
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class Entry extends AbstractEntity
{
    /**
     * Domain
     *
     * @var string
     * @Extbase\Validate("NotEmpty")
     */
    protected $domain;

    /**
     * Entry origin
     *
     * @var string
     * @Extbase\Validate("NotEmpty")
     */
    protected $origin;

    /**
     * Entry URL
     *
     * @var string
     * @Extbase\Validate("NotEmpty")
     */
    protected $loc;

    /**
     * Last modified
     *
     * @var \DateTime
     * @Extbase\Validate("NotEmpty")
     */
    protected $lastmod;

    /**
     * Change frequency
     *
     * @var int
     * @Extbase\Validate("NotEmpty")
     */
    protected $changefreq;

    /**
     * Priority
     *
     * @var float
     * @Extbase\Validate("NotEmpty")
     */
    protected $priority;

    /**
     * Language
     *
     * @var string
     */
    protected $language;

    /**
     * Position
     *
     * @var int
     * @Extbase\Validate("NotEmpty")
     */
    protected $position;

    /**
     * Source identifier
     *
     * @var string
     */
    protected $source;

    /**
     * Change frequencies
     *
     * @var array
     */
    public static $changefreqs = array(
        self::CHANGEFREQ_ALWAYS  => 'always',
        self::CHANGEFREQ_HOURLY  => 'hourly',
        self::CHANGEFREQ_DAILY   => 'daily',
        self::CHANGEFREQ_WEEKLY  => 'weekly',
        self::CHANGEFREQ_MONTHLY => 'monthly',
        self::CHANGEFREQ_YEARLY  => 'yearly',
        self::CHANGEFREQ_NEVER   => 'never',
    );

    /**
     * Constant change
     *
     * @var int
     */
    const CHANGEFREQ_ALWAYS = 0;
    /**
     * Hourly change
     *
     * @var int
     */
    const CHANGEFREQ_HOURLY = 1;
    /**
     * Daily change
     *
     * @var int
     */
    const CHANGEFREQ_DAILY = 2;
    /**
     * Weekly change
     *
     * @var int
     */
    const CHANGEFREQ_WEEKLY = 3;
    /**
     * Monthly change
     *
     * @var int
     */
    const CHANGEFREQ_MONTHLY = 4;
    /**
     * Yearly change
     *
     * @var int
     */
    const CHANGEFREQ_YEARLY = 5;
    /**
     * No change
     *
     * @var int
     */
    const CHANGEFREQ_NEVER = 6;

    /**
     * Return the domain
     *
     * @return string Domain
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * Set the domain
     *
     * @param string $domain Domain
     */
    public function setDomain(string $domain): void
    {
        $this->domain = $domain;
    }

    /**
     * Returns the origin
     *
     * @return string Origin
     */
    public function getOrigin(): string
    {
        return $this->origin;
    }

    /**
     * Sets the origin
     *
     * @param string $origin Origin
     */
    public function setOrigin(string $origin): void
    {
        $this->origin = $origin;
    }

    /**
     * Returns the loc
     *
     * @return string Loc
     */
    public function getLoc(): string
    {
        return $this->loc;
    }

    /**
     * Sets the loc
     *
     * @param string $loc Loc
     */
    public function setLoc(string $loc): void
    {
        $this->loc = $loc;
    }

    /**
     * Returns the lastmod
     *
     * @return \DateTime Last modification
     */
    public function getLastmod(): \DateTime
    {
        return $this->lastmod;
    }

    /**
     * Sets the lastmod
     *
     * @param \DateTime $lastmod Last modification
     */
    public function setLastmod(\DateTime $lastmod): void
    {
        $this->lastmod = $lastmod;
    }

    /**
     * Returns the changefreq
     *
     * @return int Change frequency
     */
    public function getChangefreq(): int
    {
        return $this->changefreq;
    }

    /**
     * Sets the changefreq
     *
     * @param int $changefreq Change frequency
     */
    public function setChangefreq(int $changefreq): void
    {
        $this->changefreq = $changefreq;
    }

    /**
     * Returns the priority
     *
     * @return float Priority
     */
    public function getPriority(): float
    {
        return $this->priority;
    }

    /**
     * Sets the priority
     *
     * @param float $priority Priority
     */
    public function setPriority(float $priority): void
    {
        $this->priority = $priority;
    }

    /**
     * Returns the language
     *
     * @return string Language
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * Sets the language
     *
     * @param string $language Language
     */
    public function setLanguage(string $language): void
    {
        $this->language = $language;
    }

    /**
     * Returns the position
     *
     * @return int Position
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * Sets the position
     *
     * @param int $position Position
     */
    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    /**
     * Returns the source identifier
     *
     * @return string Source identifier
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * Set the source identifier
     *
     * @param string $source Source identifier
     */
    public function setSource(string $source): void
    {
        $this->source = $source;
    }

    /**
     * Setting several properties at once
     *
     * @param array $data Data array
     */
    public function setFromArray(array $data): void
    {
        foreach ($data as $key => $value) {
            $setter = 'set'.ucfirst(strtolower($key));
            if (is_callable(array($this, $setter))) {
                $this->$setter($value);
            }
        }
    }
}
