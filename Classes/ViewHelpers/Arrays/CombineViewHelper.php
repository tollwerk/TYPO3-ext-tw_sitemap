<?php

namespace Tollwerk\TwSitemap\ViewHelpers\Arrays;

/***************************************************************
 *  Copyright notice
 *
 *  Copyright © 2017 Dipl.-Ing. Joschi Kuphal (joschi@tollwerk.de)
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
 * View-Helper zur dynamischen Array-Erzeugung
 *
 * = Examples =
 *
 * <code title="Basic usage">
 * <tw:arrays.combine keys="{0: 'key1', 1: 'key2'}" values="{0: 1, 1: 2}" />
 * </code>
 * <output>
 * Array mit den übergebenen Schlüssel und zugehörigen Werten
 * </output>
 *
 * <code title="Inline notation">
 * {tw:arrays.combine(keys: {0: 'key1', 1: 'key2'}, values: {0: 1, 1: 2})}
 * </code>
 *
 * @package tw_sitemap
 * @author Dipl.-Ing. Joschi Kuphal <joschi@tollwerk.de>
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class CombineViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * Kombiniert einen dynamischen Array aus Schlüsseln und Werten
     *
     * @param array $keys Schlüssel
     * @param array $index Werte
     * @return array                    Kombinierter Array
     */
    public function render(array $keys, array $values)
    {
        $count = min(count($keys), count($values));
        return array_combine(array_slice($keys, 0, $count), array_slice($values, 0, $count));
    }
}
