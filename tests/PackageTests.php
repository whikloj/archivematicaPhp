<?php

namespace whikloj\archivematicaPhp\Tests;

use VCR\VCR;
use whikloj\archivematicaPhp\Utils\ArchivmaticaUtils;

class PackageTests extends ArchivematicaPhpTestBase
{
    /**
     * @covers \whikloj\archivematicaPhp\PackageImpl::getAllAips
     */
    public function testAipsAips(): void
    {
        VCR::insertCassette("aips_aips.yaml");
        // Test that we can get all AIPs in the Storage Service.
        $aips = $this->archivematica->getPackage()->getAllAips();
        $this->assertIsArray($aips);
        $this->assertArrayHasKey("total_count", $aips);
        $this->assertEquals(2, $aips["total_count"]);
        $this->assertArrayHasKey("objects", $aips);
        $objects = $aips["objects"];
        $this->assertIsArray($objects);
        $this->assertCount(2, $objects);
        foreach ($objects as $object) {
            $this->assertIsArray($object);
            $this->assertArrayHasKey("uuid", $object);
            $this->assertTrue(ArchivmaticaUtils::isUuid($object["uuid"]));
            $this->assertEquals("AIP", $object["package_type"]);
            $this->assertStringContainsString("AIPsStore", $object["current_full_path"]);
        }
    }

    /**
     * @covers \whikloj\archivematicaPhp\PackageImpl::getAllDips
     */
    public function testDipsDips(): void
    {
        VCR::insertCassette("dips_dips.yaml");
        // Test that we can get all DIPs in the Storage Service.
        $dips = $this->archivematica->getPackage()->getAllDips();
        $this->assertIsArray($dips);
        $this->assertArrayHasKey("total_count", $dips);
        $this->assertEquals(2, $dips["total_count"]);
        $this->assertArrayHasKey("objects", $dips);
        $objects = $dips["objects"];
        $this->assertIsArray($objects);
        $this->assertCount(2, $objects);
        foreach ($objects as $object) {
            $this->assertIsArray($object);
            $this->assertArrayHasKey("uuid", $object);
            $this->assertTrue(ArchivmaticaUtils::isUuid($object["uuid"]));
            $this->assertEquals("DIP", $object["package_type"]);
            $this->assertStringContainsString("DIPsStore", $object["current_full_path"]);
        }
    }

    /**
     * @covers \whikloj\archivematicaPhp\PackageImpl::getAllDips
     */
    public function testDipsNoDips(): void
    {
        VCR::insertCassette("dips_no_dips.yaml");
        // Test that we get no DIPs from the Storage Service if there are none.
        $dips = $this->archivematica->getPackage()->getAllDips();
        $this->assertIsArray($dips);
        $this->assertArrayHasKey("total_count", $dips);
        $this->assertEquals(0, $dips["total_count"]);
        $this->assertArrayHasKey("objects", $dips);
        $this->assertCount(0, $dips["objects"]);
    }
}
