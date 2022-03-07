<?php

namespace whikloj\archivematicaPhp\Storage;

use whikloj\archivematicaPhp\DjangoFilter;

/**
 * An Archivematica Space
 * @author Jared Whiklo
 *
 * A storage space contains all the information necessary to connect to the physical storage.
 * It is where the files are stored. Protocol-specific information, like an NFS export path and
 * hostname, or the username of a system accessible only via SSH, is stored here. All locations
 * must be contained in a space.
 */
interface Space
{
    /**
     * Get all spaces
     *
     * @return array
     *   Array of array of spaces with keys meta (for pagination, etc) and objects
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Problem with username or API key.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Problem with making the request.
     */
    public function getAll(): array;

    /**
     * Get all spaces using a DjangoFilter to filter.
     *
     * @param \whikloj\archivematicaPhp\DjangoFilter $filter
     *   The filter to use to restrict spaces.
     * @return array
     *   Array of spaces. Format is same as getAll
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Problem with username or API key.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Problem with making the request.
     * @see Space::getAll()
     */
    public function getAllFilter(DjangoFilter $filter): array;

    /**
     * Get all spaces using an array of DjangoFilter to filter.
     *
     * @param array $filters
     *   An array of filters to use to restrict spaces.
     * @return array
     *   Array of spaces. Format is same as getAll
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Problem with username or API key.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Problem with making the request.
     * @see Space::getAll()
     */
    public function getAllFilters(array $filters): array;

    /**
     * Get details about a space.
     *
     * @param string $uuid
     *   The space UUID.
     * @return array
     *   Array of metadata about the space.
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Problem with username or API key.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Problem with making the request.
     */
    public function details(string $uuid): array;

    /**
     * Browse the contents of a space.
     *
     * @param string $uuid
     *   The UUID of the space to browse.
     * @param string $path
     *   A path to limit the browse to (if provided).
     * @return array
     *   Array with keys:
     *    - "entries" containing path, files and directories.
     *    - "directories" containing directories in a path (subset) of above.
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Problem with username or API key.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Problem with making the request.
     */
    public function browse(string $uuid, string $path): array;

    /**
     * Create a new space
     * @param string $type
     *   The type of space, the name of the desired class
     * @param array $fields
     *   Associative array of all required and desired optional fields.
     * @return array
     *   Metadata for the newly created space.
     * @throws \whikloj\archivematicaPhp\Exceptions\Storage\InvalidSpaceTypeException
     *   Try to specify an invalid type
     * @throws \whikloj\archivematicaPhp\Exceptions\Storage\SpaceTypeException
     *   Missing required fields.
     */
    public function create(string $type, array $fields): array;
}
