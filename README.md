# DataspectsMediaWikiFeeder

    [[ReactsToChangesIn::MediaWiki Core]]
    [[ReactsToChangesIn::MediaWiki API]]

This MediaWiki extension feeds MediaWiki page data to [dataspects](https://dataspects.com/).

## Installation

`w/extensions$ git clone https://github.com/dataspects/DataspectsMediaWikiFeeder.git`

```
wfLoadExtension( 'DataspectsMediaWikiFeeder' );
$wgRateLimits['edit']['ip'] = array( 1000, 1 );
$wgDataspectsMediaWikiID = "";
$wgDataspectsApiKey = "";
$wgDataspectsApiURL = "";
```
