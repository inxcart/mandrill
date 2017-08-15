<?php

namespace MandrillModule\Exception;

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * The provided dedicated IP pool does not exist.
 */
class UnknownPool extends Error
{
}
