# DataspectsMediaWikiFeeder

    [[ReactsToChangesIn::MediaWiki Core]]
    [[ReactsToChangesIn::MediaWiki API]]

This MediaWiki extension feeds MediaWiki page data into [dataspects system](https://dataspects.com/).

## Installation

`w/extensions$ git clone https://github.com/dataspects/DataspectsMediaWikiFeeder.git`

```
wfLoadExtension( 'DataspectsMediaWikiFeeder' );
$wgDataspectsMediaWikiID = "";
$wgDS0ResourceSiloURI = "https://wiki.dataspects.com/wiki/";
$wgDS0IndexingJob = "";
$wgDataspectsApiKey = "";
$wgDataspectsApiURL = "";
```
