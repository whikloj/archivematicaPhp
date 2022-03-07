<?php

namespace whikloj\archivematicaPhp\Storage\Spaces;

/**
 * DuraCloud Space type
 * @author Jared Whiklo
 * @since 0.0.1
 */
class DuraCloud extends AbstractSpaceType
{
    protected $space_name = "DuraCloud";

    protected $space_type_code = "DC";

    protected $required_fields = [
        "path", // Leave this field blank.
        "staging_path", // A location on your local disk where Archivematica can place files for staging purposes,
                        // for example var/archivematica/storage_service/duracloud_staging
        "host", // Hostname of the DuraCloud instance, e.g. site.duracloud.org
        "username", // The username for the Archivematica user that you created in DuraCloud.
        "password", // The password for the Archivematica user that you created in DuraCloud.
        "duraspace", // The name of the space in DuraCloud you are creating this Storage Service space for
                    // (e.g. transfer-source, aip-store, etc).
    ];
}
