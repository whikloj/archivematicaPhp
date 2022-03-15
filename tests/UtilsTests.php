<?php

namespace whikloj\archivematicaPhp\Tests;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use whikloj\archivematicaPhp\Exceptions\AuthorizationException;
use whikloj\archivematicaPhp\Exceptions\ItemNotFoundException;
use whikloj\archivematicaPhp\Exceptions\RequestException;
use whikloj\archivematicaPhp\Utils\ArchivmaticaUtils;

/**
 * Tests of the utility class.
 * @author Jared Whiklo
 * @since 0.0.1
 */
class UtilsTests extends ArchivematicaPhpTestBase
{
    /**
     * @covers \whikloj\archivematicaPhp\Utils\ArchivmaticaUtils::base64EncodeValue
     */
    public function testBase64Encode(): void
    {
        $original = [
            "some",
            "random",
            "text",
        ];
        $expected = [
            base64_encode("some"),
            base64_encode("random"),
            base64_encode("text"),
        ];
        array_walk($original, ['\whikloj\archivematicaPhp\Utils\ArchivmaticaUtils', 'base64EncodeValue']);
        $this->assertArrayEquals($expected, $original);
    }

    /**
     * @covers \whikloj\archivematicaPhp\Utils\ArchivmaticaUtils::base64DecodeValue
     */
    public function testBase64Decode(): void
    {
        $original = [
            base64_encode("some"),
            base64_encode("random"),
            base64_encode("text"),
        ];
        $expected = [
            "some",
            "random",
            "text",
        ];
        array_walk($original, ['\whikloj\archivematicaPhp\Utils\ArchivmaticaUtils', 'base64DecodeValue']);
        $this->assertArrayEquals($expected, $original);
    }

    /**
     * @covers \whikloj\archivematicaPhp\Utils\ArchivmaticaUtils::isValidTransferType
     */
    public function testIsValidTransferType(): void
    {
        $expected = [
            'standard',
            'zipped_directory',
            'unzipped_bag',
            'zipped_bag',
            'dspace',
            'disk_image',
            'dataverse',
        ];
        foreach ($expected as $type) {
            ArchivmaticaUtils::isValidTransferType($type);
        }
        // Now throw one not in the list.
        $this->expectException(\InvalidArgumentException::class);
        ArchivmaticaUtils::isValidTransferType("AIP");
    }

    /**
     * @covers \whikloj\archivematicaPhp\Utils\ArchivmaticaUtils::isValidPackageType
     */
    public function testIsValidPackageType(): void
    {
        $expected = [
            "AIP",
            "AIC",
            "DIP",
            "transfer",
            "SIP",
            "file",
            "deposit",
        ];
        foreach ($expected as $type) {
            ArchivmaticaUtils::isValidPackageType($type);
        }
        // Now throw one not in the list.
        $this->expectException(\InvalidArgumentException::class);
        ArchivmaticaUtils::isValidPackageType("zipped_directory");
    }

    /**
     * @covers \whikloj\archivematicaPhp\Utils\ArchivmaticaUtils::decodeGuzzleException
     */
    public function testDecodeGuzzleException404(): void
    {
        $g = new ClientException(
            "Failed to locate stuff",
            new Request("GET", "http://example.org/some/uri"),
            new Response(404)
        );
        $this->expectException(RequestException::class);
        $this->expectExceptionCode(404);
        ArchivmaticaUtils::decodeGuzzleException($g);
    }

    /**
     * @covers \whikloj\archivematicaPhp\Utils\ArchivmaticaUtils::assertResponseCode
     */
    public function testAssertResponseCode404(): void
    {
        $notfound = new Response(404);

        ArchivmaticaUtils::assertResponseCode($notfound, 404, "We expected a 404");

        $this->expectException(ItemNotFoundException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage("Item was not located: We fail on a 404, Not Found");
        ArchivmaticaUtils::assertResponseCode($notfound, 200, "We fail on a 404");
    }

    /**
     * @covers \whikloj\archivematicaPhp\Utils\ArchivmaticaUtils::assertResponseCode
     */
    public function testAssertResponseCode403(): void
    {
        $notfound = new Response(403);
        $this->expectException(AuthorizationException::class);
        $this->expectExceptionCode(403);
        $this->expectExceptionMessage("Invalid credentials or insufficient permissions: We expected a 404, Forbidden");
        ArchivmaticaUtils::assertResponseCode($notfound, 404, "We expected a 404");
    }

    /**
     * @covers \whikloj\archivematicaPhp\Utils\ArchivmaticaUtils::assertResponseCode
     */
    public function testAssertResponseCode500(): void
    {
        $notfound = new Response(500);
        $this->expectException(RequestException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage("We expected a 404: Internal Server Error");
        ArchivmaticaUtils::assertResponseCode($notfound, 404, "We expected a 404");
    }

    /**
     * @covers \whikloj\archivematicaPhp\Utils\ArchivmaticaUtils::assertResponseCode
     */
    public function testAssertResponseCode500Json(): void
    {
        $notfound = new Response(
            500,
            ['Content-type' => 'application/json'],
            '{"error":true, "error_message":"Very bad internal error"}'
        );
        $this->expectException(RequestException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage("We expected a 404: Very bad internal error");
        ArchivmaticaUtils::assertResponseCode($notfound, 404, "We expected a 404");
    }

    /**
     * @covers \whikloj\archivematicaPhp\Utils\ArchivmaticaUtils::assertResponseCode
     */
    public function testAssertResponseCode500JsonError(): void
    {
        $notfound = new Response(
            500,
            ['Content-type' => 'application/json'],
            '{"error":true, "message":"Very bad internal error"}'
        );
        $this->expectException(RequestException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage("We expected a 404: Internal Server Error");
        ArchivmaticaUtils::assertResponseCode($notfound, 404, "We expected a 404");
    }

    /**
     * @covers \whikloj\archivematicaPhp\Utils\ArchivmaticaUtils::asUri
     */
    public function testAsUri(): void
    {
        // If the string begins with /api/v2 then we return it unchanged.
        $this->assertEquals("/api/v2/BLAH", ArchivmaticaUtils::asUri("/api/v2/BLAH", "ballons"));

        $initial = "random-uuid-number";
        $this->assertEquals("/api/v2/location/$initial/", ArchivmaticaUtils::asUri($initial, "location"));
        $this->assertEquals("/api/v2/space/$initial/", ArchivmaticaUtils::asUri($initial, "space"));
        $this->assertEquals("/api/v2/pipeline/$initial/", ArchivmaticaUtils::asUri($initial, "pipeline"));

        $this->expectException(\InvalidArgumentException::class);
        ArchivmaticaUtils::asUri($initial, "ballons");
    }
}
