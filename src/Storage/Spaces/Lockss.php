<?php

namespace whikloj\archivematicaPhp\Storage\Spaces;

/**
 * LOCKSS space type.
 * @author Jared Whiklo
 * @since 0.0.1
 */
class Lockss extends AbstractSpaceType
{
    protected $space_name = "LOCKSS";

    protected $space_type_code = "LOM";

    protected $required_fields = [
        "path", // the absolute path to the Space on the local filesystem.
        "staging_path", // the absolute path to a staging area. Must be UNIX filesystem compatible and preferably
                        // will be located on the same filesystem as the path.
        "sd_iri", // the URL of the LOCKSS-o-matic service document IRI,
                                // e.g. http://lockssomatic.example.org/api/sword/2.0/sd-iri.
        "content_provider_id", // the On-Behalf-Of value when communicating with LOCKSS-o-matic.
        "external_domain", // the base URL for this server that LOCKSS will be able to access. Generally
                                        // this is the URL for the home page of the Storage Service.
        "keep_local", // if you wish to store a local copy of the AIPs even after they are stored in LOCKSS
    ];
}
