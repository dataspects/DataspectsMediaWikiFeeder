<?php

namespace MediaWiki\Extension\DataspectsMediaWikiFeeder;

class DataspectsMediaWikiFeederJob extends \Job {

  public function __construct(\Title $title) {
    parent::__construct("DataspectsMediaWikiFeederJob", []);
    $this->title = $title;
  }

  public function run() {
    //var_dump($this->getTitle());
    $params = new \FauxRequest(
      array(
        'action' => 'query',
        'list' => 'allpages',
        'apnamespace' => 0,
        'aplimit' => 10,
        'apprefix' => 'M'
      )
    );
    $api = new \ApiMain( $params );
    $api->execute();
    $data = $api->getResult()->getResultData();
    var_dump($data);
  }

}
