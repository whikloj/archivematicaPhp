<?php

namespace whikloj\archivematicaPhp\Utils;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use whikloj\archivematicaPhp\Exceptions\AuthorizationException;
use whikloj\archivematicaPhp\Exceptions\RequestException;

/**
 * Static utility class.
 * @author Jared Whiklo
 * @since 0.0.1
 */
class ArchivmaticaUtils
{
    /**
     * The allowed transfer types
     */
    private const TRANSFER_TYPES = [
        'standard',
        'zipped_directory',
        'unzipped_bag',
        'zipped_bag',
        'dspace',
        'disk_image',
        'dataverse',
    ];

    /**
     * The allowed package types.
     */
    private const PACKAGE_TYPES = [
        "AIP",
        "AIC",
        "DIP",
        "transfer",
        "SIP",
        "file",
        "deposit",
    ];

    /**
     * Basic constructor
     */
    private function __construct()
    {
        // Private constructor for static utility class.
    }

    /**
     * Determine the correct exception to throw for the Guzzle exception.
     *
     * @param GuzzleException $exception
     *   The exception to check.
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   If the GuzzleException is 403.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   If the GuzzleException is anything else.
     */
    public static function decodeGuzzleException(GuzzleException $exception): void
    {
        if ($exception->getCode() === 403) {
            // 403 Forbidden, insufficient permissions.
            throw new AuthorizationException(
                "Authorization failure, {$exception->getCode()}: {$exception->getMessage()}",
                $exception->getCode(),
                $exception
            );
        }
        throw new RequestException(
            "Request failed, {$exception->getCode()}: {$exception->getMessage()}",
            $exception->getCode(),
            $exception
        );
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     *   The response to check.
     * @param int $expected_code
     *   The expected response code.
     * @param string $message
     *   A message to throw with the exception if necessary.
     * @return array
     *   An array from the response body.
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   If the response was a 403 Forbidden.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   If the response is invalid.
     */
    public static function checkResponse(
        ResponseInterface $response,
        int $expected_code,
        string $message
    ): array {
        $code = $response->getStatusCode();
        if ($code === 403) {
            throw new AuthorizationException(
                "Invalid credentials or insufficient permissions: {$response->getReasonPhrase()}",
                $code
            );
        } elseif ($code !== $expected_code) {
            $excep_message = $response->getReasonPhrase();
            if (
                $response->hasHeader("content-type") &&
                in_array("application/json", $response->getHeader("content-type"))
            ) {
                $body = json_decode($response->getBody()->getContents(), true);
                if (array_key_exists("error_message", $body)) {
                    $excep_message = $body['error_message'];
                }
            }
            throw new RequestException(
                "$message: $excep_message",
                $code
            );
        }
        $body = json_decode($response->getBody()->getContents(), true);
        if (!is_array($body)) {
            $body = [$body];
        }
        return $body;
    }

    /**
     * Check whether the provided UUID is considered valid by Archivematica
     *
     * @param string $uuid
     *   The UUID to test.
     * @return bool
     *   Whether it is valid or not.
     */
    public static function isUuid(string $uuid): bool
    {
        return preg_match(
            "/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/",
            $uuid
        ) === 1;
    }

    /**
     * Reformat a UUID as a "URI", which is just with the URI path and no hostname.
     *
     * @param string $uuid
     *   The UUID to reformat.
     * @param string $type
     *   The type the UUID is.
     * @return string
     *   The new "URI".
     */
    public static function asUri(string $uuid, string $type): string
    {
        if (substr_compare($uuid, "/api/v2/", 0, null, true) == 0) {
            // Already somewhat URI formatted, pass it back.
            return $uuid;
        }
        $types = ["pipeline", "location", "space"];
        if (!in_array($type, $types)) {
            throw new \InvalidArgumentException("\$type must be one of " . implode(", ", $types));
        }
        $type = strtolower($type);
        $uuid = strtolower($uuid);
        return "/api/v2/$type/$uuid/";
    }

    /**
     * Test whether a transfer type is valid.
     *
     * @param string $type
     *   The transfer type to check
     * @throws \InvalidArgumentException
     *   On invalid transfer type.
     */
    public static function isValidTransferType(string $type): void
    {
        if (!in_array($type, self::TRANSFER_TYPES)) {
            throw new \InvalidArgumentException(
                "Invalid transfer type ($type) provided, must be one of " . implode(", ", self::TRANSFER_TYPES)
            );
        }
    }

    /**
     * Test if whether a package type is valid.
     *
     * @param string $type
     *   The package type to check.
     * @throws \InvalidArgumentException
     *   On invalid package type.
     */
    public static function isValidPackageType(string $type): void
    {
        if (!in_array($type, self::PACKAGE_TYPES)) {
            throw new \InvalidArgumentException(
                "Invalid package type ($type) provided must be one of " . implode(", ", self::PACKAGE_TYPES)
            );
        }
    }

    /**
     * Used as an array_walk function to base64 encode the elements.
     *
     * @param string $o
     *   An array element.
     */
    public static function base64EncodeValue(string &$o): void
    {
        $o = base64_encode($o);
    }

    /**
     * Used as an array_walk function to base64 decode the elements.
     *
     * @param string $o
     *   An array element
     */
    public static function base64DecodeValue(string &$o): void
    {
        $o = base64_decode($o, true);
    }
}
