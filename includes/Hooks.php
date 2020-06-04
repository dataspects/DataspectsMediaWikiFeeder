<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * @file
 */

namespace MediaWiki\Extension\DataspectsMediaWikiFeeder;

class Hooks {

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/Something
	 * @param string $arg1 First argument
	 * @param array $arg2
	 */
	public static function onPageContentSaveComplete( $wikiPage ) {
		$job = new DataspectsMediaWikiFeederSendJob($wikiPage->getTitle());
		\JobQueueGroup::singleton()->lazyPush($job);
	}

	public static function onArticleDeleteComplete( $wikiPage, $user, $reason, $id ) {
		\MediaWiki\Extension\DataspectsMediaWikiFeeder\DataspectsMediaWikiFeed::deleteFromDatastore($id);
	}

	public static function onTitleMoveComplete( $title, $newTitle, $user, $oldid, $newid ) {
		\MediaWiki\Extension\DataspectsMediaWikiFeeder\DataspectsMediaWikiFeed::deleteFromDatastore($oldid);
		$job = new DataspectsMediaWikiFeederSendJob($newTitle);
		\JobQueueGroup::singleton()->lazyPush($job);
		if($newid == 0) {
			// LEX2006041158
			// $newid = database page_id of the created redirect, or 0 if the redirect was suppressed
		}
	}

}
