
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.3-8892BF.svg?style=flat-square)](https://php.net/)
[![Github Actions](https://github.com/whikloj/archivematicaPhp/workflows/Build/badge.svg?branch=main)](https://github.com/whikloj/archivematicaPhp/actions?query=workflow%3A%22Build%22+branch%3Amain)
[![LICENSE](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](./LICENSE)
[![codecov](https://codecov.io/gh/whikloj/archivematicaPhp/branch/main/graph/badge.svg)](https://codecov.io/gh/whikloj/archivematicaPhp)

## Description
This is a PHP client library for interacting with an [Archivematica](https://www.archivematica.org) instance. 
It requires PHP >= 7.3.

## Installation
This library can installed using [Composer](https://getcomposer.org) with the command.
```
composer require whikloj/archivematicaPhp
```

## Usage
The Archivematica client is made up a main `ArchivematicaPhp` resource and several other object types.
These are described below:

* [Transfer](#transfer-object)
* [Ingest](#ingest-object)
* [Package](#package-object)
* [Location](#location-object)
* [Space](#space-object)
* [Pipeline](#pipeline-object)

### ArchivematicaPhp

To instantiate an ArchivematicPhp instance you provide your Archivematica URL and 
your Storage Service URL. You will also need to set your username and API keys for either or both the 
Archivematica system and Storage Service.
```
$client = ArchivematicaPhp::create(
    "http://my-archivematica.example.org",
    "http://my-archivematica.example.org:62101"
)->setAMCreds(
    "archivematicaUser",
    "archivematicaApiKey"
)->setSSCreds(
    "storageServiceUser",
    "storageServiceApiKey"
);
```

#### Processing Config
You can request the current processing configuration from the server. It returns an XML document.
```
$config = $client->getProcessingConfig();
```

#### Other operations
Once you have an ArchivematicaPhp instance you can request any of the other objects but
using their associated GET methods.
ie.
* Transfers - `$client->getTransfer()`
* Ingests - `$client->getIngest()`
* Packages - `$client->getPackage()`
* Locations - `$client->getLocation()`
* Spaces - `$client->getSpace()`
* Pipelines - `$client->getPipeline()`
