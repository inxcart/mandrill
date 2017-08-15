<?php

namespace MandrillModule\Exception;

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * You cannot remove the last IP from your default IP pool.
 */
class InvalidEmptyDefaultPool extends Error
{
}
