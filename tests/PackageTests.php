<?php

namespace whikloj\archivematicaPhp\Tests;

use VCR\VCR;
use whikloj\archivematicaPhp\Exceptions\ItemNotFoundException;
use whikloj\archivematicaPhp\Exceptions\RequestException;
use whikloj\archivematicaPhp\Utils\ArchivmaticaUtils;

/**
 * Tests of the Package object methods.
 * @author Jared Whiklo
 * @since 0.0.1
 */
class PackageTests extends ArchivematicaPhpTestBase
{
    /**
     * @covers \whikloj\archivematicaPhp\PackageImpl::getAll
     * @covers \whikloj\archivematicaPhp\PackageImpl::internalGet
     */
    public function testGetAllPackages(): void
    {
        VCR::insertCassette("aips_and_dips.yaml");
        $packages = $this->archivematica->getPackage()->getAll();
        $this->assertIsArray($packages);
        $this->assertEquals(11, $packages["total_count"]);
        $this->assertCount(11, $packages["objects"]);
        $aips = array_filter($packages["objects"], function ($o) {
            return $o["package_type"] == "AIP";
        });
        $this->assertCount(3, $aips);
        $dips = array_filter($packages["objects"], function ($o) {
            return $o["package_type"] == "DIP";
        });
        $this->assertCount(7, $dips);
        $transfer = array_filter($packages["objects"], function ($o) {
            return $o["package_type"] == "transfer";
        });
        $this->assertCount(1, $transfer);
    }

    /**
     * @covers \whikloj\archivematicaPhp\PackageImpl::getAllAips
     * @covers \whikloj\archivematicaPhp\PackageImpl::internalGet
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
     * @covers \whikloj\archivematicaPhp\PackageImpl::internalGet
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
     * @covers \whikloj\archivematicaPhp\PackageImpl::internalGet
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

    /**
     * @covers \whikloj\archivematicaPhp\PackageImpl::getAips2Dips
     * @covers \whikloj\archivematicaPhp\PackageImpl::internalGet
     */
    public function testAips2Dips(): void
    {
        VCR::insertCassette("aips2dips.yaml");
        // Test that we can get all AIPs in the Storage Service and their corresonding DIPs.
        $aips2dips = $this->archivematica->getPackage()->getAips2Dips();
        $this->assertIsArray($aips2dips);
        $this->assertCount(4, $aips2dips);
        $this->assertArrayEquals(
            [],
            $aips2dips["3500aee0-08ca-40ff-8d2d-9fe9a2c3ae3b"]
        );
        $this->assertArrayEquals(
            [],
            $aips2dips["979cce65-2a6f-407f-a49c-1bcf13bd8571"]
        );
        $this->assertArrayEquals(
            ["c0e37bab-e51e-482d-a066-a277330de9a7"],
            $aips2dips["721b98b9-b894-4cfb-80ab-624e52263300"]
        );
        $this->assertArrayEquals(
            ["7e49afa4-116b-4650-8bbb-9341906bdb21"],
            $aips2dips["99bb20ee-69c6-43d0-acf0-c566020357d2"]
        );
    }

    /**
     * @covers \whikloj\archivematicaPhp\PackageImpl::getDipsForAip
     * @covers \whikloj\archivematicaPhp\PackageImpl::internalGet
     */
    public function testAip2DipsDips(): void
    {
        VCR::insertCassette("aip2dips_dip.yaml");
        // Test that we can get all of the DIPs from the Storage Service for a given AIP.
        $aip_uuid = "721b98b9-b894-4cfb-80ab-624e52263300";
        $dip_uuid = "c0e37bab-e51e-482d-a066-a277330de9a7";
        $dips = $this->archivematica->getPackage()->getDipsForAip($aip_uuid);
        $this->assertIsArray($dips);
        $this->assertCount(1, $dips);
        $dip = $dips[0];
        $this->assertIsArray($dip);
        $this->assertEquals("DIP", $dip["package_type"]);
        $this->assertEquals($dip_uuid, $dip["uuid"]);
    }

