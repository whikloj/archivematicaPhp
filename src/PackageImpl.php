<?php

namespace whikloj\archivematicaPhp;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use whikloj\archivematicaPhp\Exceptions\FilesystemException;
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
            $data = ArchivmaticaUtils::decodeJsonResponse($response, 200, "Unable to get all package details");
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
            $output = ArchivmaticaUtils::decodeJsonResponse(
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
            $body = ArchivmaticaUtils::decodeJsonResponse(
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
        // TODO: Use an enum when PHP 8.0 is our minimum version.
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
            $body = ArchivmaticaUtils::decodeJsonResponse($response, 202, "Unable to initiate reingest of AIP $uuid");
            $reingest_uuid = $body['reingest_uuid'];
        } catch (GuzzleException $e) {
            ArchivmaticaUtils::decodeGuzzleException($e);
        }
        return $reingest_uuid;
    }

    /**
     * {@inheritDoc}
     */
    public function getAips2Dips(): array
    {
        $dips = $this->getAllDips();
        $aips = $this->getAllAips();
        $mapping = [];
        foreach ($aips["objects"] as $aip) {
            $uuid = $aip["uuid"];
            $mapping[$uuid] = [];
            $left_over_dips = [];
            foreach ($dips["objects"] as $dip) {
                if (substr($dip["current_path"], strlen($uuid) * -1) == $uuid) {
                    $mapping[$uuid][] = $dip["uuid"];
                } else {
                    // Make a dips that we haven't matched to avoid iterating over matched ones more than once.
                    $left_over_dips[] = $dip;
                }
            }
            $dips["objects"] = $left_over_dips;
        }
        return $mapping;
    }

    /**
     * {@inheritDoc}
     */
    public function getDipsForAip(string $uuid, bool $uuid_only = false): array
    {
        $dips = $this->getAllDips();
        if ($dips["total_count"] == 0) {
            return [];
        }
        $dips = $dips["objects"];
        // Array values regenerates the array indicies or you can't use $dips[0] to get the first element
        $dips = array_values(array_filter($dips, function ($o) use ($uuid) {
            return substr($o["current_path"], strlen($uuid) * -1) == $uuid;
        }));
        if (count($dips) > 0 && $uuid_only) {
            $dips = array_map(function ($o) {
                return $o["uuid"];
            }, $dips);
        }
        return $dips;
    }

    /**
     * {@inheritDoc}
     */
    public function download(string $uuid, string $directory): string
    {
        $full_path = "";
        try {
            if (!file_exists($directory) || !is_dir($directory) || !is_writeable($directory)) {
                throw new FilesystemException(
                    "File $directory does not exist, is not a directory or is not writeable."
                );
            }
            $response = $this->ss_client->GET(
                "/api/v2/file/$uuid/download/",
                ["stream" => true,]
            );
            ArchivmaticaUtils::assertResponseCode(
                $response,
                200,
                "Unable to download package $uuid"
            );
            $filename = "package-$uuid.7z"; // Assumes packages are all 7z archives.
            if ($response->hasHeader("Content-Disposition")) {
                $disposition = $response->getHeader("Content-Disposition")[0];
                if (preg_match('/filename="([^"]+)"/', $disposition, $matches)) {
                    $filename = $matches[1];
                }
            } elseif ($response->hasHeader("Content-Type")) {
                $type = $response->getHeader("Content-Type")[0];
                $extension = self::contentTypeToExtension($type);
                $filename = "package-$uuid.$extension";
            }
            $full_path = $directory . DIRECTORY_SEPARATOR . $filename;
            $fp = fopen($full_path, "wb+");
            $body = $response->getBody();
            while (!$body->eof()) {
                fwrite($fp, $body->read(4096));
            }
            $body->close();
            fclose($fp);
        } catch (GuzzleException $e) {
            ArchivmaticaUtils::decodeGuzzleException($e);
        }
        return $full_path;
    }

    /**
     * {@inheritDoc}
     */
    public function delete(
        string $uuid,
        string $pipeline_uuid,
        string $reason,
        int $user_id,
        string $user_email
    ): int {
        if ($user_id < 1) {
            throw new \InvalidArgumentException("User ID must be an integer greater than 0.");
        }
        $delete_id = 0;
        try {
            $json = [
                "pipeline" => $pipeline_uuid,
                "event_reason" => $reason,
                "user_id" => $user_id,
                "user_email" => $user_email,
            ];
            $response = $this->ss_client->post(
                "/api/v2/file/$uuid/delete_aip/",
                [
                    "json" => $json,
                ]
            );
            $body = ArchivmaticaUtils::decodeJsonResponse($response, 202, "Unable to delete package $uuid");
            $delete_id = (int) $body["id"];
        } catch (GuzzleException $e) {
            ArchivmaticaUtils::decodeGuzzleException($e);
        }
        return $delete_id;
    }

    /**
     * Try to determine the package extension from the content-type.
     * @param string $content_type
     *   The content type
     * @return string
     *   The extension or "7z" by default
     */
    private static function contentTypeToExtension(string $content_type): string
    {
        if (strcasecmp("application/x-bzip2", $content_type) === 0) {
            return "bz2";
        } elseif (strcasecmp("application/x-gzip-compressed", $content_type) === 0) {
            return "gz";
        } elseif (strcasecmp("application/x-tar", $content_type) === 0) {
            return "tar";
        } elseif (preg_match("/^application\/x\-([^\-]+)\-compressed$", $content_type, $matches)) {
            return $matches[1];
        }
        return "7z";
    }
}
