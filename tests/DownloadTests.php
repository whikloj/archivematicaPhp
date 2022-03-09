<?php

namespace whikloj\archivematicaPhp\Tests;

use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\Response;
use donatj\MockWebServer\Responses\NotFoundResponse;
use VCR\VCR;
use whikloj\archivematicaPhp\ArchivematicaImpl;
use whikloj\archivematicaPhp\Exceptions\ItemNotFoundException;

/**
 * Tests that attempt to download a package, as php-vcr does not support streams in Guzzle.
 * @author Jared Whiklo
 * @since 0.0.1
 */
class TestDownloads extends ArchivematicaPhpTestBase
{
    /**
     * Path to static files.
     */
    private const WEBSERVER_FILES_DIR = __DIR__ . DIRECTORY_SEPARATOR . "static_files";

    /**
     * Some common headers for our download responses
     */
    private const COMMON_HEADERS = [
        "Cache-Control: no-cache",
        "Connection: close",
        "Date: 'Thu, 16 Nov 2017 16:33:55 GMT'",
        "Server: nginx/1.4.6 (Ubuntu)",
        "Vary: 'Accept, Accept-Language, Cookie'",
        "X-Frame-Options: SAMEORIGIN",
        "Content-Language: en",
    ];

    /**
     * @var \donatj\MockWebServer\MockWebServer The mock webserver
     */
    private static $webserver;

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass(): void
    {
        self::$webserver = new MockWebServer();
        self::$webserver->start();
        self::$webserver->setDefaultResponse(new NotFoundResponse());
    }

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        parent::setUp();
        VCR::turnOff();
        $this->archivematica = ArchivematicaImpl::create(
            self::AM_URL,
            self::$webserver->getServerRoot()
        )->setAMCreds(self::AM_USER_NAME, self::AM_API_KEY)
         ->setSSCreds(self::SS_USER_NAME, self::SS_API_KEY);
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown(): void
    {
        // no-op to avoid trying to turn off VCR again.
    }

    /**
     * {@inheritdoc}
     */
    public static function tearDownAfterClass(): void
    {
        self::$webserver->stop();
    }

    /**
     * @covers \whikloj\archivematicaPhp\PackageImpl::download
     */
    public function testDownloadDipNoDip(): void
    {
        // Test that we can try to download a DIP that does not exist.
        $temp_dir = $this->createTempDir();
        $dip_uuid = "bad dip uuid";

        self::$webserver->setResponseOfPath(
            "/api/v2/file/$dip_uuid/download/",
            new NotFoundResponse()
        );

        try {
            $this->expectException(ItemNotFoundException::class);
            $this->archivematica->getPackage()->download(
                $dip_uuid,
                $temp_dir
            );
        } finally {
            @rmdir($temp_dir);
        }
    }

    /**
     * @covers \whikloj\archivematicaPhp\PackageImpl::download
     */
    public function testDownloadDipDip(): void
    {
        $temp_dir = $this->createTempDir();
        $aip_uuid = "73892c2c-c63d-47e6-b678-4918fe735f89";
        $dip_uuid = "ca8c6851-badb-4692-b7e9-138d23b07be3";
        $transfer_name = "small_transfer";

        $filename = self::WEBSERVER_FILES_DIR .
            DIRECTORY_SEPARATOR . 'small_transfer-73892c2c-c63d-47e6-b678-4918fe735f89.tar';
        self::$webserver->setResponseOfPath(
            "/api/v2/file/$dip_uuid/download/",
            new Response(
                file_get_contents($filename),
                [
                    "Content-Type: application/x-tar",
                    "Content-Length: " . stat($filename)[7],
                    "Content-Disposition: 'attachment; filename=" .
                    "\"small_transfer-73892c2c-c63d-47e6-b678-4918fe735f89.tar\"'",
                ] + self::COMMON_HEADERS,
                200
            )
        );

        $dip_path = $this->archivematica->getPackage()->download(
            $dip_uuid,
            $temp_dir
        );
        $this->assertEquals(
            $temp_dir . DIRECTORY_SEPARATOR . "$transfer_name-$aip_uuid.tar",
            $dip_path
        );
        $this->assertFileExists($dip_path);
        $this->assertEquals("d8faa14c4a6b72f5fe54df8720b5039a847c303a", hash_file("sha1", $dip_path));
        @unlink($dip_path);
        @rmdir($temp_dir);
    }

    /**
     * @covers \whikloj\archivematicaPhp\PackageImpl::download
     */
    public function testDownloadAipSuccess(): void
    {
        // Test that we can download an AIP when there is one.
        $temp_dir = $this->createTempDir();
        $aip_uuid = "73892c2c-c63d-47e6-b678-4918fe735f89";
        $transfer_name = "small_transfer";

        $filename = self::WEBSERVER_FILES_DIR .
            DIRECTORY_SEPARATOR . 'small_transfer-73892c2c-c63d-47e6-b678-4918fe735f89.7z';
        self::$webserver->setResponseOfPath(
            "/api/v2/file/$aip_uuid/download/",
            new Response(
                file_get_contents($filename),
                [
                    "Content-Type: application/x-tar",
                    "Content-Length: " . stat($filename)[7],
                    "Content-Disposition: 'attachment; filename=" .
                    "\"small_transfer-73892c2c-c63d-47e6-b678-4918fe735f89.7z\"'",
                ] + self::COMMON_HEADERS,
                200
            )
        );

        $aip_path = $this->archivematica->getPackage()->download(
            $aip_uuid,
            $temp_dir
        );
        $this->assertEquals(
            $temp_dir . DIRECTORY_SEPARATOR . "$transfer_name-$aip_uuid.7z",
            $aip_path
        );
        $this->assertFileExists($aip_path);
        $this->assertEquals("fffd20ac44040b616ba3931be1bb84d3d4211abd", hash_file("sha1", $aip_path));
        @unlink($aip_path);
        @rmdir($temp_dir);
    }

    /**
     * @covers \whikloj\archivematicaPhp\PackageImpl::download
     */
    public function testDownloadAipFail(): void
    {
        // Test that we can try to download an AIP that does not exist.
        $temp_dir = $this->createTempDir();
        $aip_uuid = "bad-aip-uuid";

        self::$webserver->setResponseOfPath(
            "/api/v2/file/$aip_uuid/download/",
            new NotFoundResponse()
        );

        try {
            $this->expectException(ItemNotFoundException::class);
            $this->archivematica->getPackage()->download(
                $aip_uuid,
                $temp_dir
            );
        } finally {
            rmdir($temp_dir);
        }
    }
}
