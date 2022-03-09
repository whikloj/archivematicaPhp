<?php

namespace whikloj\archivematicaPhp\Tests;

use VCR\VCR;
use whikloj\archivematicaPhp\Exceptions\ItemNotFoundException;
use whikloj\archivematicaPhp\Exceptions\RequestException;
use whikloj\archivematicaPhp\Utils\ArchivmaticaUtils;

class IngestTests extends ArchivematicaPhpTestBase
{
    /**
     * @covers \whikloj\archivematicaPhp\IngestImpl::completed
     */
    public function testCompletedIngestsIngests()
    {
        VCR::insertCassette("completed_ingests_ingests.yaml");
        // Test getting completed ingests when there are completed ingests to get.
        $results = $this->archivematica->getIngest()->completed();
        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        foreach ($results as $item) {
            $this->assertTrue(ArchivmaticaUtils::isUuid($item));
        }
    }

    /**
     * @covers \whikloj\archivematicaPhp\IngestImpl::closeCompleted
     */
    public function testCloseCompletedIngestsIngests()
    {
        VCR::insertCassette("close_completed_ingests_ingests.yaml");
        // Test closing completed ingests when there are completed ingests to close.
        $response = $this->archivematica->getIngest()->closeCompleted();
        $close_succeeded = $response["close_succeeded"];
        $completed_ingests = $response["completed"];
        // Sort arrays to allow comparison.
        sort($close_succeeded);
        sort($completed_ingests);
        $this->assertEquals($close_succeeded, $completed_ingests);
        $this->assertIsArray($close_succeeded);
        $this->assertCount(2, $close_succeeded);
        foreach ($close_succeeded as $item) {
            $this->assertTrue(ArchivmaticaUtils::isUuid($item));
        }
    }

    /**
     * @covers \whikloj\archivematicaPhp\IngestImpl::delete
     */
    public function testHideUnits()
    {
        VCR::insertCassette("test_hide_units.yaml");
        // Test the hiding of a unit type (transfer or ingest) via the Archivematica API.
        // Split up from original AMClient tests into 2 separate tests.
        // This on exists.
        $this->archivematica->getIngest()->delete(
            "b72afa68-9e82-410d-9235-02fa10512e14"
        );
        // This one doesn't exist.
        $this->expectException(ItemNotFoundException::class);
        $this->archivematica->getIngest()->delete(
            "777a9d9e-baad-f00d-8c7e-00b75773672d"
        );
    }

    /**
     * @covers \whikloj\archivematicaPhp\IngestImpl::completed
     */
    public function testCompletedIngestsNoIngests()
    {
        VCR::insertCassette("completed_ingests_no_ingests.yaml");
        // Test getting completed ingests when there are no completed ingests to get.
        $results = $this->archivematica->getIngest()->completed();
        $this->assertIsArray($results);
        $this->assertCount(0, $results);
    }

    /**
     * @covers \whikloj\archivematicaPhp\IngestImpl::closeCompleted
     */
    public function testCloseCompletedIngestsNoIngests()
    {
        VCR::insertCassette("close_completed_ingests_no_ingests.yaml");
        // Test closing completed ingests when there are no completed ingests to close.
        $response = $this->archivematica->getIngest()->closeCompleted();
        $close_succeeded = $response["close_succeeded"];
        $completed_ingests = $response["completed"];
        $this->assertIsArray($close_succeeded);
        $this->assertIsArray($completed_ingests);
        sort($close_succeeded);
        sort($completed_ingests);
        $this->assertEquals($close_succeeded, $completed_ingests);
        $this->assertCount(0, $close_succeeded);
    }

    /**
     * @covers \whikloj\archivematicaPhp\IngestImpl::status
     */
    public function testGetIngestStatus()
    {
        VCR::insertCassette("ingest_status.yaml");
        // Test the successful return of the status of an ingest for a valid SIP UUID.
        $response = $this->archivematica->getIngest()->status(
            "23129471-09e3-467e-88b6-eb4714afb5ac"
        );
        $this->assertIsArray($response);
        $message = $response["message"];
        $message_type = $response["type"];
        $this->assertEquals(
            "Fetched status for 23129471-09e3-467e-88b6-eb4714afb5ac successfully.",
            $message
        );
        $this->assertEquals("SIP", $message_type);
    }

