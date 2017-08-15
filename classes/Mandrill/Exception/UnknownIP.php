<?php

namespace MandrillModule\Exception;

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * The provided dedicated IP does not exist.
 */
class UnknownIP extends Error
{
}
