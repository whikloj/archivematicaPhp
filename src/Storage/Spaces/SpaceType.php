<?php

namespace whikloj\archivematicaPhp\Storage\Spaces;

use GuzzleHttp\Client;

/**
 * Generic interface for creating/interacting with various Archivematica space types.
 * @author Jared Whiklo
 */
interface SpaceType
{
    /**
     * Create a new space.
     *
     * @param \GuzzleHttp\Client $client
     *   A Guzzle client to perform the request.
     * @param array $fields
     *   Array of required fields for this space type, field names are keys.
     * @return array
     *   Metadata of the newly created space.
     * @throws \whikloj\archivematicaPhp\Exceptions\Storage\SpaceTypeException
     *   Incorrect configuration of space type or missing fields.
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Permissions issues with request.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Other issues with the request.
     */
    public function create(Client $client, array $fields): array;
}
