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
		// I tried to put this code into a Job...
		$url = $GLOBALS['wgDataspectsApiURL'].$GLOBALS['wgDataspectsMediaWikiID']."/pages/".$id;
		$req = \MWHttpRequest::factory(
		$url,
		[
			"method" => "delete"
		],
		__METHOD__
		);
		$req->setHeader("Authorization", "Bearer ".$GLOBALS['wgDataspectsApiKey']);
		$req->setHeader("content-type", "application/json");
		$req->setHeader("accept", "application/json");
		$status = $req->execute();
		if($status->isOK()) {
			
		} else {
			echo $status;
		}
	}

	public static function onTitleMoveComplete( Title &$title, Title &$newTitle, User $user, $oldid, $newid ) {
		// PENDING!
	}

}
