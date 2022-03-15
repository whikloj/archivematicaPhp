<?php

namespace whikloj\archivematicaPhp\Tests\Storage;

use VCR\VCR;
use whikloj\archivematicaPhp\Exceptions\ItemNotFoundException;
use whikloj\archivematicaPhp\Exceptions\RequestException;
use whikloj\archivematicaPhp\Tests\ArchivematicaPhpTestBase;
use whikloj\archivematicaPhp\Utils\ArchivmaticaUtils;

class LocationTests extends ArchivematicaPhpTestBase
{
    private const DETAIL_KEYS = [
        "description",
        "enabled",
        "path",
        "pipeline",
        "space",
        "purpose",
        "quota",
        "relative_path",
        "resource_uri",
        "used",
        "uuid",
    ];

    /**
     * @covers \whikloj\archivematicaPhp\Storage\LocationImpl::browsePath
     */
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

    /**
     * @covers \whikloj\archivematicaPhp\Storage\LocationImpl::browsePath
     */
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

    /**
     * @covers \whikloj\archivematicaPhp\Storage\LocationImpl::browsePath
     */
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

    /**
     * @covers \whikloj\archivematicaPhp\Storage\LocationImpl::getAll
     */
    public function testGetAllLocations(): void
    {
        VCR::insertCassette("location_get_all.yaml");
        $response = $this->archivematica->getLocation()->getAll();
        $this->assertIsArray($response);
        $this->assertArrayHasKey("meta", $response);
        $this->assertArrayHasKey("objects", $response);
        $this->assertEquals(7, $response["meta"]["total_count"]);
        $this->assertCount(7, $response["objects"]);
    }

    /**
     * @covers \whikloj\archivematicaPhp\Storage\LocationImpl::create
     */
    public function testCreateLocationFailurePipeline(): void
    {
        VCR::insertCassette("location_create_bad_pipeline.yaml");
        $this->expectException(RequestException::class);
        $this->expectExceptionCode(400);
        $this->archivematica->getLocation()->create(
            "Some test location",
            "some-fake-pipeline",
            "88498bd8-dd31-40b7-80dc-574a5a01bdf2",
            false,
            "SD",
            "test_fake_location"
        );
    }

    /**
     * @covers \whikloj\archivematicaPhp\Storage\LocationImpl::create
     */
    public function testCreateLocationFailureSpace(): void
    {
        VCR::insertCassette("location_create_bad_space.yaml");
        $this->expectException(RequestException::class);
        $this->expectExceptionCode(400);
        $this->archivematica->getLocation()->create(
            "Some test location",
            "8490b352-6ad0-4590-a3f1-6dc5f8abd603",
            "some-fake-pipeline",
            false,
            "SD",
            "test_fake_location"
        );
    }

    /**
     * @covers \whikloj\archivematicaPhp\Storage\LocationImpl::create
     */
    public function testCreateLocationSucceed(): void
    {
        VCR::insertCassette("location_create_success.yaml");
        $response = $this->archivematica->getLocation()->create(
            "Some test location",
            "8490b352-6ad0-4590-a3f1-6dc5f8abd603",
            "88498bd8-dd31-40b7-80dc-574a5a01bdf2",
            false,
            "SD",
            "test_fake_location"
        );
        $this->assertIsArray($response);
        foreach (self::DETAIL_KEYS as $key) {
            $this->assertArrayHasKey($key, $response);
        }
        $uuid = $response["uuid"];
        $this->assertEquals(ArchivmaticaUtils::asUri($uuid, "location"), $response["resource_uri"]);
    }

    /**
     * @covers \whikloj\archivematicaPhp\Storage\LocationImpl::getDetails
     */
    public function testLocationGetDetailsSucceed(): void
    {
        VCR::insertCassette("location_get_details_succeed.yaml");
        $location_uuid = "ecf0a4e3-2fbc-4e38-9de6-aa1f400ef9ff";
        $response = $this->archivematica->getLocation()->getDetails($location_uuid);
        $this->assertIsArray($response);
        foreach (self::DETAIL_KEYS as $key) {
            $this->assertArrayHasKey($key, $response);
        }
        $this->assertEquals(ArchivmaticaUtils::asUri($location_uuid, "location"), $response["resource_uri"]);
    }

    /**
     * @covers \whikloj\archivematicaPhp\Storage\LocationImpl::getDetails
     */
    public function testLocationGetDetailsFail(): void
    {
        VCR::insertCassette("location_get_details_fail.yaml");
        $location_uuid = "non-existant-location-uuid";
        $this->expectException(ItemNotFoundException::class);
        $this->archivematica->getLocation()->getDetails($location_uuid);
    }
}
