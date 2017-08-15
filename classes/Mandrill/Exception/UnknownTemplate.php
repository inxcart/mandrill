<?php

namespace MandrillModule\Exception;

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * The requested template does not exist
 */
class UnknownTemplate extends Error
{
}
