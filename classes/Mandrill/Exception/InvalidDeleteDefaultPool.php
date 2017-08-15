<?php

namespace MandrillModule\Exception;

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * The default pool cannot be deleted.
 */
class InvalidDeleteDefaultPool extends Error
{
}
