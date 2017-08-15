<?php

namespace MandrillModule\Exception;

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * The provided tracking domain does not exist.
 */
class UnknownTrackingDomain extends Error
{
}
