<?php

namespace whikloj\archivematicaPhp\Storage;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use whikloj\archivematicaPhp\Utils\ArchivmaticaUtils;

/**
 * Implementation of the StorageService.
 * @author Jared Whiklo
 * @since 0.0.1
 */
class StorageServiceImpl implements StorageService
{
    /**
     * @var Client The guzzle client
     */
    private $client;

    /**
     * @var Pipeline A pipeline instance.
     */
    private $pipeline = null;

    /**
     * Basic constructor.
     *
     * @param Client $client
     *   A Guzzle client setup with the base url for the Archivematica host.
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @inheritDoc
     */
    public function getPipeline(): Pipeline
    {
        if (is_null($this->pipeline)) {
            $this->pipeline = new PipelineImpl($this->client);
        }
        return $this->pipeline;
    }

    /**
     * {@inheritDoc}
     */
    public function listEntities(
        string $transfer_source,
        string $transfer_path
    ): array {
        $body = [];
        try {
            $url = "/api/v2/location/$transfer_source/browse/";
            $response = $this->client->get($url);
            $body = ArchivmaticaUtils::checkResponse(
                $response,
                200,
                "Problem with listEntities request"
            );
        } catch (GuzzleException $e) {
            ArchivmaticaUtils::decodeGuzzleException($e);
        }
        return $body;
    }
}
