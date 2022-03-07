<?php

namespace whikloj\archivematicaPhp;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use whikloj\archivematicaPhp\Exceptions\RequestException;
use whikloj\archivematicaPhp\Utils\ArchivmaticaUtils;

/**
 * Implementation of Package
 * @author Jared Whiklo
 * @since 0.0.1
 */
class PackageImpl implements Package
{
    /**
     * @var \GuzzleHttp\Client The Archivematica client.
     */
    private $am_client;

    /**
     * @var \GuzzleHttp\Client The Storage Service client.
     */
    private $ss_client;

    public function __construct(Client $archivematica, Client $storage)
    {
        $this->am_client = $archivematica;
        $this->ss_client = $storage;
    }

    /**
     * @inheritDoc
     */
    public function getAll(): array
    {
        return $this->internalGet(null);
    }

    /**
     * {@inheritDoc}
     */
    public function getAllAips(): array
    {
        return $this->internalGet("AIP");
    }

    /**
     * {@inheritDoc}
     */
    public function getAllDips(): array
    {
        return $this->internalGet("DIP");
    }

    /**
     * Handles internal get request
     *
     * @param string|null $type
     *   The package type or null to get all types
     * @param array $objects
     *   Context array with keys
     *      - "objects": Listing of objects.
     *      - "next": Next offset or null if we are done.
     *      - "total_count": Total number of objects.
     * @return array
     *   The metadata and objects, same for as getAll()
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Permissions issues with request.
     * @throws \InvalidArgumentException
     *   Invalid re-ingest type received.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Other request issues.
     * @see \whikloj\archivematicaPhp\PackageImpl::getAll()
     * @todo Use enum for $type once PHP >= 8.0
     */
    private function internalGet(?string $type, array $objects = []): array
    {
        $output = [];
        $params = [];
        if (count($objects) != 0) {
            $params["offset"] = $objects["next"];
        } else {
            $objects["objects"] = [];
        }
        if (!is_null($type)) {
            $type = strtoupper($type);
            if ($type != "AIP" && $type != "DIP") {
                throw new \InvalidArgumentException(
                    "Optional package type was $type, must be AIP or DIP if provided."
                );
            }
            $params["package_type"] = $type;
        }
        try {
            $response = $this->ss_client->get(
                '/api/v2/file/',
                [
                    "query" => $params,
                ]
            );
            $data = ArchivmaticaUtils::checkResponse($response, 200, "Unable to get all package details");
            $output = self::processListing($data);
            $output["objects"] = array_merge($objects["objects"], $output["objects"]);
        } catch (GuzzleException $e) {
            ArchivmaticaUtils::decodeGuzzleException($e);
        }
        if (!is_null($output["next"])) {
            $output = $this->internalGet($type, $output);
        }
        return $output;
    }

    private static function processListing(array $incoming): array
    {
        $output = [];
        $output["total_count"] = (int) $incoming["meta"]["total_count"];
        $output["next"] = (!is_null($incoming["meta"]["next"]) ? $incoming["meta"]["offset"] + 1 : null);
        $output["objects"] = $incoming["objects"];
        return $output;
    }

    /**
     * @inheritDoc
     */
    public function createFromExisting(
        string $new_uuid,
        string $old_uuid,
        string $new_location_uri,
        string $new_relative_path,
        string $type,
        string $related_package
    ): array {
        ArchivmaticaUtils::isValidPackageType($type);
        if ($new_uuid == $old_uuid) {
            throw new \InvalidArgumentException("New package UUID cannot match old UUID.");
        }
        // Creating a package actually needs more things, but those can be retrieved from the package details.
        $package_details = $this->details($old_uuid);
        $params = [
            "uuid" => $new_uuid,
            "origin_location" => $package_details["current_location"],
            "origin_path" => $package_details["current_path"],
            "origin_pipeline" => $package_details["origin_pipeline"],
            "size" => $package_details["size"],
            "package_type" => $type,
            "current_location" => ArchivmaticaUtils::asUri($new_location_uri, "location"),
            "current_path" => $new_relative_path,
        ];
        $output = [];
        try {
            $response = $this->ss_client->post(
                "/api/v2/file/",
                [
                    "json" => $params,
                ]
            );
            $output = ArchivmaticaUtils::checkResponse(
                $response,
                201,
                "Unable to create new package from existing package ($old_uuid)"
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
            $response = $this->ss_client->get(
                "/api/v2/file/$uuid/"
            );
            $body = ArchivmaticaUtils::checkResponse(
                $response,
                200,
                "Unable to GET details of package $uuid"
            );
        } catch (GuzzleException $e) {
            ArchivmaticaUtils::decodeGuzzleException($e);
        }
        return $body;
    }

    /**
     * {@inheritDoc}
     */
    public function reingest(
        string $uuid,
        string $pipeline_uuid,
        string $type = "FULL",
        string $processing_config = "default"
    ): string {
        $type = strtoupper($type);
        if ($type !== "FULL" && $type !== "OBJECTS" && $type !== "METADATA_ONLY") {
            throw new \InvalidArgumentException(
                "Reingest type was $type, must be one of FULL, OBJECTS or METADATA_ONLY"
            );
        }
        $reingest_uuid = "";
        try {
            $url = "/api/v2/file/$uuid/reingest/";
            $response = $this->ss_client->post(
                $url,
                [
                    "json" => [
                        "pipeline" => $pipeline_uuid,
                        "reingest_type" => $type,
                        "processing_config" => $processing_config,
                    ]
                ]
            );
            $body = ArchivmaticaUtils::checkResponse($response, 202, "Unable to initiate reingest of AIP $uuid");
            $reingest_uuid = $body['reingest_uuid'];
        } catch (GuzzleException $e) {
            ArchivmaticaUtils::decodeGuzzleException($e);
        }
        return $reingest_uuid;
    }
}
