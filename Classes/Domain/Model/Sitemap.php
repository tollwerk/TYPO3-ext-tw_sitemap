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
 * XML Sitemap
 *
 * @package tw_sitemap
 * @author  Dipl.-Ing. Joschi Kuphal <joschi@tollwerk.de>
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class Sitemap extends AbstractEntity
{
    /**
     * Domain
     *
     * @var string
     * @Extbase\Validate("NotEmpty")
     */
    protected $domain;

    /**
     * Alternative Ziel-Domain
     *
     * @var string
     */
    protected $targetDomain;

    /**
     * Scheme
     *
     * @var string
     * @Extbase\Validate("NotEmpty")
     */
    protected $scheme;

    /**
     * File name
     *
     * @var string
     * @Extbase\Validate("NotEmpty")
     */
    protected $filename;

    /**
     * GZIP compression
     *
     * @var boolean
     */
    protected $gz;

    /**
     * Returns the use of GZIP
     *
     * @return bool GZIP
     */
    public function getGz(): bool
    {
        return $this->gz;
    }

    /**
     * Set the use of GZIP
     *
     * @param bool $gz
     */
    public function setGz(bool $gz): void
    {
        $this->gz = $gz;
    }

    /**
     * Returns the domain
     *
     * @return string Domain
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * Returns the scheme
     *
     * @return string Scheme
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * Returns the filename
     *
     * @return string Filename
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * Sets the domain
     *
     * @param string $domain Domain
     */
    public function setDomain(string $domain): void
    {
        $this->domain = $domain;
    }

    /**
     * Sets the scheme
     *
     * @param string $scheme Scheme
     */
    public function setScheme(string $scheme): void
    {
        $this->scheme = $scheme;
    }

    /**
     * Sets the filename
     *
     * @param string $filename Filename
     */
    public function setFilename(string $filename): void
    {
        $this->filename = $filename;
    }

    /**
     * Returns the target domain
     *
     * @return string Target domain
     */
    public function getTargetDomain(): string
    {
        return $this->targetDomain;
    }

    /**
     * Sets the target domain
     *
     * @param string $targetDomain Target domain
     */
    public function setTargetDomain(string $targetDomain): void
    {
        $this->targetDomain = $targetDomain;
    }
}