    /**
     * @covers \whikloj\archivematicaPhp\PackageImpl::getDipsForAip
     * @covers \whikloj\archivematicaPhp\PackageImpl::internalGet
     */
    public function testAip2DipsDipsUuidOnly(): void
    {
        VCR::insertCassette("aip2dips_dip.yaml");
        // Test that we can get all of the DIPs from the Storage Service for a given AIP.
        $aip_uuid = "721b98b9-b894-4cfb-80ab-624e52263300";
        $dip_uuid = "c0e37bab-e51e-482d-a066-a277330de9a7";
        $dips = $this->archivematica->getPackage()->getDipsForAip($aip_uuid, true);
        $this->assertIsArray($dips);
        $this->assertCount(1, $dips);
        $dip = $dips[0];
        $this->assertIsNotArray($dip);
        $this->assertEquals($dip_uuid, $dip);
    }

    /**
     * @covers \whikloj\archivematicaPhp\PackageImpl::getDipsForAip
     * @covers \whikloj\archivematicaPhp\PackageImpl::internalGet
     */
    public function testAip2Dips(): void
    {
        VCR::insertCassette("aip2dips_no_dip.yaml");
        // Test that we get no DIPs when attempting to get all DIPs corresponding to an AIP that has none.
        $aip_uuid = "3500aee0-08ca-40ff-8d2d-9fe9a2c3ae3b";
        $dips = $this->archivematica->getPackage()->getDipsForAip($aip_uuid);
        $this->assertIsArray($dips);
        $this->assertCount(0, $dips);
    }

    /**
     * @covers \whikloj\archivematicaPhp\PackageImpl::delete
     */
    public function testDeleteAipSuccess(): void
    {
        VCR::insertCassette("delete_aip_success.yaml");
        // Test that we can request deletion of existing AIP.
        $aip_uuid = "b36758e8-fe77-4af6-8b1e-b3dd074c25d0";
        $pipeline_uuid = "8490b352-6ad0-4590-a3f1-6dc5f8abd603";
        $response = $this->archivematica->getPackage()->delete(
            $aip_uuid,
            $pipeline_uuid,
            "Testing that deletion request works",
            1,
            "test@example.com"
        );
        $this->assertEquals(2, $response);
    }

    /**
     * @covers \whikloj\archivematicaPhp\PackageImpl::delete
     */
    public function testDeleteAipFail(): void
    {
        VCR::insertCassette("delete_aip_fail.yaml");
        // Test that we can try to delete an AIP that does not exist.
        $aip_uuid = "bad-aip-uuid";
        $pipeline_uuid = "a49dce91-3dca-4228-a271-0327ea89afb6";
        $this->expectException(ItemNotFoundException::class);
        $this->archivematica->getPackage()->delete(
            $aip_uuid,
            $pipeline_uuid,
            "Testing when deletion request doesn't work",
            1,
            "test@example.com"
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

    /**
     * @covers \whikloj\archivematicaPhp\PackageImpl::details
     */
    public function testGetPackageDetails(): void
    {
        VCR::insertCassette("get_package_details.yaml");
        // Test that amclient can retrieve details about a package.
        $package_uuid = "23129471-09e3-467e-88b6-eb4714afb5ac";
        $response = $this->archivematica->getPackage()->details($package_uuid);
        $status = $response["status"];
        $package_type = $response["package_type"];
        $this->assertEquals("UPLOADED", $status);
        $this->assertEquals("AIP", $package_type);
    }

    /**
     * @covers \whikloj\archivematicaPhp\PackageImpl::details
     */
    public function testGetPackageDetailsInvalidUuid(): void
    {
        VCR::insertCassette("get_package_details_invalid_uuid.yaml");
        // Test amlient's response when an invalid package uuid is provided to the get package details endpoint.
        $package_uuid = "23129471-baad-f00d-88b6-eb4714afb5ac";
        $this->expectException(ItemNotFoundException::class);
        $this->archivematica->getPackage()->details($package_uuid);
    }
}
