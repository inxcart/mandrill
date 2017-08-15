<?php

namespace MandrillModule\Exception;

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * The provided API key is not a valid Mandrill API key
 */
class InvalidKey extends Error
{
}
