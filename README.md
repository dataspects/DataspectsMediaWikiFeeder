# DataspectsMediaWikiFeeder

This MediaWiki extension feeds MediaWiki page data into a MongoDB. It is part of https://cookbook.findandlearn.net/wiki/DataspectsSearch.

## Installation

`w/extensions$ git clone https://github.com/dataspects/DataspectsMediaWikiFeeder.git`

```
wfLoadExtension( 'DataspectsMediaWikiFeeder' );
$wgRateLimits['edit']['ip'] = array( 1000, 1 );
$wgMediaWikiMongoID = "";
$wgDataspectsApiKey = "";
$wgDataspectsApiURL = "";
```
