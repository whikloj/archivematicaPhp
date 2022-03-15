<?php

namespace whikloj\archivematicaPhp;

/**
 * A transfer operation to Archivematica.
 * @author Jared Whiklo
 * @since 0.0.1
 */
interface Transfer extends Operation
{
    /**
     * Start a transfer.
     *
     * @param string $name
     *   The transfer name.
     * @param string $type
     *   The transfer type.
     * @param string $accession
     *   The transfer accession number.
     * @param array $paths
     *   Array of local uuid => relative path pairs.
     * @param array $row_ids
     *   Array of TransferMetadataSet IDs
     * @return string
     *   The path of the new transfer.
     * @throws \InvalidArgumentException
     *   If the type is not valid.
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Problem with username or API key.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Problem with making the request.
     */
    public function start(
        string $name,
        string $type,
        string $accession,
        array $paths,
        array $row_ids
    ): string;

    /**
     * Get the status of an operation.
     *
     * @param string $uuid
     *   The UUID of the operation.
     * @return array
     *   Array with keys
     *    - status -> one of FAILED, REJECTED, USER_INPUT, COMPLETE or PROCESSING
     *    - name -> Name of transfer
     *    - sip_uuid -> If status is COMPLETE, this field will exist with either the UUID of the SIP or ‘BACKLOG’
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
     *   Transfer not found
     */
    public function status(string $uuid): array;

    /**
     * List unapproved transfers.
     *
     * @return array
     *   Array of associative array with keys.
     *   - type: Transfer type, One of: standard, unzipped bag, zipped bag, dspace.
     *   - directory: Directory the transfer is in currently.
     *   - uuid: UUID of the transfer.
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Problem with username or API key.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Problem with making the request.
     * @throws \whikloj\archivematicaPhp\Exceptions\ItemNotFoundException
     *   Endpoint not found (returned a 404)
     */
    public function unapproved(): array;

    /**
     * Approve a pending transfer.
     *
     * @param string $directory
     *   Directory the transfer is in currently.
     * @param string $type
     *   The type of transfer. One of: standard, unzipped bag, zipped bag, dspace.
     * @return string
     *   The UUID of the approved transfer.
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Problem with username or API key.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Problem with making the request.
     * @throws \InvalidArgumentException
     *   Invalid type provided.
     * @throws \whikloj\archivematicaPhp\Exceptions\ItemNotFoundException
     *   Transfer not found.
     */
    public function approve(string $directory, string $type): string;

    /**
     * Reingest a transfer
     *
     * @param string $name
     *   Name of the AIP. The AIP should also be found at %sharedDirectory%/tmp/<name>.
     * @param string $uuid
     *   UUID of the AIP to reingest
     * @return string
     *   UUID of the re-ingested AIP.
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Problem with username or API key.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Problem with making the request.
     * @throws \whikloj\archivematicaPhp\Exceptions\ItemNotFoundException
     *   Location not found.
     */
    public function reingest(string $name, string $uuid): string;
}
