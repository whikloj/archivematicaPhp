<?php

namespace whikloj\archivematicaPhp;

use GuzzleHttp\Exception\GuzzleException;

/**
 * An ingest implementation
 * @author Jared Whiklo
 * @since 0.0.1
 */
class IngestImpl extends OperationImpl implements Ingest
{
    /**
     * @inheritDoc
     */
    public function listWaiting(): array
    {
        $output = [];
        try {
            $response = $this->am_client->get(
                '/api/ingest/waiting'
            );
            $results = Utils\ArchivmaticaUtils::decodeJsonResponse(
                $response,
                200,
                "Failed to get waiting ingests"
            );
            if (
                array_key_exists('message', $results) &&
                strcasecmp("Fetched units successfully.", $results["message"])
            ) {
                $output = $results["results"];
            }
        } catch (GuzzleException $e) {
            Utils\ArchivmaticaUtils::decodeGuzzleException($e);
        }
        return $output;
    }

    /**
     * @inheritDoc
     */
    public function addMetadata(string $uuid, array $source_paths): string
    {
        $uuid = "";
        try {
            $encoded_paths = $source_paths;
            array_walk($encoded_paths, [
                'whikloj\archivematicaPhp\Utils\ArchivmaticaUtils',
                'base64EncodeValue'
            ]);
            $response = $this->am_client->post(
                "/api/ingest/copy_metadata_files/",
                [
                    "form_params" => [
                        "sip_uuid" => $uuid,
                        "source_paths" => $encoded_paths,
                    ],
                ]
            );
            $body = Utils\ArchivmaticaUtils::decodeJsonResponse(
                $response,
                204,
                "Unable to add metadata to ingest $uuid"
            );
            if (is_array($body) && array_key_exists('message', $body) && $body['message'] == 'Approval successful.') {
                $uuid = $body['reingest_uuid'];
            }
        } catch (GuzzleException $e) {
            Utils\ArchivmaticaUtils::decodeGuzzleException($e);
        }
        return $uuid;
    }

    /**
     * @inheritDoc
     */
    public function completed(): array
    {
        return parent::internalCompleted("ingest");
    }

    /**
     * @inheritDoc
     */
    public function status(string $uuid): array
    {
        return parent::internalStatus($uuid, "ingest");
    }

    /**
     * @inheritDoc
     */
    public function delete(string $uuid): void
    {
        parent::internalDelete($uuid, "ingest");
    }
}
