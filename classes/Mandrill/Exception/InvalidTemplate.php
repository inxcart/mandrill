<?php

namespace MandrillModule\Exception;

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * The given template name already exists or contains invalid characters
 */
class InvalidTemplate extends Error
{
}
