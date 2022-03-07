<?php

namespace whikloj\archivematicaPhp;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use whikloj\archivematicaPhp\Exceptions\RequestException;

abstract class OperationImpl implements Operation
{
    /**
     * @var \GuzzleHttp\Client The Archivematica Guzzle client.
     */
    protected $am_client;

    /**
     * @var \Psr\Log\LoggerInterface A logger.
     */
    protected $logger;

    /**
     * Basic constructor.
     *
     * @param \GuzzleHttp\Client $archivematica
     *   A Guzzle client setup with the base url for the Archivematica host.
     */
    public function __construct(Client $archivematica, LoggerInterface $logger)
    {
        $this->am_client = $archivematica;
        $this->logger = $logger;
    }

    /**
     * Get the completed resources of a type.
     *
     * @param string $type
     *   The type to get, one of "ingest" or "transfer"
     * @return array
     *   UUIDs of completed type (from above)
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Problem with username or API key.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Problem with making the request.
     */
    protected function internalCompleted(string $type): array
    {
        $output = [];
        try {
            $response = $this->am_client->get(
                "/api/v2/$type/completed/"
            );
            $body = Utils\ArchivmaticaUtils::checkResponse(
                $response,
                200,
                "Request for completed {$type}s failed"
            );
            if (
                !is_array($body) || !array_key_exists('message', $body) ||
                strcasecmp(
                    $body['message'],
                    "Fetched completed {$type}s successfully."
                ) != 0
            ) {
                throw new RequestException(
                    "Request for completed {$type}s did not succeed"
                );
            }
            $output = $body['results'];
        } catch (GuzzleException $e) {
            Utils\ArchivmaticaUtils::decodeGuzzleException($e);
        }
        return $output;
    }

    /**
     * Get the status of a resource.
     *
     * @param string $uuid
     *   The UUID of the resource.
     * @param string $type
     *   The type of the resource. One of "ingest" or "transfer"
     * @return array
     *   Associative array of metadata
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Problem with username or API key.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Problem with making the request.
     */
    protected function internalStatus(string $uuid, string $type): array
    {
        $output = [];
        try {
            $url = "/api/v2/$type/status/$uuid/";
            $response = $this->am_client->get(
                $url
            );
            $output = Utils\ArchivmaticaUtils::checkResponse(
                $response,
                200,
                "Request for status failed"
            );
            if (
                !is_array($output) || (
                    !array_key_exists('status', $output) &&
                    !array_key_exists('error', $output)
                )
            ) {
                throw new RequestException("Request for status response is missing 'status' and 'error' keys.");
            }
        } catch (GuzzleException $e) {
            Utils\ArchivmaticaUtils::decodeGuzzleException($e);
        }
        return $output;
    }

    /**
     * @param string $uuid
     *   UUID of the resource to hide (delete).
     * @param string $type
     *   The type of the resource to hide (delete). One of "ingest" or "transfer"
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Problem with username or API key.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Problem with making the request.
     */
    protected function internalDelete(string $uuid, string $type): void
    {
        try {
            $response = $this->am_client->delete(
                "/api/$type/$uuid/delete/"
            );
            $body = Utils\ArchivmaticaUtils::checkResponse(
                $response,
                200,
                "Request to hide $type ({$uuid}) failed"
            );
            if (
                !is_array($body) || !array_key_exists('removed', $body) ||
                $body['removed'] !== true
            ) {
                throw new RequestException("Could not hide $type ({$uuid})");
            }
        } catch (GuzzleException $e) {
            Utils\ArchivmaticaUtils::decodeGuzzleException($e);
        }
    }

    /**
     * @param string $name
     *   Name of the AIP. The AIP should also be found at %sharedDirectory%/tmp/<name>.
     * @param string $uuid
     *   UUID of the AIP to reingest
     * @param string $type
     *   The type of resource to reingest. One of "ingest" or "transfer"
     * @return string
     *   UUID of the re-ingested AIP.
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Problem with username or API key.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Problem with making the request.
     */
    protected function internalReingest(string $name, string $uuid, string $type): string
    {
        $output = "";
        try {
            $response = $this->am_client->post(
                "/api/$type/reingest/",
                [
                    'form_params' => [
                        'name' => $name,
                        'uuid' => $uuid,
                    ],
                ]
            );
            $body = Utils\ArchivmaticaUtils::checkResponse(
                $response,
                201,
                "Re-ingest request ({$uuid}) failed"
            );
            if (
                !is_array($body) || !array_key_exists('message', $body) ||
                strcasecmp($body['message'], 'Approval successful.') != 0
            ) {
                throw new RequestException("Request for re-ingest ({$uuid}) did not succeed:");
            }
            $output = $body['reingest_uuid'];
        } catch (GuzzleException $e) {
            Utils\ArchivmaticaUtils::decodeGuzzleException($e);
        }
        return $output;
    }

    /**
     * Close all operations.
     *
     * @return array
     *   Array with keys "completed", "close_failed" and "close_succeeded" with arrays of UUIDs.
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Problem with username or API key.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Problem with getting the closed operations..
     */
    public function closeCompleted(): array
    {
        $units = $this->completed();
        $results = [
            'completed' => $units,
            'close_failed' => [],
            'close_succeeded' => [],
        ];
        foreach ($units as $uuid) {
            try {
                $this->delete($uuid);
                $results['close_succeeded'][] = $uuid;
            } catch (RequestException $e) {
                $results['close_failed'][] = $uuid;
            }
        }
        return $results;
    }

    /**
     * {@inheritDoc}
     */
    abstract public function completed(): array;

    /**
     * {@inheritDoc}
     */
    abstract public function status(string $uuid): array;

    /**
     * {@inheritDoc}
     */
    abstract public function delete(string $uuid): void;
}
