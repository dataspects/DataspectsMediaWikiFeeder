<?php

namespace MediaWiki\Extension\DataspectsMediaWikiFeeder;

class DataspectsMediaWikiFeed {

  public function __construct(\Title $title) {
    $this->title = $title;
  }

  static function deleteFromDatastore($id) {
		// Run this code immediately rather than through a job.
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

  public function sendToDatastore() {
    // Check if the page exists in the datastore
    // $req = \MWHttpRequest::factory(
    //   $this->url."?rawUrl=".$this->title->getFullURL(),
    //   [
    //     "method" => "get"
    //   ],
    //   __METHOD__
    // );
    // $req->setHeader("Authorization", "Bearer ".$GLOBALS['wgDataspectsApiKey']);
    // $req->setHeader("content-type", "application/json");
    // $req->setHeader("accept", "application/json");
    // $status = $req->execute();
    // if($status->isOK()) {
    //   echo $this->title->getFullURL()." checked\n";
    //   $content = json_decode($req->getContent());
    //   if($content->pages[0]->id) {
    //     $this->updatePage($content->pages[0]->id);
    //   } else {
    //   }     
    // } else {
    //   echo $status;
    // }
    $this->annotations = array();
    $this->wikiPage = \WikiPage::factory($this->title);
    /*
    * The getMediaWikiPage's full.html expects $this->parsedWikitext.
    * However, for some namespaces we don't want $this->parsedWikitext.
    * That's why we default $this->parsedWikitext = ""; and if we want
    * $this->parsedWikitext for a specific namespace we set it by
    * $this->getParsedWikitext();.
    */
    $this->parsedWikitext = null;
    switch($this->title->mNamespace) {
      case 0:
        $this->getCategories();
        $this->getWikitext();
        $this->parsedWikitext = $this->getParsedWikitext($this->wikitext);
        $this->getMediaWikiPageAnnotations();
        $this->getIncomingAndOutgoingLinks();
        $this->url = $GLOBALS['wgDataspectsApiURL'].$GLOBALS['wgDataspectsMediaWikiID']."/pages";
        $this->mediaWikiPage = $this->getMediaWikiPage();
        break;
      case 10:
        $this->getCategories();
        $this->getWikitext();
        $this->url = $GLOBALS['wgDataspectsApiURL'].$GLOBALS['wgDataspectsMediaWikiID']."/pages";
        $this->mediaWikiPage = $this->getMediaWikiPage();
        break;
      case 106:
        $this->getCategories();
        $this->getWikitext();
        $this->url = $GLOBALS['wgDataspectsApiURL'].$GLOBALS['wgDataspectsMediaWikiID']."/pages";
        $this->mediaWikiPage = $this->getMediaWikiPage();
        break;
      case 102:
        $this->getCategories();
        $this->getPredicateAnnotations();
        $this->url = $GLOBALS['wgDataspectsApiURL'].$GLOBALS['wgDataspectsMediaWikiID']."/predicates";
        $this->mediaWikiPage = $this->predicateMongodoc();
        break;
      default:
        echo "ERROR in determining namespace ".$this->title->mNamespace."\n";
        break;
    }
    $this->addPage();
  }

  private function getCategories() {
    $this->categories = array();
    $categories = $this->wikiPage->getCategories();
    foreach($categories as $category) {
      $this->categories[] = $category->mTextform;
    }
  }

  private function getWikitext() {
    $revision = $this->wikiPage->getRevision();
    if(empty($revision)) {
      $this->wikitext = '';
    } else {
      $content = $revision->getContent( \Revision::RAW );
      $this->wikitext = \ContentHandler::getContentText( $content );
    }
  }

  private function getParsedWikitext($wikitext) {
    $parser = new \Parser();
    $parserOptions = new \ParserOptions();
    $parsedWikitext = $parser->parse($wikitext, $this->title, $parserOptions);
    if($parsedWikitext->mText) {
      return $parsedWikitext->mText;
    }
  }

  private function getMediaWikiPageAnnotations() {
    $data = $this->browseBySubject($this->title);
    foreach($data['query']['data'] as $property) {
      if(is_array($property)) {
        $propertyName = $property['property'];
        if($propertyName[0] != '_') {
          foreach($property['dataitem'] as $object) {
	          if(is_array($object)) {
              $source = str_replace('#0##', '', $object['item']);
              $this->annotations[] = array(
                'subject' => $this->title->getFullURL(),
                'predicate' => $propertyName,
                'object' => $source,
                'objectHtml' => $this->getParsedWikitext($source)
              );
            }
          }
        }
      }
    }
  }

  private function getIncomingAndOutgoingLinks() {
    foreach($this->title->getLinksFrom() as $linkFrom) {
      $this->annotations[] = array(
        'subject' => $this->title->getFullURL(),
        'predicate' => "LinksTo",
        'objectSource' => $linkFrom->getInternalURL(),
        'objectHtml' => $linkFrom->getInternalURL()
      );
    }
    foreach($this->title->getLinksTo() as $linkTo) {
      $this->annotations[] = array(
        'subject' => $this->title->getFullURL(),
        'predicate' => "IsLinkedToFrom",
        'object' => $linkTo->getInternalURL()
      );
    }
  }

  private function getPredicateAnnotations() {
    $data = $this->browseBySubject($this->title);
    foreach($data['query']['data'] as $property) {
      if(is_array($property)) {
        $propertyName = $property['property'];
        foreach($property['dataitem'] as $object) {
          if(is_array($object)) {
            $this->annotations[$propertyName] = array(
              'object' => $object['item']
            );
          }
        }
      }
    }
  }

  # LEX200122141600

  private function getMediaWikiPage() {
    $mediaWikiPage = array(
      "pageID" => $this->title->mArticleID,
      "resourceSiloLabel" => $GLOBALS['wgSitename'],
      "pagename" => $this->title->mTextform,
      // Do we want the index.php?title= form here?
      "rawUrl" => $this->title->getInternalURL(),
      "shortUrl" => $this->title->getFullURL(),
      "namespace" => $this->getNamespace($this->title->mNamespace),
      "wikitext" => trim($this->wikitext),
      "html" => trim($this->parsedWikitext),
      "categories" => $this->categories,
      "annotations" => $this->annotations
    );
    return json_encode($mediaWikiPage);
  }

  private function predicateMongodoc() {
    $predicateMongodoc = array(
      "predicate" => $this->title->mTextform,
      "predicateType" => $this->annotations['_TYPE']['object'],
      "predicateClass" => $this->annotations['HasPredicateClass']['object'],
      "predicateNamespace" => $this->getNamespace($this->title->mNamespace),
      "predicateCategories" => $this->categories
    );
    return json_encode($predicateMongodoc);
  }

  

  // private function updatePage($pageID) {
  //   $req = \MWHttpRequest::factory(
  //     $this->url."/".$pageID,
  //     [
  //       "method" => "post",
  //       "postData" => $this->mediaWikiPage
  //     ],
  //     __METHOD__
  //   );
  //   $req->setHeader("Authorization", "Bearer ".$GLOBALS['wgDataspectsApiKey']);
  //   $req->setHeader("content-type", "application/json");
  //   $req->setHeader("accept", "application/json");
  //   $status = $req->execute();
  //   if($status->isOK()) {
  //     echo $this->title->getFullURL()." updated\n";
  //   } else {
  //     echo $status;
  //   }
  // }  

  private function addPage() {
    $req = \MWHttpRequest::factory(
      $this->url,
      [
        "method" => "post",
        "postData" => $this->mediaWikiPage
      ],
      __METHOD__
    );
    $req->setHeader("Authorization", "Bearer ".$GLOBALS['wgDataspectsApiKey']);
    $req->setHeader("content-type", "application/json");
    $req->setHeader("accept", "application/json");
    $status = $req->execute();
    if($status->isOK()) {
      echo "DATASPECTS: page JSON ".$this->title->getFullURL()." created\n";
      echo "DATASPECTS: page sent to ".$this->url."\n";
    } else {
      echo "DATASPECTS: ".$status;
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

  private function browseBySubject(string $title) {
    $params = new \FauxRequest(
      array(
        'action' => 'browsebysubject',
        'subject' => $title
      )
    );
    $api = new \ApiMain( $params );
    $api->execute();
    $data = $api->getResult()->getResultData();
    return $data;
  }

}
