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
 * This class implements the header alteration and entity alteration functions
 * used for any style of Time Negotiation when an Accept-Datetime header is NOT
 * given in the request.
 *
 * This class is for the direclty accessed URI-R mentioned in the Memento RFC.
 */
class OriginalResourceDirectlyAccessed extends MementoResource {

	/**
	 * alterHeaders
	 *
	 * Alter the headers for 200-style Time Negotiation when an Accept-Datetime
	 * header is NOT given in the request.
	 */
	public function alterHeaders() {

		$out = $this->article->getContext()->getOutput();
		$request = $out->getRequest();
		$response = $request->response();
		$titleObj = $this->article->getTitle();

		$requestURL = $request->getFullRequestURL();
		$title = $this->getFullNamespacePageTitle( $titleObj );

		$linkEntries = array();

		// if we exclude this Namespace, don't show folks the Memento relations
		if ( in_array( $titleObj->getNamespace(),
			$this->conf->get('ExcludeNamespaces') ) ) {

			$entry = '<http://mementoweb.org/terms/donotnegotiate>; rel="type"';
			array_push( $linkEntries, $entry );
		} else {

			$uri = $titleObj->getFullURL();

			$tguri = $this->getTimeGateURI( $title );

			if ( $uri == $tguri ) {
				$entry = $this->constructLinkRelationHeader( $tguri,
					'original latest-version timegate' );
				array_push( $linkEntries, $entry );
			} else {
				$entry = $this->constructLinkRelationHeader( $uri,
					'original latest-version' );
				array_push( $linkEntries, $entry );

				$entry = $this->constructLinkRelationHeader( $tguri,
					'timegate' );
				array_push( $linkEntries, $entry );
			}

			if ( $this->conf->get('RecommendedRelations') ) {
				$pageID = $titleObj->getArticleID();

				// for performance, these database calls only occur
				// when $wgMementoRecommendedRelations is true
				//$first = $this->getFirstMemento( $pageID );
				$first = $this->getFirstMemento($titleObj);
				$last = $this->getLastMemento( $titleObj );

				$entries = $this->generateRecommendedLinkHeaderRelations(
					$titleObj, $first, $last );

				$linkEntries = array_merge( $linkEntries, $entries);

			} else {
				$entry = $this->constructTimeMapLinkHeader( $title );
				array_push( $linkEntries, $entry );
			}

			$linkEntries = implode( ',', $linkEntries );
		}

		$response->header( 'Link: ' . $linkEntries, true );
	}

	/**
	 * alterEntity
	 *
	 * No entity alterations are necessary for directly accessed Original pages.
	 *
	 */
	public function alterEntity() {
		// do nothing to the body
	}
}
