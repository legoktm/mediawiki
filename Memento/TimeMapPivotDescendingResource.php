<?php
/**
 * This file is part of the Memento Extension to MediaWiki
 * http://www.mediawiki.org/wiki/Extension:Memento
 *
 * @section LICENSE
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 * 
 * @file
 */

/**
 * Ensure that this file is only executed in the right context.
 *
 * @see http://www.mediawiki.org/wiki/Security_for_developers
 */
if ( ! defined( 'MEDIAWIKI' ) ) {
	echo "Not a valid entry point";
	exit( 1 );
}

/**
 * This class handles TimeMaps from URIs of the form:
 * http://example.com/index.php/Special:TimeMap/20130720011113/-1/Page
 * referred to here as a "descending pivot" where the pivot is the date
 * of 20130720011113 and the -1 in the URI indicates that we want a
 * Time Map of mementos from before that pivot date.
 *
 */
class TimeMapPivotDescendingResource extends TimeMapResource {

	/**
	 * getPivotTimeMapData
	 *
	 * Concrete implementation of a method that acquires decreasing
	 * TimeMap data, based on a given formatted timestamp.
	 *
	 * @param $page_id
	 * @param $formattedTimestamp
	 *
 	 * @return $data - array with keys 'rev_id' and 'rev_timestamp' containing
	 *		the revision ID and the revision timestamp respectively
	 */
	public function getPivotTimeMapData( $page_id, $formattedTimestamp ) {
		return $this->getDescendingTimeMapData( $page_id, $formattedTimestamp );
	}

	/**
	 * Render the page
	 * 
	 */
	public function alterEntity() {
		$this->renderPivotTimeMap();
	}

	/**
	 * alterHeaders
	 *
	 * No headers to alter for Time Maps.
	 */
	public function alterHeaders() {
		// do nothing to the headers
	}

}
