<?php

namespace whikloj\archivematicaPhp\Storage\Spaces;

class DspaceRest extends AbstractSpaceType
{
    protected $space_name = "DSpace via REST API";

    protected $space_type_code = "DSPC_RST";

    protected $required_fields = [
        "path", // the absolute path to the Space on the local filesystem.
        "staging_path", // the absolute path to a staging area. Must be UNIX filesystem compatible and
                        // preferably will be located on the same filesystem as the path.
        "ds_rest_url", // URL to the “REST” webapp. E.g. http://localhost:8080/rest/; for production systems,
                    // this address will be slightly different, such as: https://demo.dspace.org/rest/.
        "ds_user", // a username for the DSpace instance with sufficient permissions to permit authentication.
        "ds_password", // the password for the username above.
        "ds_dip_collection", // the UUID of the collection into which the DIP will be deposited
                                            // barring it being designated in a transfer metadata file.
        "ds_api_collection", // the UUID of the collection into which the AIP will be deposited
                                            // barring it being designated in a transfer metadata file.
    ];

    protected $optional_fields = [
        "size", // the maximum size allowed for this space. Set to 0 or leave blank for unlimited.
        "as_url", // URL to the ArchiveSpace server. E.g.: http://sandbox.archivesspace.org/.
        "as_user", // ArchivesSpace username to authenticate as
        "as_password", // ArchivesSpace password to authenticate with
        "as_repository", // Identifier of the default ArchivesSpace repository
        "as_archival_object", // Identifier of the default ArchivesSpace archival object
                                // barring it being designated in a transfer metadata file
        "upload_to_tsm", // this is a feature specific to the requirements of Edinburgh University
                        // which sponsored the development of this space. Essentially it executes a
                        // bash command using a binary called dsmc.
        "verify_ssl", // Requests verifies SSL certificates for HTTPS requests, just like a web browser. By
                        // default, SSL verification is enabled, and Requests will throw a SSLError if it’s
                        // unable to verify the certificate
    ];
}
