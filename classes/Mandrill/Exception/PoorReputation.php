<?php

namespace MandrillModule\Exception;

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * The user's reputation is too low to continue.
 */
class PoorReputation extends Error
{
}
