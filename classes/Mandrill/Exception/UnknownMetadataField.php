<?php

namespace MandrillModule\Exception;

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * The provided metadata field name does not exist.
 */
class UnknownMetadataField extends Error
{
}
