<?php

namespace whikloj\archivematicaPhp\Utils;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use whikloj\archivematicaPhp\Exceptions\AuthorizationException;
use whikloj\archivematicaPhp\Exceptions\ItemNotFoundException;
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
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   If the GuzzleException is anything else.
     */
    public static function decodeGuzzleException(GuzzleException $exception): void
    {
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
     * @throws \whikloj\archivematicaPhp\Exceptions\ItemNotFoundException
     *   Thrown if status code was 404 and not expected.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   If the response is invalid.
     */
    public static function decodeJsonResponse(
        ResponseInterface $response,
        int $expected_code,
        string $message
    ): array {
        self::assertResponseCode($response, $expected_code, $message);
        $body = json_decode($response->getBody()->getContents(), true);
        if (!is_array($body)) {
            $body = [$body];
        }
        return $body;
    }

    /**
     * Check the response for the expected response code.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *   The response to check
     * @param int $expected_code
     *   The expected HTTP status code
     * @param string $error_message
     *   A contextual error message to add to any exceptions thrown.
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Thrown if status code was 403 and not expected.
     * @throws \whikloj\archivematicaPhp\Exceptions\ItemNotFoundException
     *   Thrown if status code was 404 and not expected.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Thrown for any other not expected status codes.
     */
    public static function assertResponseCode(
        ResponseInterface $response,
        int $expected_code,
        string $error_message
    ): void {
        $code = $response->getStatusCode();
        $excep_message = $response->getReasonPhrase();
        if ($code === $expected_code) {
            // Short circuit in-case we expected an error.
        } elseif ($code === 403) {
            throw new AuthorizationException(
                "Invalid credentials or insufficient permissions: $error_message, $excep_message",
                $code
            );
        } elseif ($code === 404) {
            throw new ItemNotFoundException(
                "Item was not located: $error_message, $excep_message",
                $code
            );
        } else {
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
                "$error_message: $excep_message",
                $code
            );
        }
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
        // TODO: Use an enum once PHP 8.0 is the minimum supported version.
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
        // TODO: Use an enum once PHP 8.0 is the minimum supported version.
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
