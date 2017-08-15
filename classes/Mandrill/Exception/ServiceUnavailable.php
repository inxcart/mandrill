<?php

namespace MandrillModule\Exception;

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * The subsystem providing this API call is down for maintenance
 */
class ServiceUnavailable extends Error
{
}
