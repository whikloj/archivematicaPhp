<?php

namespace whikloj\archivematicaPhp\Tests;

use VCR\VCR;
use whikloj\archivematicaPhp\Exceptions\ItemNotFoundException;
use whikloj\archivematicaPhp\Exceptions\RequestException;
use whikloj\archivematicaPhp\Utils\ArchivmaticaUtils;

/**
 * Tests of the Ingest object
 * @author Jared Whiklo
 * @since 0.0.1
 */
class IngestTests extends ArchivematicaPhpTestBase
{
    /**
     * @covers \whikloj\archivematicaPhp\IngestImpl::completed
     * @covers \whikloj\archivematicaPhp\OperationImpl::internalCompleted
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
     * @covers \whikloj\archivematicaPhp\OperationImpl::closeCompleted
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
     * @covers \whikloj\archivematicaPhp\OperationImpl::internalDelete
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
     * @covers \whikloj\archivematicaPhp\OperationImpl::internalCompleted
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
     * @covers \whikloj\archivematicaPhp\OperationImpl::closeCompleted
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
     * @covers \whikloj\archivematicaPhp\OperationImpl::internalStatus
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
     * @covers \whikloj\archivematicaPhp\OperationImpl::internalStatus
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
     * @covers \whikloj\archivematicaPhp\IngestImpl::listWaiting
     */
    public function testListWaitingNone(): void
    {
        VCR::insertCassette("ingest_list_waiting_none.yaml");
        $waiting = $this->archivematica->getIngest()->listWaiting();
        $this->assertIsArray($waiting);
        $this->assertCount(0, $waiting);
    }

    /**
     * @covers \whikloj\archivematicaPhp\IngestImpl::listWaiting
     */
    public function testListWaiting(): void
    {
        VCR::insertCassette("ingest_list_waiting_some.yaml");
        $waiting = $this->archivematica->getIngest()->listWaiting();
        $this->assertIsArray($waiting);
        $this->assertCount(1, $waiting);
        $this->assertArrayHasKey("sip_name", $waiting[0]);
        $this->assertEquals("add_metadata", $waiting[0]["sip_name"]);
    }

    # Skipping as when it operates against the docker instance it returns a 500 Server error,
    # so the actual response is unknown.
    public function skiptestAddMetadata(): void
    {
        $this->switchToLive();
        VCR::insertCassette("ingest_add_metadata_sip.yaml");
        $sip_uuid = "717106ae-8aa6-4961-8b28-d37344f7947c";
        $source_paths = [
            "4890ff24-14d8-4e02-a9b5-08c1d32dd707:/home/test_metadata.txt",
            "4890ff24-14d8-4e02-a9b5-08c1d32dd707:/home/some_other.txt",
        ];
        $reingest = $this->archivematica->getIngest()->addMetadata($sip_uuid, $source_paths);
        $this->assertIsNotArray($reingest);
    }
}
