<?php

namespace whikloj\archivematicaPhp;

interface Ingest extends Operation
{
    /**
     * List SIPS Waiting for User Input.
     *
     * Despite the URL, this currently returns both SIPs & transfers that are waiting for user input.
     * In the future, a separate /api/transfer/ waiting should be added for transfers.
     *
     * @return array
     *  Array of associative arrays with keys:
     *  - sip_directory: Directory the SIP is in currently.
     *  - sip_uuid: UUID OF THE SIP.
     *  - sip_name: Name of the SIP.
     *  - microservice: Name of the current microservice.
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Problem with username or API key.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Problem with making the request.
     */
    public function listWaiting(): array;

    /**
     * Add metadata files to a SIP.
     *
     * @param string $uuid
     *   The UUID of the SIP to put the files in.
     * @param array $source_paths
     *   List of files to be copied, in the format 'source_location_uuid:full_path'
     * @return string
     *   UUID of the re-ingested SIP.
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   Problem with username or API key.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   Problem with making the request.
     */
    public function addMetadata(string $uuid, array $source_paths): string;
}
