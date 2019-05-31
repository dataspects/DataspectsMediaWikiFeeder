<?php

namespace MediaWiki\Extension\DataspectsMediaWikiFeeder;

class DataspectsMediaWikiFeed {

  public function __construct($title) {
    $this->title = $title;
    $this->annotations = array();
    $this->wikiPage = \WikiPage::factory($title);
    $this->getParsedWikitext();
    $this->getAnnotations();
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
        'subject' => $this->title
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
                'subject' => $this->title->prefixedText,
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
      "pagename" => $this->title->mTextform,
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

  public function sendToMongoDB() {
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
