<?php

namespace whikloj\archivematicaPhp\Storage;

/**
 * This might be useless. It appears in the API docs to have a single method
 * which is the same as browsing a location.
 */
interface StorageService
{
    /**
     * Get a Pipeline instance.
     *
     * @return \whikloj\archivematicaPhp\Storage\Pipeline
     */
    public function getPipeline(): Pipeline;

    /**
     * List entities in a transfer source.
     *
     * @param string $transfer_source
     *   The transfer source.
     * @param string $transfer_path
     *   A transfer path to filter by (optional)
     * @return array
     *   Associative array of entities
     *
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Permissions problems
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Problem with the request.
     */
    public function listEntities(string $transfer_source, string $transfer_path): array;
}
