<?php

namespace whikloj\archivematicaPhp\Storage\Spaces;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use whikloj\archivematicaPhp\ArchivematicaImpl;
use whikloj\archivematicaPhp\Exceptions\Storage\SpaceTypeException;
use whikloj\archivematicaPhp\Utils\ArchivmaticaUtils;

abstract class AbstractSpaceType implements SpaceType
{
    /**
     * @var string The human readable space name.
     */
    protected $space_name = "";

    /**
     * @var string The code for this space type (ie. FS for Local Filesystem)
     */
    protected $space_type_code = "";

    /**
     * @var array The required fields for this type.
     */
    protected $required_fields = [
        "path", // the local path on the Storage Service machine to the CIFS share, e.g. /mnt/astor
        "staging_path", // the absolute path to a staging area. Must be UNIX filesystem compatible, preferably on the
                        // same filesystem as the path, e.g. /mnt/astor/archivematica1/tmp
    ];

    /**
     * @var array Optional but still valid fields for this type.
     */
    protected $optional_fields = [
        "size", // the maximum size allowed for this space. Set to 0 or leave blank for unlimited. Optional.
    ];

    /**
     * Generate an array for the payload to create a space.
     *
     * @param array $fields
     *   Associative array of fields => values.
     * @return array
     *   The JSON encoded string.
     */
    protected function generatePayload(array $fields): array
    {
        $required_keys = array_combine($this->required_fields, $this->required_fields);
        $optional_keys = array_combine($this->optional_fields, $this->optional_fields);
        $all_allowed = array_merge($required_keys, $optional_keys);
        $valid_data = array_intersect_key($fields, $all_allowed);
        $valid_data['access_protocol'] = $this->space_type_code;
        return $valid_data;
    }

    /**
     * @inheritDoc
     */
    public function create(Client $client, array $fields): array
    {
        $output = [];
        if (!is_array($this->required_fields) || count($this->required_fields) == 0) {
            throw new SpaceTypeException("This space type does not have required fields configured.");
        }
        if (!$this->checkFields($fields)) {
            throw new SpaceTypeException("Missing required fields for this type of space.");
        }
        $payload = $this->generatePayload($fields);
        try {
            $response = $client->post(
                '/api/v2/space/',
                [
                    'json' => $payload,
                ]
            );
            $output = ArchivmaticaUtils::checkResponse(
                $response,
                201,
                "Failed to create space of type ($this->space_name)"
            );
        } catch (GuzzleException $e) {
            ArchivmaticaUtils::decodeGuzzleException($e);
        }
        return $output;
    }

    /**
     * Check the the provided array has all the required fields, extra fields are ignored.
     * @param array $incoming_fields
     *   Incoming associative array.
     * @return bool
     *   True if has all required fields.
     */
    protected function checkFields(array $incoming_fields): bool
    {
        $has_required = array_intersect_key(
            array_combine($this->required_fields, $this->required_fields),
            $incoming_fields
        );
        return count($has_required) == count($this->required_fields);
    }
}
