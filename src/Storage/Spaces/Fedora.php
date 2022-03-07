<?php

namespace whikloj\archivematicaPhp\Storage\Spaces;

/**
 * Fedora via SWORD 2 Space.
 * @author Jared Whiklo
 * @since 0.0.1
 */
class Fedora extends AbstractSpaceType
{
    protected $space_name = "Fedora via SWORD 2";

    protected $space_type_code = "FEDORA";

    protected $required_fields = [
        "path", // the absolute path to the Space on the local filesystem.
        "staging_path", // the absolute path to a staging area. Must be UNIX filesystem compatible
                        // and preferably will be located on the same filesystem as the path.
        "fedora_user", // Fedora user name (for SWORD functionality).
        "fedora_password", // Fedora password (for SWORD functionality).
        "fedora_name", // Name or IP of the remote Fedora machine.
    ];
}
