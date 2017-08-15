<?php

namespace MandrillModule\Exception;

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * Custom metadata field limit reached.
 */
class MetadataFieldLimit extends Error
{
}
