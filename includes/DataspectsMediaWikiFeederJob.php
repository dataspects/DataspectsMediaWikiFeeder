<?php

namespace MediaWiki\Extension\DataspectsMediaWikiFeeder;

class DataspectsMediaWikiFeederJob extends \Job {

  public function __construct(\Title $title) {
    parent::__construct("DataspectsMediaWikiFeederJob", []);
    $this->title = $title;
  }

  public function run() {
    $params = new \FauxRequest(
      array(
        'action' => 'browsebysubject',
        'subject' => $this->getTitle()
      )
    );
    $api = new \ApiMain( $params );
    $api->execute();
    $data = $api->getResult()->getResultData();
    // print_r(var_dump($this->getTitle()));
    // print_r($data["query"]);
    // var_dump($this->mongodoc());
    $this->getAnnotations($data);
    // $this->sendToMongoDB();
  }

  public function getAnnotations($data) {
    foreach($data["query"]["data"] as $property) {
      $propertyName = $property["property"];
      if($propertyName[0] != "_") {
        echo($propertyName);
      }
    }
  }

  public function mongodoc() {
    // $this->getTitle()->mTextform->prefixedText is "Template:MyName"
    $mongoDoc = array(
      "slug" => "TEST",
      "resourceSiloType" => "TEST2",
      "resourceSiloLabel" => "TEST",
      "resourceSiloID" => "TEST",
      "resourceType" => "TEST",
      "pagename" => $this->getTitle()->mTextform,
      "rawUrl" => "TEST",
      "shortUrl" => "TEST",
      "namespace" => $this->getTitle()->mNamespace,
      "full" => array(
        "wikitext" => "TEST",
        "text" => "TEST",
        "html" => "TEST"
      ),
      "nonFormssemanticized" => array(
        "html" => "TEST",
        "text" => "TEST",
      ),
      "annotations" => array(),
      "feederClass" =>"dummy"
    );
    return json_encode($mongoDoc);
  }

  public function sendToMongoDB() {
    $url = $GLOBALS['wgDataspectsApiURL'].$GLOBALS['wgMediaWikiMongoID']."/pages";
    // Compile $data into $mongoDoc so that it fits mongodoc!
    $req = \MWHttpRequest::factory(
      $url,
      [
        "method" => "post",
        "postData" => $this->mongodoc()
      ],
      __METHOD__
    );
    $req->setHeader("content-type", "application/json");
    $req->setHeader("accept", "application/json");
    $req->setHeader("dataspects-api-key", $GLOBALS['wgDataspectsApiKey']);

    $status = $req->execute();

    if($status->isOK()) {
      $req->getContent();
    } else {
      echo $status;
    }
  }

}
