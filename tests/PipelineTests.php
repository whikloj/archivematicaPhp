<?php

namespace whikloj\archivematicaPhp\Tests;

use VCR\VCR;
use whikloj\archivematicaPhp\ArchivematicaImpl;
use whikloj\archivematicaPhp\Utils\ArchivmaticaUtils;

class PipelineTests extends ArchivematicaPhpTestBase
{
    public function testGetPipelines()
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

    public function testGetPipelinesNone()
    {
        VCR::insertCassette("pipeline_none.yaml");
        // Test getting the pipelines available to the storage service where
        // there is at least one pipeline available to the service.
        $response = $this->archivematica->getPipeline()->getAll();
        $objects = $response["objects"];
        $this->assertIsArray($objects);
        $this->assertCount(0, $objects);
    }
}
