<?php

namespace MandrillModule\Exception;

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * The parameters passed to the API call are invalid or not provided when required
 */
class ValidationError extends Error
{
}
