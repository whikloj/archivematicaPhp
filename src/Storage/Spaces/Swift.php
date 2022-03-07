<?php

namespace whikloj\archivematicaPhp\Storage\Spaces;

/**
 * Swift space type.
 * @author Jared Whiklo
 * @since 0.0.1
 */
class Swift extends AbstractSpaceType
{
    protected $space_name = "Swift";

    protected $space_type_code = "SWIFT";

    protected $required_fields = [

        "path", // the absolute path to the Space on the local filesystem.
        "staging_path", // the absolute path to a staging area. Must be UNIX filesystem compatible
                        // and preferably will be located on the same filesystem as the path.
        "auth_url", // the URL to authenticate against.
        "auth_version", // the OpenStack authentication version.
        "username", // the Swift username that will be used for authentication.
        "password", // the password for the above username.
        "container", // the name of the Swift container. To list available containers in your Swift installation,
                    // run swift list from the command line.
        "tenant", // the tenant/account name, required when connecting to an auth 2.0 system.
    ];

    protected $optional_fields = [
        "size", // the maximum size allowed for this space. Set to 0 or leave blank for unlimited.
        "region", // the region in Swift.
    ];
}
