<?php

namespace whikloj\archivematicaPhp\Tests\Storage\Spaces;

use VCR\VCR;
use whikloj\archivematicaPhp\ArchivematicaImpl;
use whikloj\archivematicaPhp\Exceptions\Storage\InvalidSpaceTypeException;
use whikloj\archivematicaPhp\Exceptions\Storage\SpaceTypeException;
use whikloj\archivematicaPhp\Tests\ArchivematicaPhpTestBase;

/**
 * Test SpaceTypes
 * @author Jared Whiklo
 * @since 0.0.1
 *
 * Currently only testing local filesystem as until I have access and a use case
 * for the other systems to try and see what their responses look like.
 */
class SpaceTypeTests extends ArchivematicaPhpTestBase
{
    private function validateCreateSpaceResponse(array $response): void
    {
        $this->assertIsArray($response);
        $this->assertArrayHasKey("id", $response);
        $this->assertIsInt($response['id']);
        $this->assertArrayHasKey("access_protocol", $response);
        $this->assertArrayHasKey("path", $response);
        $this->assertArrayHasKey("resource_uri", $response);
        $this->assertArrayHasKey("space", $response);
        $this->assertArrayHasKey("uuid", $response);
        $this->assertEquals($response['space'], $response['uuid']);
        $this->assertEquals("/api/v2/space/{$response['uuid']}/", $response["resource_uri"]);
        $this->assertArrayHasKey("staging_path", $response);
        $this->assertArrayHasKey("used", $response);
        $this->assertIsInt($response['used']);
        $this->assertArrayHasKey("verified", $response);
        $this->assertIsBool($response["verified"]);
        $this->assertArrayHasKey("last_verified", $response);
        $this->assertArrayHasKey("size", $response);
        if (!is_null($response['size']) && !is_int($response['size'])) {
            $this->fail("Size should be null or int");
        }
    }

    public function testBadSpaceType()
    {
        $this->expectException(InvalidSpaceTypeException::class);
        $this->archivematica->getSpace()->create('BadType', []);
    }

    public function testLocalRequired()
    {
        VCR::insertCassette("create_space_local_required.yaml");
        $fields = [
            "path" => "/test/path",
            "staging_path" => "/opt/staging/path",
        ];
        $response = $this->archivematica->getSpace()->create('LocalFilesystem', $fields);
        $this->validateCreateSpaceResponse($response);
    }

    public function testLocalOptionalOnly()
    {
        $this->expectException(SpaceTypeException::class);
        $fields = [
            "size" => 500,
        ];
        $this->archivematica->getSpace()->create('LocalFilesystem', $fields);
    }

    public function testLocalRequiredAndOptional()
    {
        VCR::insertCassette("create_space_local_required_optional.yaml");
        $fields = [
            "path" => "/test/path",
            "staging_path" => "/opt/staging/path",
            "size" => 500,
        ];
        $response = $this->archivematica->getSpace()->create('LocalFilesystem', $fields);
        $this->validateCreateSpaceResponse($response);
        $this->assertEquals(500, $response['size']);
    }

    public function testLocalRequiredAndExtraParam()
    {
        VCR::insertCassette("create_space_local_extra.yaml");
        $fields = [
            "path" => "/test/path",
            "staging_path" => "/opt/staging/path",
            "host" => "http://arkivum.localhost"
        ];
        $response = $this->archivematica->getSpace()->create('LocalFilesystem', $fields);
        $this->validateCreateSpaceResponse($response);
        $this->assertArrayNotHasKey('host', $response);
    }
}
