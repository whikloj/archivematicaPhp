<?php

namespace whikloj\archivematicaPhp\Tests\Storage;

use whikloj\archivematicaPhp\Tests\ArchivematicaPhpTestBase;
use VCR\VCR;

class LocationTests extends ArchivematicaPhpTestBase
{
    public function testTransferables(): void
    {
        VCR::insertCassette("transferables.yaml");
        // Test that we can get all transferable entities in the Storage Service.
        $transferables = $this->archivematica->getLocation()->browsePath(
            self::TRANSFER_SOURCE_UUID
        );
        $this->assertIsArray($transferables);
        $this->assertArrayHasKey("directories", $transferables);
        $this->assertArrayHasKey("entries", $transferables);
        $this->assertArrayHasKey("properties", $transferables);
        $this->assertEquals(
            ["ubuntu", "vagrant"],
            $transferables["directories"]
        );
    }

    public function testTransferablesPath(): void
    {
        VCR::insertCassette("transferables_path.yaml");
        // Test that we can get all transferable entities in the Storage Service under a given path.
        $transferables = $this->archivematica->getLocation()->browsePath(
            self::TRANSFER_SOURCE_UUID,
            "vagrant/archivematica-sampledata"
        );
        $this->assertIsArray($transferables);
        $this->assertArrayHasKey("directories", $transferables);
        $this->assertArrayHasKey("entries", $transferables);
        $this->assertArrayHasKey("properties", $transferables);
        $this->assertEquals(
            [
                "OPF format-corpus",
                "SampleTransfers",
                "TestTransfers",
            ],
            $transferables["directories"]
        );
    }

    public function testTransferablesBadPath(): void
    {
        VCR::insertCassette("transferables_bad_path.yaml");
        // Test that we get empty values when we request all transferable
        // entities in the Storage Service with a non-existent path.
        $transferables = $this->archivematica->getLocation()->browsePath(
            self::TRANSFER_SOURCE_UUID,
            "vagrant/archivematica-sampledataz"
        );
        $this->assertIsArray($transferables);
        $this->assertArrayHasKey("directories", $transferables);
        $this->assertArrayHasKey("entries", $transferables);
        $this->assertArrayHasKey("properties", $transferables);
        $this->assertIsArray($transferables["directories"]);
        $this->assertCount(0, $transferables["directories"]);
        $this->assertIsArray($transferables["entries"]);
        $this->assertCount(0, $transferables["entries"]);
        $this->assertIsArray($transferables["properties"]);
        $this->assertCount(0, $transferables["properties"]);
    }
}
