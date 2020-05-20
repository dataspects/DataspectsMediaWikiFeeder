<?php

namespace MediaWiki\Extension\DataspectsMediaWikiFeeder;

class DataspectsMediaWikiFeederDeleteJob extends \Job {
  // https://doc.wikimedia.org/mediawiki-core/master/php/classJob.html

  public function __construct($id) {
    // https://doc.wikimedia.org/mediawiki-core/master/php/classTitle.html
    // https://www.mediawiki.org/wiki/Manual:Title.php#Functions
    parent::__construct("DataspectsMediaWikiFeederDeleteJob", []);
    $this->id = $id;
    echo "DATASPECTS: deleting ".$id."\n";
  }

  public function run() {
    // $this->deletePage();
  }

  private function deletePage() {
    $url = $GLOBALS['wgDataspectsApiURL'].$GLOBALS['wgDataspectsMediaWikiID']."/pages/".$this->id;
    echo $url;
    
    $req = \MWHttpRequest::factory(
      $url,
      [
        "method" => "delete",
        "postData" => array(
          "pageID" => $this->id
        )
      ],
      __METHOD__
    );
    $req->setHeader("Authorization", "Bearer ".$GLOBALS['wgDataspectsApiKey']);
    $req->setHeader("content-type", "application/json");
    $req->setHeader("accept", "application/json");
    $status = $req->execute();
    if($status->isOK()) {
      echo $this->id." deleted\n";
    } else {
      echo $status;
    }
  }

}
