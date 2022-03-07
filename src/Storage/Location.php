<?php

namespace whikloj\archivematicaPhp\Storage;

/**
 * Operations on/around a location.
 * @author Jared Whiklo
 * @since 0.0.1
 */
interface Location
{
    /**
     * Return the current locations.
     * @return array
     *   Array with keys "meta" and "objects"
     *
     *   "meta" contains array with keys
     *      - "limit": max objects returned
     *      - "next": next offset
     *      - "offset": current offset
     *      - "previous": previous offset
     *      - "total_count": total objects.
     *
     *   "objects" contains an array of arrays with keys
     *      - "description": the location description
     *      - "enabled": is the location enabled (boolean)
     *      - "path": path of the location
     *      - "pipeline": array of pipeline resource uris this location is attached to
     *      - "purpose": the purpose code
     *      - "quota": quota
     *      - "relative_path": the relative path
     *      - "resource_uri": location's resource uri
     *      - "space": space resource uri
     *      - "used": amount used???
     *      - "uuid": location's UUID.
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Permissions issues with request.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Other request issues.
     */
    public function getAll(): array;

    /**
     * Create a new location.
     *
     * @param string $description
     *   The description
     * @param string $pipeline_uri
     *   Resource URI of the pipeline using this location.
     * @param string $space_uri
     *   Resource URI of the space for this location.
     * @param bool $default
     *   If true this location will be the default for its PURPOSE
     * @param string $purpose
     *   Purpose code, one of
     *      - AR (AIP_RECOVERY)
     *      - AS (AIP_STORAGE)
     *      - CP (CURRENTLY_PROCESSING)
     *      - DS (DIP_STORAGE)
     *      - SD (SWORD_DEPOSIT)
     *      - SS (STORAGE_SERVICE_INTERNAL)
     *      - BL (BACKLOG)
     *      - TS (TRANSFER_SOURCE)
     *      - RP (REPLICATOR)
     * @param string $relative_path
     *   Relative to the space's path.
     * @return array
     *   ??
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Permissions issues with request.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Other request issues.
     * @throws \whikloj\archivematicaPhp\Exceptions\Storage\InvalidLocationPurposeException
     *   If the purpose code provided is not valid.
     * @todo Use enum for $purpose once PHP >= 8.0
     */
    public function create(
        string $description,
        string $pipeline_uri,
        string $space_uri,
        bool $default,
        string $purpose,
        string $relative_path
    ): array;

    /**
     * Get location's details.
     *
     * @param string $uuid
     *   The location's UUID.
     * @return array
     *   Associative array with the following keys
     *      - "description": the location description
     *      - "enabled": is the location enabled (boolean)
     *      - "path": path of the location
     *      - "pipeline": array of pipeline resource uris this location is attached to
     *      - "purpose": the purpose code
     *      - "quota": quota
     *      - "relative_path": the relative path
     *      - "resource_uri": location's resource uri
     *      - "space": space resource uri
     *      - "used": amount used???
     *      - "uuid": location's UUID.
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Permissions issues with request.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Other request issues.
     */
    public function getDetails(string $uuid): array;

    /**
     * Move files between pipeline.
     *
     * @param string $uuid
     *   The new location for the files.
     * @param string $origin_location
     *   The original location of the files.
     * @param string $pipeline
     *   The pipeline which both locations MUST be connected to.
     * @param array $files
     *   Array of associative arrays with keys ('source', 'destination') I THINK.
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Permissions issues with request.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Other request issues
     */
    public function moveTo(
        string $uuid,
        string $origin_location,
        string $pipeline,
        array $files
    ): void;

    /**
     * Get files from a location
     *
     * @param string $uuid
     *   The UUID of the location.
     * @param string $path
     *   Path to restrict entries to.
     * @return array
     *   Array of files.
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Permissions issues with request.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Other request issues
     */
    public function browsePath(string $uuid, string $path = ""): array;
}
