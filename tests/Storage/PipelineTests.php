<?php

namespace whikloj\archivematicaPhp\Storage\Tests;

use VCR\VCR;
use whikloj\archivematicaPhp\Exceptions\ItemNotFoundException;
use whikloj\archivematicaPhp\Tests\ArchivematicaPhpTestBase;
use whikloj\archivematicaPhp\Utils\ArchivmaticaUtils;

class PipelineTests extends ArchivematicaPhpTestBase
{
    /**
     * @covers \whikloj\archivematicaPhp\Storage\PipelineImpl::getAll
     * @covers \whikloj\archivematicaPhp\Storage\PipelineImpl::get
     */
    public function testGetPipelines(): void
    {
        VCR::insertCassette("pipeline.yaml");
        // Test getting the pipelines available to the storage service where
        // there is at least one pipeline available to the service.
        $response = $this->archivematica->getPipeline()->getAll();
        $this->assertIsArray($response);
        $this->assertArrayHasKey('objects', $response);
        $objects = $response["objects"];
        $pipelines = $objects[0]["uuid"];
        $resource_uri = $objects[0]["resource_uri"];
        $this->assertTrue(ArchivmaticaUtils::isUuid($pipelines));
        $this->assertEquals("/api/v2/pipeline/f914af05-c7d2-4611-b2eb-61cd3426d9d2/", $resource_uri);
        $this->assertIsArray($objects);
        $this->assertGreaterThan(0, count($objects));
    }

    /**
     * @covers \whikloj\archivematicaPhp\Storage\PipelineImpl::getAll
     * @covers \whikloj\archivematicaPhp\Storage\PipelineImpl::get
     */
    public function testGetPipelinesNone(): void
    {
        VCR::insertCassette("pipeline_none.yaml");
        // Test getting the pipelines available to the storage service where
        // there is at least one pipeline available to the service.
        $response = $this->archivematica->getPipeline()->getAll();
        $objects = $response["objects"];
        $this->assertIsArray($objects);
        $this->assertCount(0, $objects);
    }

    /**
     * @covers \whikloj\archivematicaPhp\Storage\PipelineImpl::getByDescription
     * @covers \whikloj\archivematicaPhp\Storage\PipelineImpl::get
     */
    public function testGetPipelineByDescription(): void
    {
        VCR::insertCassette("pipeline_get_description.yaml");
        // Try starts with
        $response = $this->archivematica->getPipeline()->getByDescription("Test", false);
        $objects = $response["objects"];
        $this->assertIsArray($objects);
        $this->assertCount(1, $objects);
    }

    /**
     * @covers \whikloj\archivematicaPhp\Storage\PipelineImpl::getByDescription
     * @covers \whikloj\archivematicaPhp\Storage\PipelineImpl::get
     */
    public function testGetPipelineByDescriptionNone(): void
    {
        VCR::insertCassette("pipeline_get_description_none.yaml");
        $response = $this->archivematica->getPipeline()->getByDescription("Weird", false);
        $objects = $response["objects"];
        $this->assertIsArray($objects);
        $this->assertCount(0, $objects);
    }

    /**
     * @covers \whikloj\archivematicaPhp\Storage\PipelineImpl::getByDescription
     * @covers \whikloj\archivematicaPhp\Storage\PipelineImpl::get
     */
    public function testGetPipelineByDescriptionExact(): void
    {
        VCR::insertCassette("pipeline_get_description_exact.yaml");
        $response = $this->archivematica->getPipeline()->getByDescription("Test pipeline", true);
        $objects = $response["objects"];
        $this->assertIsArray($objects);
        $this->assertCount(1, $objects);
    }

    /**
     * @covers \whikloj\archivematicaPhp\Storage\PipelineImpl::getByDescription
     * @covers \whikloj\archivematicaPhp\Storage\PipelineImpl::get
     */
    public function testGetPipelineByDescriptionNoneExact(): void
    {
        VCR::insertCassette("pipeline_get_description_none_exact.yaml");
        $response = $this->archivematica->getPipeline()->getByDescription("Weird lines", true);
        $objects = $response["objects"];
        $this->assertIsArray($objects);
        $this->assertCount(0, $objects);
    }

    /**
     * @covers \whikloj\archivematicaPhp\Storage\PipelineImpl::getByUuid
     */
    public function testGetPipelineByUuid(): void
    {
        VCR::insertCassette("pipeline_get_uuid.yaml");
        $uuid = "8490b352-6ad0-4590-a3f1-6dc5f8abd603";
        $response = $this->archivematica->getPipeline()->getByUuid($uuid);
        $this->assertIsArray($response);
        $this->assertArrayHasKey("uuid", $response);
        $this->assertEquals($uuid, $response["uuid"]);
    }

    /**
     * @covers \whikloj\archivematicaPhp\Storage\PipelineImpl::getByUuid
     */
    public function testGetPipelineByUuidNone(): void
    {
        VCR::insertCassette("pipeline_get_uuid_none.yaml");
        $this->expectException(ItemNotFoundException::class);
        $this->archivematica->getPipeline()->getByUuid("8490b352-6ad0-4590-aaaa-6dc5f8abd603");
    }
}
