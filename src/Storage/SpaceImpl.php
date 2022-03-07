<?php

namespace whikloj\archivematicaPhp\Storage;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use whikloj\archivematicaPhp\DjangoFilter;
use whikloj\archivematicaPhp\Exceptions\Storage\InvalidSpaceTypeException;
use whikloj\archivematicaPhp\Utils\ArchivmaticaUtils;

/**
 * Implementation of a space object.
 * @author Jared Whiklo
 * @since 0.0.1
 */
class SpaceImpl implements Space
{
    /**
     * @var \GuzzleHttp\Client The client for requests.
     */
    private $client;

    /**
     * Basic constructor.
     *
     * @param \GuzzleHttp\Client $client
     *   The guzzle client.
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @inheritDoc
     */
    public function getAll(): array
    {
        return $this->internalGet([]);
    }

    /**
     * @inheritDoc
     */
    public function getAllFilter(DjangoFilter $filter): array
    {
        return $this->internalGet([$filter]);
    }

    /**
     * @inheritDoc
     */
    public function getAllFilters(array $filters): array
    {
        return $this->internalGet($filters);
    }

    /**
     * Internal logic for GETting spaces.
     *
     * @param array $filters
     *   Array of filters to apply
     * @return array
     *   Metadata and arrays of objects.
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Raised on permissions issues.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Raised for other request failures.
     */
    private function internalGet(array $filters): array
    {
        $output = [];
        $filter_params = [];
        foreach ($filters as $f) {
            $filter_params[$f->getField()] = $f->getValue();
        }
        try {
            $response = $this->client->get(
                '/api/v2/space/',
                [
                    'query' => $filter_params
                ]
            );
            $output = ArchivmaticaUtils::checkResponse(
                $response,
                200,
                "Failed to get spaces"
            );
        } catch (GuzzleException $e) {
            ArchivmaticaUtils::decodeGuzzleException($e);
        }
        return $output;
    }

    /**
     * {@inheritDoc}
     */
    public function details(string $uuid): array
    {
        $body = [];
        try {
            $response = $this->client->get(
                "/api/v2/space/$uuid/",
            );
            $body = ArchivmaticaUtils::checkResponse(
                $response,
                200,
                "Failed to get details about space ({$uuid})"
            );
        } catch (GuzzleException $e) {
            ArchivmaticaUtils::decodeGuzzleException($e);
        }
        return $body;
    }

    /**
     * {@inheritDoc}
     */
    public function browse(string $uuid, string $path): array
    {
        $query_params = [];
        $body = [];
        if (!empty($path)) {
            $query_params['path'] = $path;
        }
        try {
            $response = $this->client->get(
                "/api/v2/space/{$uuid}/browse/",
                [
                    'query' => $query_params,
                ]
            );
            $body = ArchivmaticaUtils::checkResponse(
                $response,
                200,
                "Request to browse space ({$uuid}) failed."
            );
        } catch (GuzzleException $e) {
            ArchivmaticaUtils::decodeGuzzleException($e);
        }
        return $body;
    }

    /**
     * {@inheritDoc}
     */
    public function create(string $type, array $fields): array
    {
        if (file_exists(__DIR__ . "/Spaces/$type.php")) {
            $class = "\\whikloj\\archivematicaPhp\\Storage\\Spaces\\$type";
            $space = new $class();
            return $space->create($this->client, $fields);
        }
        throw new InvalidSpaceTypeException("There is no space type called $type");
    }
}
