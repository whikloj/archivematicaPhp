<?php

namespace whikloj\archivematicaPhp\Tests;

class InternalTests extends ArchivematicaPhpTestBase
{
    /**
     * Get a private or protected method to test it directly.
     *
     * @param string $class
     *   Class to refect.
     * @param string $method_name
     *   Method to get.
     *
     * @return \ReflectionMethod
     *   Reflection of the method.
     *
     * @throws \ReflectionException
     */
    private static function getReflectionMethod(string $class, string $method_name): \ReflectionMethod
    {
        $class = new \ReflectionClass($class);
        $methodCall = $class->getMethod($method_name);
        $methodCall->setAccessible(true);
        return $methodCall;
    }

    /**
     * @covers \whikloj\archivematicaPhp\PackageImpl::contentTypeToExtension
     */
    public function testContentTypeToExtension(): void
    {
        $method = self::getReflectionMethod("whikloj\archivematicaPhp\PackageImpl", "contentTypeToExtension");
        $this->assertEquals(
            "tar",
            $method->invokeArgs($this->archivematica->getPackage(), ["application/x-tar"])
        );
        $this->assertEquals(
            "gz",
            $method->invokeArgs($this->archivematica->getPackage(), ["application/x-gzip-compressed"])
        );
        $this->assertEquals(
            "7z",
            $method->invokeArgs($this->archivematica->getPackage(), ["application/x-7z-compressed"])
        );
        $this->assertEquals(
            "zip",
            $method->invokeArgs($this->archivematica->getPackage(), ["application/x-zip-compressed"])
        );
        $this->assertEquals(
            "bz2",
            $method->invokeArgs($this->archivematica->getPackage(), ["application/x-bzip2"])
        );
        $this->assertEquals(
            "7z",
            $method->invokeArgs($this->archivematica->getPackage(), ["image/tiff"])
        );
    }

    /**
     * @covers \whikloj\archivematicaPhp\PackageImpl::processListing
     */
    public function testProcessListingSuccess(): void
    {
        $method = self::getReflectionMethod('\whikloj\archivematicaPhp\PackageImpl', 'processListing');
        $starting = [
            "meta" => [
                "total_count" => 2,
                "next" => null,
                "previous" => null,
                "offset" => 0,
            ],
            "objects" => [
                [
                    "uuid" => "test-object-1",
                ],
                [
                    "uuid" => "test-object-2",
                ],
            ],
        ];
        $output = $method->invokeArgs($this->archivematica->getPackage(), [$starting]);
        $this->assertIsArray($output);
        $this->assertArrayHasKey("total_count", $output);
        $this->assertArrayHasKey("next", $output);
        $this->assertArrayHasKey("objects", $output);
        $this->assertArrayNotHasKey("previous", $output);
        $this->assertArrayNotHasKey("offset", $output);
        $this->assertIsArray($output["objects"]);
        $this->assertCount(2, $output["objects"]);
    }

    /**
     * @covers \whikloj\archivematicaPhp\TransferImpl::getEncodedPaths
     */
    public function testTransferEncodedPaths(): void
    {
        $method = self::getReflectionMethod('\whikloj\archivematicaPhp\TransferImpl', 'getEncodedPaths');
        $original = [
            "key1" => "value1",
            "key2" => "value2",
            "key3" => "value3",
        ];
        $expected = [
            base64_encode("key1:value1"),
            base64_encode("key2:value2"),
            base64_encode("key3:value3"),
        ];
        $output = $method->invokeArgs($this->archivematica->getTransfer(), [$original]);
        $this->assertArrayEquals($expected, $output);
    }
}
