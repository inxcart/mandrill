<?php

namespace MandrillModule\Exception;

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * The requested email is not in the rejection list
 */
class InvalidReject extends Error
{
}
