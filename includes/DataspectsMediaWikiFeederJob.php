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
        'action' => 'browsebysubject',
        'subject' => $this->getTitle()
      )
    );
    $api = new \ApiMain( $params );
    $api->execute();
    $data = $api->getResult()->getResultData();
    var_dump($data["query"]);
  }
  // Compile $data into $mongoDoc so that it fits mongodoc!
  $req = \MWHttpRequest::factory("http://", ["method" => "post", "postData" => $mongoDoc], __METHOD__);
  $req->setHeader("content-type", "application/json");
  $req->setHeader("accept", "application/json");
  //$req->setHeader("dataspects-api-key", "");

  $status = $req->execute();
  if($status->isOK()) {
    $req->getContent();
  } else {

  }



}