    /**
     * @covers \whikloj\archivematicaPhp\IngestImpl::status
     */
    public function testGetIngestStatusInvalidUuid()
    {
        VCR::insertCassette("ingest_status_invalid_uuid.yaml");
        // Test the response from the server for a request to find the status
        // of an ingest uuid that doesn't exist.
        $this->expectException(RequestException::class);
        $this->archivematica->getIngest()->status(
            "63fcc1b0-f83d-47e6-ac9d-a8f8d1fc2ab9"
        );
    }

    /**
     * @covers \whikloj\archivematicaPhp\PackageImpl::reingest
     */
    public function testReingestAip(): void
    {
        VCR::insertCassette("reingest_existing_aip.yaml");
        // Test amclient's ability to initiate the reingest of an AIP.
        $pipeline_uuid = "65aaac5d-b4fd-478e-967b-6cdfee02f2c5";
        $aip_uuid = "df8e0c68-3bda-4d1d-8493-789f7dec47f5";
        $reingest_uuid = $this->archivematica->getPackage()->reingest(
            $aip_uuid,
            $pipeline_uuid
        );
        $this->assertEquals($reingest_uuid, $aip_uuid);
    }

    /**
     * @covers \whikloj\archivematicaPhp\PackageImpl::reingest
     */
    public function testReingestMetadata(): void
    {
        $aip_uuid = "b36758e8-fe77-4af6-8b1e-b3dd074c25d0";
        $pipeline_uuid = "8490b352-6ad0-4590-a3f1-6dc5f8abd603";
        VCR::insertCassette("reingest_existing_aip_metadata_only.yaml");
        $reingest_uuid = $this->archivematica->getPackage()->reingest(
            $aip_uuid,
            $pipeline_uuid,
            "METADATA_ONLY"
        );
        $this->assertEquals($aip_uuid, $reingest_uuid);
    }

    /**
     * @covers \whikloj\archivematicaPhp\PackageImpl::reingest
     */
    public function testReingestInvalidType(): void
    {
        $pipeline_uuid = "65aaac5d-b4fd-478e-967b-6cdfee02f2c5";
        $aip_uuid = "df8e0c68-3bda-4d1d-8493-789f7dec47f5";
        $this->expectException(\InvalidArgumentException::class);
        $this->expectErrorMessage("Reingest type was MAJOR, must be one of FULL, OBJECTS or METADATA_ONLY");
        $this->archivematica->getPackage()->reingest(
            $aip_uuid,
            $pipeline_uuid,
            "MAJOR"
        );
    }

    /**
     * @covers \whikloj\archivematicaPhp\PackageImpl::reingest
     */
    public function testReingestNonAip(): void
    {
        VCR::insertCassette("reingest_non_existing_aip.yaml");
        // Test amclient's response to the initiation of a reingest for an AIP
        // that does not exist.
        $pipeline_uuid = "bb033eff-131e-48d5-980f-c4edab0cb038";
        $aip_uuid = "bb033eff-131e-48d5-980f-c4edab0cb038";
        $this->expectException(ItemNotFoundException::class);
        $this->expectExceptionCode(404);
        $this->archivematica->getPackage()->reingest(
            $aip_uuid,
            $pipeline_uuid
        );
    }

    /**
     * @covers \whikloj\archivematicaPhp\PackageImpl::reingest
     */
    public function testReingestDip(): void
    {
        VCR::insertCassette("reingest_existing_dip.yaml");
        $aip_uuid = "7d7a4a47-19b5-46b3-aafe-a4a6c79d65ba";
        $pipeline_uuid = "8490b352-6ad0-4590-a3f1-6dc5f8abd603";
        $this->expectException(RequestException::class);
        $this->expectExceptionCode(405);
        $this->archivematica->getPackage()->reingest(
            $aip_uuid,
            $pipeline_uuid
        );
    }
}
