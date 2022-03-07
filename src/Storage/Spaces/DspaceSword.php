<?php

namespace whikloj\archivematicaPhp\Storage\Spaces;

/**
 * DSpace via SWORD 2 space type
 * @author Jared Whiklo
 * @since 0.0.1
 */
class DspaceSword extends AbstractSpaceType
{
    protected $space_name = "Dspace via SWORD 2";

    protected $space_type_code = "DSPACE";

    protected $required_fields = [
        "path", // the absolute path to the Space on the local filesystem.
        "staging_path", // the absolute path to a staging area. Must be UNIX filesystem compatible and preferably
                        // will be located on the same filesystem as the path.
        "sd_iri", // URL of the service document. E.g. http://demo.dspace.org/swordv2/servicedocument,
                                // where servicedocument is the handle to the community or collection being used for
                                // deposit.
        "user", // a username for the DSpace instance with sufficient permissions to permit authentication.
        "password", // the password for the username above.
        "metadata_policy", // Use to restrict access to the metadata bitstream. Must be specified as a
                                      // list of objects in JSON, e.g.
                                      // [{"action":"READ","groupId":"5","rpType":"TYPE_CUSTOM"}]. This will
                                      // override existing policies.
        "archive_format", // One of "ZIP" or "7z"
    ];
}
