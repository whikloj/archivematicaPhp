<?php

namespace whikloj\archivematicaPhp\Storage\Spaces;

class NetworkFileSystem extends AbstractSpaceType
{
    protected $space_name = "Networked Filesystem";

    protected $space_type_code = "NFS";

    protected $required_fields = [
        "path", // the absolute path to where the space is mounted on the filesystem local to the Storage Service.
        "staging_path", // the absolute path to a staging area. Must be UNIX filesystem compatible and preferably
                        // will be located on the same filesystem as the path.
        "remote_name", // the hostname or IP address of the remote computer exporting the NFS mount.
        "remote_path", // the export path on the NFS server
        "version", // the version of the filesystem, e.g. nfs or nfs4,as would be passed to the mount command.
        "manually_mounted", // This is a placeholder for a feature that is not yet available.
    ];
}
