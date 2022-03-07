<?php

namespace whikloj\archivematicaPhp\Storage\Spaces;

class WriteOnlyReplica extends AbstractSpaceType
{
    protected $space_type_code = "REPLICA";

    protected $space_name = "Write-Only Replica Staging on Local Filesystem";
}
