<?php

namespace whikloj\archivematicaPhp\Storage\Spaces;

/**
 * Pipeline Local Filesystem space type.
 * @author Jared Whiklo
 * @since 0.0.1
 */
class PipelineLocalFS extends AbstractSpaceType
{
    protected $space_name = "Pipeline Local Filesystem";

    protected $space_type_code = "PIPE_FS";

    protected $required_fields = [
        "path", // the absolute path to the Space on the local filesystem.
        "staging_path", // the absolute path to a staging area. Must be UNIX filesystem compatible
                        // and preferably will be located on the same filesystem as the path.
        "remote_user", // Username on the remote machine accessible via ssh
        "remote_name", // Name or IP of the remote machine.
        "assume_rsync_daemon", // True if will use rsync daemon-style commands instead of the default rsync with remote
                                // shell transport

    ];

    protected $optional_fields = [
        "size", // the maximum size allowed for this space. Set to 0 or leave blank for unlimited.
        "rsync_password", // RSYNC_PASSWORD value (rsync daemon)
    ];
}
