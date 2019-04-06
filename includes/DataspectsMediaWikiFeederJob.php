<?php

namespace MediaWiki\Extension\DataspectsMediaWikiFeeder;

class DataspectsMediaWikiFeederJob extends \Job {

  public function __construct(\Title $title) {
    parent::__construct("DataspectsMediaWikiFeederJob", []);
    $this->title = $title;
  }

  public function run() {
    // When the queue picks up this job, then this is the code that will be
    // executed!
    var_dump("lex");
    var_dump($this->getTitle());
  }

}
