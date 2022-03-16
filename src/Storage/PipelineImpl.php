<?php

namespace whikloj\archivematicaPhp\Storage;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use whikloj\archivematicaPhp\DjangoFilter;
use whikloj\archivematicaPhp\Utils\ArchivmaticaUtils;

/**
 * Implementation of the Pipeline
 * @author Jared Whiklo
 * @since 0.0.1
 */
class PipelineImpl implements Pipeline
{
    /**
     * @var Client The guzzle client
     */
    private $client;

    /**
     * Basic constructor.
     *
     * @param Client $client
     *   A Guzzle client setup with the base url for the Storage Service host.
     */
    public function __construct(
        Client $client
    ) {
        $this->client = $client;
    }

    /**
     * @inheritDoc
     */
    public function getAll(): array
    {
        return $this->get("Request to get all pipelines failed.");
    }

    /**
     * @inheritDoc
     */
    public function getByDescription(string $description, bool $exact): array
    {
        $filter = DjangoFilter::create("description", $description);
        if (!$exact) {
            $filter->startsWith();
        }
        return $this->get("Request to get pipeline by description ($description) failed", $filter);
    }

    /**
     * @inheritDoc
     */
    public function getByUuid(string $uuid): array
    {
        //$filter = "uuid=" . urlencode($uuid);
        //return $this->get($filter, "Request to get pipeline by uuid ({$uuid}) failed");
        $body = [];
        try {
            $response = $this->client->get(
                "/api/v2/pipeline/$uuid/"
            );
            $body = ArchivmaticaUtils::decodeJsonResponse(
                $response,
                200,
                "Failed to get Pipeline by UUID ($uuid)"
            );
        } catch (GuzzleException $e) {
            ArchivmaticaUtils::decodeGuzzleException($e);
        }
        return $body;
    }

    /**
     * Utility to do heavy lifting of list pipelines.
     *
     * @param string $message
     *  A message to use for exceptions (if necessary)
     * @param DjangoFilter|null $filter
     *   A filter object, ie
     *      - uuid=12345678-1234...
     *      - description=Archivematica+Pipeline
     *      - description_startsWith=Archivematica
     * @return array
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   On 403 Forbidden response
     * @throws \whikloj\archivematicaPhp\Exceptions\ItemNotFoundException
     *   On 404 Not Found response
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   On other client exceptions.
     * @see Pipeline::getAll()
     */
    private function get(string $message, DjangoFilter $filter = null): array
    {
        $output = [];
        $filter_array = [];
        if (!is_null($filter)) {
            $filter_array[$filter->getField()] = $filter->getValue();
        }
        try {
            $response = $this->client->get(
                '/api/v2/pipeline/',
                [
                    'query' => $filter_array,
                ]
            );
            $output = ArchivmaticaUtils::decodeJsonResponse(
                $response,
                200,
                $message
            );
        } catch (GuzzleException $e) {
            ArchivmaticaUtils::decodeGuzzleException($e);
        }
        return $output;
    }

    /**
     * @inheritDoc
     */
    public function create(
        string $uuid,
        string $description,
        string $api_key,
        string $api_user,
        bool $create_default_locations,
        string $shared_path,
        string $remote_name
    ): array {
        $body = [];
        try {
            $body_data = [
                "uuid" => $uuid,
                "description" => $description,
                "api_username" => $api_user,
                "api_key" => $api_key,
                "create_default_locations" => $create_default_locations,
            ];
            if ($create_default_locations && !empty($shared_path)) {
                $body_data['shared_path'] = $shared_path;
            }
            if (!empty($remote_name)) {
                $body_data['remote_name'] = $remote_name;
            }
            $response = $this->client->post(
                '/api/v2/pipeline/',
                [
                    'json' => $body_data,
                ]
            );
            $body = ArchivmaticaUtils::decodeJsonResponse(
                $response,
                201,
                "Failed to create new pipeline with UUID ($uuid)"
            );
        } catch (GuzzleException $e) {
            ArchivmaticaUtils::decodeGuzzleException($e);
        }
        return $body;
    }
}
