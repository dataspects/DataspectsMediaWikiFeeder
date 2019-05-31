<?php

/**
 * To the extent possible under law,  I, Mark Hershberger, have waived all copyright and
 * related or neighboring rights to Hello World. This work is published from the
 * United States.
 *
 * @copyright CC0 http://creativecommons.org/publicdomain/zero/1.0/
 * @author Mark A. Hershberger <mah@everybody.org>
 * @ingroup Maintenance
 */

$basePath = getenv( 'MW_INSTALL_PATH' ) !== false ? getenv( 'MW_INSTALL_PATH' ) : __DIR__ . '/../../..';
require_once $basePath . '/maintenance/Maintenance.php';

class HelloWorld extends Maintenance {

	public function execute() {
    // https://www.mediawiki.org/wiki/Manual:Database_access
    // https://doc.wikimedia.org/mediawiki-core/master/php/classWikimedia_1_1Rdbms_1_1Database.html
    // https://doc.wikimedia.org/mediawiki-core/master/php/classWikimedia_1_1Rdbms_1_1Database.html#a3b03dd27f434aabfc8d2d639d1e5fa9a
    foreach($this->pageTitlesInNamespace(0) as $title) {
			$wikiPage = \WikiPage::factory($title);
		}
	}

  private function pageTitlesInNamespace($namespaceNumber) {
    $pageTitles = array();
    $dbr = wfGetDB( DB_REPLICA );
    $res = $dbr->select(
    	'page',                                   // $table The table to query FROM (or array of tables)
    	array( 'page_title' ),            // $vars (columns of the table to SELECT)
    	'page_namespace = '.$namespaceNumber,                              // $conds (The WHERE conditions)
    	__METHOD__,                                   // $fname The current __METHOD__ (for performance tracking)
    	array()        // $options = array()
    );
    foreach( $res as $row ) {
    	$pageTitles[] = Title::newFromRow($row);
    }
    return $pageTitles;
  }

}

$maintClass = HelloWorld::class;

require_once RUN_MAINTENANCE_IF_MAIN;
