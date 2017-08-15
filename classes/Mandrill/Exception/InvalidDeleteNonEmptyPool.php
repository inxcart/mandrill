<?php

namespace MandrillModule\Exception;

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * Non-empty pools cannot be deleted.
 */
class InvalidDeleteNonEmptyPool extends Error
{
}
