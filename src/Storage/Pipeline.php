<?php

namespace whikloj\archivematicaPhp\Storage;

/**
 * Operations on/around pipelines
 * @author Jared Whiklo
 * @since 0.0.1
 */
interface Pipeline
{
    /**
     * Get all pipelines
     *
     * @return array
     *   Array with keys meta (metadata) and objects,
     *   objects are arrays with keys
     *     - description
     *     - remote_name
     *     - resource_uri
     *     - uuid
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Problem with username or API key.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Problem with making the request.
     */
    public function getAll(): array;

    /**
     * Get all pipelines filtered by description.
     *
     * @param string $description
     *   The description to match on
     * @param bool $exact
     *   Whether to exact match or starts with
     * @return array
     *   Array with keys meta (metadata) and objects,
     *   objects are arrays with keys
     *     - description
     *     - remote_name
     *     - resource_uri
     *     - uuid
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Problem with username or API key.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Problem with making the request.
     */
    public function getByDescription(string $description, bool $exact): array;

    /**
     * Get all pipelines filtered by the UUID.
     *
     * @param string $uuid
     *   The UUID to match on
     * @return array
     *   Array with keys meta (metadata) and objects,
     *   objects are arrays with keys
     *     - description
     *     - remote_name
     *     - resource_uri
     *     - uuid
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Problem with username or API key.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Problem with making the request.
     */
    public function getByUuid(string $uuid): array;

    /**
     * Create a new pipeline.
     *
     * @param string $uuid
     *   The UUID to use.
     * @param string $description
     *   The description to use.
     * @param string $api_key
     *   The API key associated with below user
     * @param string $api_user
     *   The username of admin authorized to write to storage location.
     * @param bool $create_default_locations
     *   If true will associate default locations with new pipeline.
     * @param string $shared_path
     *   If default locations are created, create the processing location at this path in the local filesystem.
     * @param string $remote_name
     *   URI of the pipeline, if not provided the storage service will try to determine using REMOTE_ADDR header.
     * @return array
     *   With metadata of new pipeline, contains keys.
     *     - create_default_locations
     *     - description
     *     - remote_name
     *     - resource_uri
     *     - uuid
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Problem with username or API key.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Problem with making the request.
     */
    public function create(
        string $uuid,
        string $description,
        string $api_key,
        string $api_user,
        bool $create_default_locations,
        string $shared_path,
        string $remote_name
    ): array;
}
