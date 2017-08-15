<?php

namespace MandrillModule\Exception;

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * The requested tag does not exist or contains invalid characters
 */
class InvalidTagName extends Error
{
}
