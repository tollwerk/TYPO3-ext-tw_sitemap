<?php

namespace Tollwerk\TwSitemap\Domain\Model;

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

/**
 * Modellklasse für XML-Sitemap-Einträge
 *
 * @package tw_sitemap
 * @author Dipl.-Ing. Joschi Kuphal <joschi@tollwerk.de>
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class Entry extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * Domain
	 *
	 * @var string
	 * @validate NotEmpty
	 */
	protected $domain;
	
	/**
	 * Entry origin
	 *
	 * @var string
	 * @validate NotEmpty
	 */
	protected $origin;

	/**
	 * Entry URL
	 *
	 * @var string
	 * @validate NotEmpty
	 */
	protected $loc;

	/**
	 * Last modified
	 *
	 * @var DateTime
	 * @validate NotEmpty
	 */
	protected $lastmod;

	/**
	 * Change frequency
	 *
	 * @var integer
	 * @validate NotEmpty
	 */
	protected $changefreq;

	/**
	 * Priority
	 *
	 * @var float
	 * @validate NotEmpty
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
	 * @validate NotEmpty
	 */
	protected $position;
	
	/**
	 * Source identifier
	 *
	 * @var string
	 */
	protected $source;
	
	/**
	 * Änderungsfrequenzen
	 * 
	 * @var array
	 */
	public static $changefreqs = array(
		self::CHANGEFREQ_ALWAYS			=> 'always',
		self::CHANGEFREQ_HOURLY			=> 'hourly',
		self::CHANGEFREQ_DAILY			=> 'daily',
		self::CHANGEFREQ_WEEKLY			=> 'weekly',
		self::CHANGEFREQ_MONTHLY		=> 'monthly',
		self::CHANGEFREQ_YEARLY			=> 'yearly',
		self::CHANGEFREQ_NEVER			=> 'never',
	);
	
	/**
	 * Ständige Aktualisierung
	 * 
	 * @var int
	 */
	const CHANGEFREQ_ALWAYS = 0;
	/**
	 * Stündliche Aktualisierung
	 * 
	 * @var int
	 */
	const CHANGEFREQ_HOURLY = 1;
	/**
	 * Tägliche Aktualisierung
	 * 
	 * @var int
	 */
	const CHANGEFREQ_DAILY = 2;
	/**
	 * Wöchentliche Aktualisierung
	 * 
	 * @var int
	 */
	const CHANGEFREQ_WEEKLY = 3;
	/**
	 * Monatliche Aktualisierung
	 * 
	 * @var int
	 */
	const CHANGEFREQ_MONTHLY = 4;
	/**
	 * Jährliche Aktualisierung
	 * 
	 * @var int
	 */
	const CHANGEFREQ_YEARLY = 5;
	/**
	 * Keine Aktualisierung
	 * 
	 * @var int
	 */
	const CHANGEFREQ_NEVER = 6;

	/**
	 * Konstruktor
	 *
	 * @return void
	 */
	public function __construct() {}
	
	/**
	 * @return the $domain
	 */
	public function getDomain() {
		return $this->domain;
	}

	/**
	 * @param string $domain
	 */
	public function setDomain($domain) {
		$this->domain = $domain;
	}

	/**
	 * Returns the origin
	 *
	 * @return string $origin
	 */
	public function getOrigin() {
		return $this->origin;
	}

	/**
	 * Sets the origin
	 *
	 * @param string $origin
	 * @return void
	 */
	public function setOrigin($origin) {
		$this->origin = $origin;
	}

	/**
	 * Returns the loc
	 *
	 * @return string $loc
	 */
	public function getLoc() {
		return $this->loc;
	}
	
	/**
	 * Sets the loc
	 *
	 * @param string $loc
	 * @return void
	 */
	public function setLoc($loc) {
		$this->loc = $loc;
	}

	/**
	 * Returns the lastmod
	 *
	 * @return DateTime $lastmod
	 */
	public function getLastmod() {
		return $this->lastmod;
	}

	/**
	 * Sets the lastmod
	 *
	 * @param DateTime $lastmod
	 * @return void
	 */
	public function setLastmod($lastmod) {
		$this->lastmod = $lastmod;
	}

	/**
	 * Returns the changefreq
	 *
	 * @return integer $changefreq
	 */
	public function getChangefreq() {
		return $this->changefreq;
	}

	/**
	 * Sets the changefreq
	 *
	 * @param integer $changefreq
	 * @return void
	 */
	public function setChangefreq($changefreq) {
		$this->changefreq = $changefreq;
	}

	/**
	 * Returns the priority
	 *
	 * @return float $priority
	 */
	public function getPriority() {
		return $this->priority;
	}

	/**
	 * Sets the priority
	 *
	 * @param float $priority
	 * @return void
	 */
	public function setPriority($priority) {
		$this->priority = $priority;
	}
	
	/**
	 * Gets the language
	 * 
	 * @return string
	 */
	public function getLanguage() {
		return $this->language;
	}

	/**
	 * Sets the language
	 * 
	 * @param string $language
	 */
	public function setLanguage($language) {
		$this->language = $language;
	}

	/**
	 * Returns the position
	 *
	 * @return int $position
	 */
	public function getPosition() {
		return $this->position;
	}
	
	/**
	 * Sets the position
	 *
	 * @param int $position
	 * @return void
	 */
	public function setPosition($position) {
		$this->position = $position;
	}
	
	/**
	 * Get the source identifier
	 * 
	 * @return string
	 */
	public function getSource() {
		return $this->source;
	}

	/**
	 * Set the source identifier
	 * 
	 * @param string $source
	 */
	public function setSource($source) {
		$this->source = $source;
	}

	/**
	 * Setting several properties at once
	 * 
	 * @param array $data				Data array
	 * @return void
	 */
	public function setFromArray(array $data) {
		foreach ($data as $key => $value) {
			$setter			= 'set'.ucfirst(strtolower($key));
			if (is_callable(array($this, $setter))) {
				$this->$setter($value);
			}
		}
	}
}