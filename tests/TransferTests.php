<?php

namespace whikloj\archivematicaPhp\Tests;

use VCR\VCR;
use whikloj\archivematicaPhp\Exceptions\AuthorizationException;
use whikloj\archivematicaPhp\Exceptions\RequestException;
use whikloj\archivematicaPhp\Utils\ArchivmaticaUtils;

/**
 * Transfer tests, copied and modified from AMClient tests.
 * @author Jared Whiklo
 * @since 0.0.1
 * @see https://github.com/artefactual-labs/amclient/blob/66cf354788678831ae9905fc0d6c6df7aa0427a2/tests/test_amclient.py
 * @coversDefaultClass \whikloj\archivematicaPhp\TransferImpl
 */
class TransferTests extends ArchivematicaPhpTestBase
{
    /**
     * @covers ::completed
     */
    public function testCompletedTransfersTransfers(): void
    {
        VCR::insertCassette('completed_transfers_transfers.yaml');
        // Test getting completed transfers when there are completed transfers to get.
        $completed_transfers = $this->archivematica->getTransfer()->completed();
        $this->assertIsArray($completed_transfers);
        $this->assertCount(2, $completed_transfers);
        foreach ($completed_transfers as $x) {
            $this->assertTrue(ArchivmaticaUtils::isUuid($x));
        }
    }

    /**
     * @covers ::closeCompleted
     */
    public function testCloseCompletedTransfers(): void
    {
        VCR::insertCassette('close_completed_transfers_transfers.yaml');
        // Test closing completed transfers when there are completed transfers to close.
        $response = $this->archivematica->getTransfer()->closeCompleted();
        $close_succeeded = $response["close_succeeded"];
        $completed_transfers = $response["completed"];
        // Sort arrays to allow comparison.
        sort($close_succeeded);
        sort($completed_transfers);
        $this->assertEquals($close_succeeded, $completed_transfers);
        $this->assertIsArray($close_succeeded);
        $this->assertCount(2, $close_succeeded);
        foreach ($close_succeeded as $item) {
            $this->assertTrue(ArchivmaticaUtils::isUuid($item));
        }
    }

    /**
     * @covers ::completed
     */
    public function testCompletedTransfersNoTransfers(): void
    {
        VCR::insertCassette('completed_transfers_no_transfers.yaml');
        // Test getting completed transfers when there are no completed transfers to get.
        $completed_transfers = $this->archivematica->getTransfer()->completed();
        $this->assertIsArray($completed_transfers);
        $this->assertCount(0, $completed_transfers);
    }

    /**
     * @covers ::closeCompleted
     */
    public function testCloseCompletedTransfersNoTransfers(): void
    {
        VCR::insertCassette("close_completed_transfers_no_transfers.yaml");
        // Test closing completed transfers when there are no completed transfers to close.
        $response = $this->archivematica->getTransfer()->closeCompleted();
        $close_succeeded = $response["close_succeeded"];
        $completed_transfers = $response["completed"];
        sort($close_succeeded);
        sort($completed_transfers);
        $this->assertEquals($close_succeeded, $completed_transfers);
        $this->assertIsArray($close_succeeded);
        $this->assertCount(0, $close_succeeded);
    }

    /**
     * @covers ::completed
     */
    public function testCompletedTransfersBadKey(): void
    {
        VCR::insertCassette("completed_transfers_bad_key.yaml");
        // Test getting completed transfers when a bad AM API key is provided.
        $this->archivematica->setAMCreds("test", "bad api key");
        $this->expectException(AuthorizationException::class);
        $this->archivematica->getTransfer()->completed();
    }

    /**
     * @covers ::unapproved
     */
    public function testUnapprovedTransfersTransfers(): void
    {
        VCR::insertCassette("unapproved_transfers_transfers.yaml");
        // Test getting unapproved transfers when there are unapproved transfers to get.
        $unapproved_transfers = $this->archivematica->getTransfer()->unapproved();
        $this->assertIsArray($unapproved_transfers);
        $this->assertCount(1, $unapproved_transfers);
        foreach ($unapproved_transfers as $transfer) {
            $this->assertArrayHasKey('type', $transfer);
            $this->assertArrayHasKey('uuid', $transfer);
            $this->assertArrayHasKey('directory', $transfer);
            $this->assertTrue(ArchivmaticaUtils::isUuid($transfer['uuid']));
        }
    }

    /**
     * @covers ::unapproved
     */
    public function testUnapprovedTransfersNoTransfers(): void
    {
        VCR::insertCassette("unapproved_transfers_no_transfers.yaml");
        // Test getting unapproved transfers when there are no unapproved transfers to get.
        $unapproved_transfers = $this->archivematica->getTransfer()->unapproved();
        $this->assertIsArray($unapproved_transfers);
        $this->assertCount(0, $unapproved_transfers);
    }

    /**
     * @covers ::status
     */
    public function testGetTransferStatus(): void
    {
        VCR::insertCassette("transfer_status.yaml");
        // Test the successful return of the status of a transfer for a valid transfer UUID.
        $response = $this->archivematica->getTransfer()->status(
            "63fcc1b0-f83d-47e6-ac9d-a8f8d1fc2ab9"
        );
        $status = $response["status"];
        $message = $response["message"];
        $this->assertEquals("COMPLETE", $status);
        $this->assertEquals(
            "Fetched status for 63fcc1b0-f83d-47e6-ac9d-a8f8d1fc2ab9 successfully.",
            $message
        );
    }

    /**
     * @covers ::status
     */
    public function testGetTransferStatusInvalidUuid(): void
    {
        VCR::insertCassette("transfer_status_invalid_uuid.yaml");
        // Test the successful return of the status for a non-existant transfer in Archivematica.
        $response = $this->archivematica->getTransfer()->status(
            "7bffc8f7-baad-f00d-8120-b1c51c2ab5db"
        );
        $message = $response["message"];
        $message_type = $response["type"];
        $this->assertEquals(
            "Cannot fetch unitTransfer with UUID 7bffc8f7-baad-f00d-8120-b1c51c2ab5db",
            $message
        );
        $this->assertEquals("transfer", $message_type);
    }

    /**
     * @covers ::delete
     */
    public function testHideUnits()
    {
        VCR::insertCassette("test_hide_units.yaml");
        // Test the hiding of a unit type (transfer or ingest) via the Archivematica API.
        // Split up from original AMClient tests into 2 separate tests.

        $this->archivematica->getTransfer()->delete(
            "fdf1f7d4-7b0e-46d7-a1cc-e1851f8b92ed"
        );

        $this->expectException(RequestException::class);
        $this->archivematica->getTransfer()->delete(
            "777a9d9e-baad-f00d-8c7e-00b75773672d"
        );
    }

    /**
     * @covers ::approve
     */
    public function testApproveInvalidType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->archivematica->getTransfer()->approve("some_directory", "bad_type");
    }

    /**
     * @covers ::approve
     */
    public function testApproveTransfer(): void
    {
        VCR::insertCassette("approve_existing_transfer.yaml");
        // Test the approval of a transfer waiting in the Archivematica pipeline.
        $uuid = $this->archivematica->getTransfer()->approve(
            "approve_1",
            "standard"
        );
        $this->assertTrue(ArchivmaticaUtils::isUuid($uuid));
    }

    /**
     * @covers ::approve
     */
    public function testApproveNonExistingTransfer(): void
    {
        VCR::insertCassette("approve_non_existing_transfer.yaml");
        $this->expectException(RequestException::class);
        $this->expectErrorMessageMatches("/^Request failed, 500: Server error:/");
        $this->archivematica->getTransfer()->approve("approve_2", "standard");
    }
}
