<?php

namespace whikloj\archivematicaPhp\Storage;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use whikloj\archivematicaPhp\ArchivematicaImpl;
use whikloj\archivematicaPhp\Exceptions\Storage\InvalidLocationPurposeException;
use whikloj\archivematicaPhp\Utils\ArchivmaticaUtils;

class LocationImpl implements Location
{
    private const PURPOSE_CODES = [
        "AR" => "AIP_RECOVERY",
        "AS" => "AIP_STORAGE",
        "CP" => "CURRENTLY_PROCESSING",
        "DS" => "DIP_STORAGE",
        "SD" => "SWORD_DEPOSIT",
        "SS" => "STORAGE_SERVICE_INTERNAL",
        "BL" => "BACKLOG",
        "TS" => "TRANSFER_SOURCE",
        "RP" => "REPLICATOR",
    ];

    /**
     * @var \GuzzleHttp\Client The Archivematica client.
     */
    private $am_client;

    /**
     * @var \GuzzleHttp\Client The Storage service client.
     */
    private $ss_client;

    public function __construct(Client $archive, Client $storage)
    {
        $this->am_client = $archive;
        $this->ss_client = $storage;
    }

    /**
     * {@inheritDoc}
     */
    public function getAll(): array
    {
        $output = [];
        try {
            $response = $this->am_client->get(
                '/api/v2/location/'
            );
            $output = ArchivmaticaUtils::checkResponse(
                $response,
                200,
                "Failed to get all locations"
            );
        } catch (GuzzleException $e) {
            ArchivmaticaUtils::decodeGuzzleException($e);
        }
        return $output;
    }

    /**
     * {@inheritDoc}
     */
    public function create(
        string $description,
        string $pipeline_uri,
        string $space_uri,
        bool $default,
        string $purpose,
        string $relative_path
    ): array {
        $output = [];
        $purpose = $this->validatePurpose($purpose);
        $payload = [
            'description' => $description,
            'pipeline' => $pipeline_uri,
            'space' => $space_uri,
            'default' => $default,
            'purpose' => $purpose,
            'relative_path' => $relative_path,
        ];
        try {
            $response = $this->ss_client->post(
                '/api/v2/location/',
                [
                    'json' => $payload,
                ]
            );
            $output = ArchivmaticaUtils::checkResponse(
                $response,
                201,
                "Failure to create new location"
            );
        } catch (GuzzleException $e) {
            ArchivmaticaUtils::decodeGuzzleException($e);
        }
        return $output;
    }

    /**
     * {@inheritDoc}
     */
    public function getDetails(string $uuid): array
    {
        $details = [];
        try {
            $response = $this->am_client->get(
                "/api/v2/location/$uuid/"
            );
            $details = ArchivmaticaUtils::checkResponse(
                $response,
                200,
                "Failed to get location details ($uuid)"
            );
        } catch (GuzzleException $e) {
            ArchivmaticaUtils::decodeGuzzleException($e);
        }
        return $details;
    }

    /**
     * {@inheritDoc}
     */
    public function moveTo(
        string $uuid,
        string $origin_location,
        string $pipeline,
        array $files
    ): void {
        try {
            $response = $this->ss_client->post(
                "/api/v2/location/$uuid/",
                [
                    'json' => [
                        'origin_location' => $origin_location,
                        'pipeline' => $pipeline,
                        'files' => $files
                    ],
                ]
            );
            ArchivmaticaUtils::checkResponse(
                $response,
                204,
                "Failure to move files from $origin_location to $uuid on pipeline $pipeline"
            );
        } catch (GuzzleException $e) {
            ArchivmaticaUtils::decodeGuzzleException($e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function browsePath(
        string $uuid,
        string $path = ""
    ): array {
        $files = [];
        $data = [];
        if (!empty($path)) {
            $data['path'] = base64_encode($path);
        }
        try {
            $response = $this->ss_client->get(
                "/api/v2/location/$uuid/browse/",
                [
                    'query' => $data,
                ]
            );
            $files = ArchivmaticaUtils::checkResponse(
                $response,
                200,
                "Failed to browse location ($uuid)"
            );
        } catch (GuzzleException $e) {
            ArchivmaticaUtils::decodeGuzzleException($e);
        }
        // Directories and entries are base64 encoded.
        $decode = function (&$o) {
            $o = base64_decode($o);
        };
        array_walk(
            $files['directories'],
            [
                'whikloj\archivematicaPhp\Utils\ArchivmaticaUtils',
                'base64DecodeValue'
            ]
        );
        array_walk(
            $files['entries'],
            [
                'whikloj\archivematicaPhp\Utils\ArchivmaticaUtils',
                'base64DecodeValue'
            ]
        );
        // The keys for properties are also base64 encoded.
        $new_props = [];
        array_walk($files['properties'], function ($o, $k) use (&$new_props) {
            $new_props[base64_decode($k)] = $o;
        });
        $files["properties"] = $new_props;
        return $files;
    }

    /**
     * Ensure a purpose is valid.
     * @param string $purpose
     *   The desired purpose code.
     * @return string
     *   The purpose code normalized to uppercase.
     * @throws \whikloj\archivematicaPhp\Exceptions\Storage\InvalidLocationPurposeException
     *   If the code is not valid.
     */
    private function validatePurpose(string $purpose): string
    {
        if (!array_key_exists(strtoupper($purpose), self::PURPOSE_CODES)) {
            throw new InvalidLocationPurposeException("");
        }
        return strtoupper($purpose);
    }
}
