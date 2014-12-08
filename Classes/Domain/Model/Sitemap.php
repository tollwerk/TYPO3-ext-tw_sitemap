<?php

namespace Tollwerk\TwSitemap\Domain\Model;

/***************************************************************
 *  Copyright notice
 *
 *  © 2012 Dipl.-Ing. Joschi Kuphal <joschi@tollwerk.de>, tollwerk® GmbH
 *  
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Modellklasse für XML-Sitemaps
 *
 * @package tw_sitemap
 * @author Dipl.-Ing. Joschi Kuphal <joschi@tollwerk.de>
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class Sitemap extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * Domain
	 *
	 * @var string
	 * @validate NotEmpty
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
	 * @validate NotEmpty
	 */
	protected $scheme;

	/**
	 * File name
	 *
	 * @var string
	 * @validate NotEmpty
	 */
	protected $filename;
	
	/**
	 * GZIP-Kompression
	 * 
	 * @var boolean
	 */
	protected $gz;
	
	/**
	 * Konstruktor
	 *
	 * @return void
	 */
	public function __construct() {}

	/**
	 * @return the $gz
	 */
	public function getGz() {
		return $this->gz;
	}

	/**
	 * @param boolean $gz
	 */
	public function setGz($gz) {
		$this->gz = $gz;
	}

	/**
	 * @return the $domain
	 */
	public function getDomain() {
		return $this->domain;
	}

	/**
	 * @return the $scheme
	 */
	public function getScheme() {
		return $this->scheme;
	}

	/**
	 * @return the $filename
	 */
	public function getFilename() {
		return $this->filename;
	}

	/**
	 * @param string $domain
	 */
	public function setDomain($domain) {
		$this->domain = $domain;
	}

	/**
	 * @param string $scheme
	 */
	public function setScheme($scheme) {
		$this->scheme = $scheme;
	}

	/**
	 * @param string $filename
	 */
	public function setFilename($filename) {
		$this->filename = $filename;
	}
	/**
	 * @return the $targetDomain
	 */
	public function getTargetDomain() {
		return $this->targetDomain;
	}

	/**
	 * @param string $targetDomain
	 */
	public function setTargetDomain($targetDomain) {
		$this->targetDomain = $targetDomain;
	}
}

?>