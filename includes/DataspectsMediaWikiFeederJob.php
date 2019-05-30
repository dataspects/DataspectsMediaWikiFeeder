<?php

namespace MediaWiki\Extension\DataspectsMediaWikiFeeder;

class DataspectsMediaWikiFeederJob extends \Job {
  // https://doc.wikimedia.org/mediawiki-core/master/php/classJob.html

  private $annotations = [];
  // FIXME: Why can't these instance variables be declared private?
  // private $title = '';
  // private $wikiPage = '';
  // private $wikitext = '';
  // private $parsedWikitext = '';

  public function __construct(\Title $title) {
    // https://doc.wikimedia.org/mediawiki-core/master/php/classTitle.html
    // https://www.mediawiki.org/wiki/Manual:Title.php#Functions
    parent::__construct("DataspectsMediaWikiFeederJob", []);
    $this->title = $title;
  }

  public function run() {
    // https://doc.wikimedia.org/mediawiki-core/master/php/classWikiPage.html
    // https://www.mediawiki.org/wiki/Manual:WikiPage.php
    $this->wikiPage = \WikiPage::factory($this->title);
    $this->getParsedWikitext();
    $this->getAnnotations();
    $this->sendToMongoDB();
  }

  private function getParsedWikitext() {
    $revision = $this->wikiPage->getRevision();
    $content = $revision->getContent( \Revision::RAW );
    $this->wikitext = \ContentHandler::getContentText( $content );
    $parser = new \Parser();
    $parserOptions = new \ParserOptions();
    $this->parsedWikitext = $parser->parse($this->wikitext, $this->title, $parserOptions);
  }

  private function getAnnotations() {
    $params = new \FauxRequest(
      array(
        'action' => 'browsebysubject',
        'subject' => $this->getTitle()
      )
    );
    $api = new \ApiMain( $params );
    $api->execute();
    $data = $api->getResult()->getResultData();
    foreach($data['query']['data'] as $property) {
      if(is_array($property)) {
        $propertyName = $property['property'];
        if($propertyName[0] != '_') {
          foreach($property['dataitem'] as $object) {
            if(is_array($object)) {
              $this->annotations[] = array(
                'subject' => $this->getTitle()->prefixedText,
                'predicate' => $propertyName,
                'object' => array(
                  'source' => str_replace('#0##', '', $object['item']),
                  'html' => '',
                  'text' => ''
                )
              );
            }
          }
        }
      }
    }
  }

  private function mongodoc() {
    $mongoDoc = array(
      "slug" => "pending",
      "resourceSiloType" => "pending",
      "resourceSiloLabel" => "pending",
      "resourceSiloID" => "pending",
      "resourceType" => "MediaWikiPage",
      "pagename" => $this->getTitle()->mTextform,
      // Do we want the index.php?title= form here?
      "rawUrl" => $this->title->getInternalURL(),
      "shortUrl" => $this->title->getFullURL(),
      "namespace" => $this->getNamespace($this->title->getNamespace()),
      "full" => array(
        "wikitext" => $this->wikitext,
        "text" => "NOT USED BECAUSE NO TIKA HERE",
        "html" => $this->parsedWikitext->mText
      ),
      "nonFormssemanticized" => array(
        "html" => "pending",
        "text" => "pending",
      ),
      "annotations" => $this->annotations,
      "feederClass" =>"DataspectsMediaWikiFeeder"
    );
    return json_encode($mongoDoc);
  }

  private function sendToMongoDB() {
    $url = $GLOBALS['wgDataspectsApiURL'].'mediawikis/'.$GLOBALS['wgMediaWikiMongoID']."/pages";
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

  private function getNamespace($index) {
    if($index == 0) {
      $namespace = 'Mainspace';
    } else {
      $namespace = \MWNamespace::getCanonicalName($index);
    }
    return $namespace;
  }

}
