<?php

namespace whikloj\archivematicaPhp\Tests;

use VCR\VCR;
use whikloj\archivematicaPhp\Exceptions\RequestException;

/**
 * Tests for basic ArchivematicImpl methods.
 * @coversDefaultClass \whikloj\archivematicaPhp\ArchivematicaImpl
 */
class ArchivematicaInstanceTests extends ArchivematicaPhpTestBase
{
    /**
     * @covers ::getTransfer
     */
    public function testGetTransfer(): void
    {
        $transfer = $this->archivematica->getTransfer();
        $this->assertInstanceOf(
            '\whikloj\archivematicaPhp\Transfer',
            $transfer
        );
    }

    /**
     * @covers ::getSpace
     */
    public function testGetSpace(): void
    {
        $space = $this->archivematica->getSpace();
        $this->assertInstanceOf(
            '\whikloj\archivematicaPhp\Storage\Space',
            $space
        );
    }

    /**
     * @covers ::getLocation
     */
    public function testGetLocation(): void
    {
        $location = $this->archivematica->getLocation();
        $this->assertInstanceOf(
            '\whikloj\archivematicaPhp\Storage\Location',
            $location
        );
    }

    /**
     * @covers ::getIngest
     */
    public function testGetIngest(): void
    {
        $ingest = $this->archivematica->getIngest();
        $this->assertInstanceOf('\whikloj\archivematicaPhp\Ingest', $ingest);
    }

    /**
     * @covers ::getPackage
     */
    public function testGetPackage(): void
    {
        $package = $this->archivematica->getPackage();
        $this->assertInstanceOf('\whikloj\archivematicaPhp\Package', $package);
    }

    /**
     * @covers ::getProcessingConfig
     */
    public function testGetProcessingConfig(): void
    {
        VCR::insertCassette("test_get_existing_processing_config.yaml");
        // Test retrieval of the default Processing MCP Config file from the
        // Archivematica instance.
        $response = $this->archivematica->getProcessingConfig("default");
        $this->assertInstanceOf("\DOMDocument", $response);
        $xml = $response->saveXML();
        $this->assertStringContainsString("<processingMCP>", $xml);
        $this->assertStringContainsString("</processingMCP>", $xml);
    }

    /**
     * @covers ::getProcessingConfig
     */
    public function testGetNonExistingProcessingConfig(): void
    {
        VCR::insertCassette("test_get_non_existing_processing_config.yaml");
        // Test retrieval of a Processing MCP Config file that does not exist in the Archivematica instance.
        // Archivematica returns a 404 error and a HTML result. This test is volatile to both changes in AM's handling
        // of this request failure in future, and changes to the error handling in AMClient.py.
        $this->expectException(RequestException::class);
        $this->archivematica->getProcessingConfig("badf00d");
    }
}
