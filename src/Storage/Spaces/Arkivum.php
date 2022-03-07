<?php

namespace whikloj\archivematicaPhp\Storage\Spaces;

/**
 * Arkivum space type.
 * @author Jared Whiklo
 * @since 0.0.1
 */
class Arkivum extends AbstractSpaceType
{
    protected $space_name = "Arkivum";

    protected $space_type_code = "ARKIVUM";

    protected $required_fields = [
        "path", // the local path on the Storage Service machine to the CIFS share, e.g. /mnt/astor
        "staging_path", // the absolute path to a staging area. Must be UNIX filesystem compatible, preferably on the
                        // same filesystem as the path, e.g. /mnt/astor/archivematica1/tmp
        "host", // the hostname of the Arkivum web instance or IP address with port, e.g. arkivum.example.com:8443
    ];

    protected $optional_fields = [
        "size", // the maximum size allowed for this space. Set to 0 or leave blank for unlimited.
        "remote_user", // the username on the remote machine accessible via passwordless ssh.
        "remote_name", // the name or IP of the remote machine.
    ];
}
