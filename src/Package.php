<?php

namespace whikloj\archivematicaPhp;

/**
 * Interface of package operations.
 * @author Jared Whiklo
 * @since 0.0.1
 */
interface Package
{
    /**
     * Returns metadata
     *
     * @return array
     *   "total_count": total objects.
     *   "next": next offset number or null.
     *   "objects" contains an array of arrays with keys
     *      - "current_full_path": the package's path
     *      - "current_location": Location URI
     *      - "current_path": Path relative to location?
     *      - "encrypted": encrypted package (boolean)
     *      - "misc_attributes": miscellaneous attributes
     *          - "reingest_pipeline": Pipeline this was reingested onto
     *      - "origin_pipeline": Original pipeline URI
     *      - "package_type":  "AIP", "AIC", "DIP", "transfer", "SIP", "file", "deposit"
     *      - "related_packages": []??
     *      - "replicas": []??,
     *      - "replicated_package": null??,
     *      - "resource_uri": Package URI
     *      - "size": Package size in bytes
     *      - "status": "UPLOADED", ???
     *      - "uuid": UUID of the package
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Permissions issues with request.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Other request issues.
     */
    public function getAll(): array;

    /**
     * Get all packages of a AIP type.
     *
     * @return array
     *   "total_count": total objects.
     *   "next": next offset number or null.
     *   "objects" contains an array of arrays with keys
     *      - "current_full_path": the package's path
     *      - "current_location": Location URI
     *      - "current_path": Path relative to location?
     *      - "encrypted": encrypted package (boolean)
     *      - "misc_attributes": miscellaneous attributes
     *          - "reingest_pipeline": Pipeline this was reingested onto
     *      - "origin_pipeline": Original pipeline URI
     *      - "package_type":  "AIP", "AIC", "DIP", "transfer", "SIP", "file", "deposit"
     *      - "related_packages": []??
     *      - "replicas": []??,
     *      - "replicated_package": null??,
     *      - "resource_uri": Package URI
     *      - "size": Package size in bytes
     *      - "status": "UPLOADED", ???
     *      - "uuid": UUID of the package
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Permissions issues with request.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Other request issues.
     */
    public function getAllAips(): array;

    /**
     * Get all packages of a DIP type.
     *
     * @return array
     *   "total_count": total objects.
     *   "next": next offset number or null.
     *   "objects" contains an array of arrays with keys
     *      - "current_full_path": the package's path
     *      - "current_location": Location URI
     *      - "current_path": Path relative to location?
     *      - "encrypted": encrypted package (boolean)
     *      - "misc_attributes": miscellaneous attributes
     *          - "reingest_pipeline": Pipeline this was reingested onto
     *      - "origin_pipeline": Original pipeline URI
     *      - "package_type":  "AIP", "AIC", "DIP", "transfer", "SIP", "file", "deposit"
     *      - "related_packages": []??
     *      - "replicas": []??,
     *      - "replicated_package": null??,
     *      - "resource_uri": Package URI
     *      - "size": Package size in bytes
     *      - "status": "UPLOADED", ???
     *      - "uuid": UUID of the package
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Permissions issues with request.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Other request issues.
     */
    public function getAllDips(): array;

    /**
     * Create a package from an existing one.
     *
     * @param string $new_uuid
     *   UUID of the new package, leave empty to auto-generate one.
     * @param string $old_uuid
     *   UUID of the existing package.
     * @param string $new_location_uri
     *   URI of the location the package should be stored at.
     * @param string $new_relative_path
     *   New path for the package relative to the $new_location_uri
     * @param string $type
     *   Type of package to create. One of: AIP, AIC, DIP, transfer, SIP, file, deposit.
     * @param string $related_package
     *   UUID of a package that is related to this one. E.g. UUID of a DIP when storing an AIP
     * @return array
     *   Package details.
     * @throws \InvalidArgumentException
     *   If an invalid package type is provided or new UUID matches old package UUID.
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Permissions issues with request.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Other request issues.
     */
    public function createFromExisting(
        string $new_uuid,
        string $old_uuid,
        string $new_location_uri,
        string $new_relative_path,
        string $type,
        string $related_package
    ): array;

    /**
     * Retrieve the package details.
     *
     * @param string $uuid
     *   The UUID of the package.
     * @return array
     *   Array of details, see "objects" details of getAll() method.
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Permissions issues with request.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Other request issues.
     * @see \whikloj\archivematicaPhp\Package::getAll()
     */
    public function details(string $uuid): array;

    /**
     * Re-ingest an AIP package.
     *
     * @param string $uuid
     *   The AIP UUID.
     * @param string $pipeline_uuid
     *   The pipeline uuid.
     * @param string $type
     *   The type of reingest. One of
     *    - METADATA_ONLY (metadata only re-ingest)
     *    - OBJECTS (partial re-ingest)
     *    - FULL (full re-ingest) (default)
     * @param string $processing_config
     *   The processing config to use, default to 'default'
     * @return string
     *   The reingest UUID.
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Permissions issues with request.
     * @throws \InvalidArgumentException
     *   Invalid re-ingest type received.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Other request issues.
     */
    public function reingest(
        string $uuid,
        string $pipeline_uuid,
        string $type = "FULL",
        string $processing_config = "default"
    ): string;

    /**
     * Get a mapping of AIP UUID to DIP UUIDs
     *
     * @return array
     *   Associative array where keys are AIP UUID and values are array of DIP UUIDs.
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Permissions issues with request.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Other request issues
     */
    public function getAips2Dips(): array;

    /**
     * Get an array of DIPs for an AIP.
     *
     * @param string $uuid
     *   The AIP UUID.
     * @param bool $uuid_only
     *   Whether to return an array of DIP UUIDs instead of full detailed objects.
     * @return array
     *   Array of DIPs.
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Permissions issues with request.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Other request issues
     */
    public function getDipsForAip(string $uuid, bool $uuid_only = false): array;

    /**
     * Download a package to the directory.
     *
     * @param string $uuid
     *   The AIP or DIP UUID.
     * @param string $directory
     *   The directory to download too.
     * @return string
     *   The path to the downloaded file.
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Permissions issues with request.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Other request issues
     * @throws \whikloj\archivematicaPhp\Exceptions\FilesystemException
     *   Problems writing the package to the directory.
     */
    public function download(string $uuid, string $directory): string;

    /**
     * Delete a package
     *
     * @param string $uuid
     *   The UUID of the package to delete.
     * @param string $pipeline_uuid
     *   The UUID of the pipeline the package is on.
     * @param string $reason
     *   The reason to delete the package.
     * @param int $user_id
     *   The id of the user requesting the package deletion. This is the user on the pipeline specified.
     * @param string $user_email
     *   The email of the user requesting the package deletion.
     * @return int
     *   The delete package request id.
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Permissions issues with request.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Other request issues
     * @throws \InvalidArgumentException
     *   User ID is not greater than 0
     */
    public function delete(string $uuid, string $pipeline_uuid, string $reason, int $user_id, string $user_email): int;
}
