<?php

namespace whikloj\archivematicaPhp;

/**
 * Generic interface for common actions between transfers and ingests.
 * @author Jared Whiklo
 * @since 0.0.1
 */
interface Operation
{
    /**
     * Get the UUIDs of all completed operations.
     *
     * @return array
     *   Array of UUIDS of completed operations.
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Problem with username or API key.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Problem with making the request.
     * @throws \whikloj\archivematicaPhp\Exceptions\ItemNotFoundException
     *   Endpoint not found (returned a 404).
     */
    public function completed(): array;

    /**
     * Close all completed operations that exist.
     *
     * @return array
     *   Array with keys 'completed', 'close_failed', and 'close_succeeded' pointing at an array of UUIDs.
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Problem with username or API key.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Problem with making the request to get completed operations.
     * @throws \whikloj\archivematicaPhp\Exceptions\ItemNotFoundException
     *   Endpoint not found (returned a 404).
     */
    public function closeCompleted(): array;

    /**
     * Get the status of an operation .
     *
     * @param string $uuid
     *   The UUID of the operation.
     * @return array
     *   Array with keys
     *    - status -> one of FAILED, REJECTED, USER_INPUT, COMPLETE or PROCESSING
     *    - name -> Name of transfer
     *    - microservice -> Name of the current microservice
     *    - path -> Full path to the transfer
     *    - message -> A text message
     *    - type -> TRANSFER
     *    - uuid -> uuid of the transfer
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Problem with username or API key.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Problem with making the request.
     * @throws \whikloj\archivematicaPhp\Exceptions\ItemNotFoundException
     *   Operation not found.
     */
    public function status(string $uuid): array;

    /**
     * Remove (hide) an operation
     *
     * @param string $uuid
     *   The UUID of the operation.
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Problem with username or API key.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Problem with making the request.
     * @throws \whikloj\archivematicaPhp\Exceptions\ItemNotFoundException
     *   Operation not found.
     */
    public function delete(string $uuid): void;
}
