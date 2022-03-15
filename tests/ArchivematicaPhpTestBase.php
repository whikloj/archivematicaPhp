<?php

namespace whikloj\archivematicaPhp\Tests;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use VCR\VCR;
use whikloj\archivematicaPhp\ArchivematicaImpl;

class ArchivematicaPhpTestBase extends TestCase
{
    protected const AM_URL = "http://192.168.168.192";
    protected const SS_URL = "http://192.168.168.192:8000";
    protected const AM_USER_NAME = "test";
    protected const AM_API_KEY = "3c23b0361887ace72b9d42963d9acbdf06644673";
    protected const SS_USER_NAME = "test";
    protected const SS_API_KEY = "5de62f6f4817f903dcfac47fa5cffd44685a2cf2";
    protected const TMP_DIR = ".tmp-downloads";
    protected const TRANSFER_SOURCE_UUID = "7609101e-15b2-4f4f-a19d-7b23673ac93b";

    protected $archivematica;

    public function setUp(): void
    {
        VCR::configure()->setCassettePath(__DIR__ . '/../tests/fixtures')
            ->setMode(VCR::MODE_NONE);
        VCR::turnOn();
        $handler = new StreamHandler("archivematicaPhpTests.log", Logger::DEBUG);
        $logger = new Logger("archivematicaPhp", [$handler]);
        $this->archivematica = ArchivematicaImpl::create(
            self::AM_URL,
            self::SS_URL
        )
            ->setAMCreds(self::AM_USER_NAME, self::AM_API_KEY)
            ->setSSCreds(self::SS_USER_NAME, self::SS_API_KEY)
            ->setLogger($logger);
    }

    public function tearDown(): void
    {
        VCR::eject();
        VCR::turnOff();
    }

    /**
     * Switch to using the docker instance values for seeing real responses.
     *
     * @throws \whikloj\archivematicaPhp\Exceptions\ArchivematicaException
     */
    protected function switchToLive(): void
    {
        $local_am_user = 'test';
        $local_am_key = '817deb8f6fb537d0c6afc417110261de0a1cc336';
        $local_ss_user = 'test';
        $local_ss_key = 'test';
        $this->archivematica = ArchivematicaImpl::create(
            'http://127.0.0.1:62080',
            'http://127.0.0.1:62081'
        )
            ->setAMCreds($local_am_user, $local_am_key)
            ->setSSCreds($local_ss_user, $local_ss_key);
        VCR::configure()->setMode("new_episodes");
    }

    /**
     * Compare two arrays have all the same elements, does not compare order.
     *
     * @param array $expected The expected array.
     * @param array $testing The array to test.
     */
    protected function assertArrayEquals(array $expected, array $testing): void
    {
        // They have the same number of elements
        $this->assertCount(count($expected), $testing);
        // All the elements in $expected exist in $testing
        $this->assertCount(0, array_diff($expected, $testing));
        // All the elements in $testing exist in $expected (possibly overkill)
        $this->assertCount(0, array_diff($testing, $expected));
    }

    /**
     * Create a temporary directory
     *
     * @return string
     *   The temporary directory path.
     * @throws \Exception
     *   Unable to create a temporary file or delete the file and create a directory.
     */
    protected function createTempDir(): string
    {
        $tempname = @tempnam("", "archivematicaPhp_");
        if ($tempname !== false) {
            if (@unlink($tempname)) {
                if (@mkdir($tempname)) {
                    return $tempname;
                }
            }
        }
        throw new \Exception("Unable to create a temporary directory");
    }
}
