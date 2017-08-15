<?php

namespace MandrillModule\Exception;

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * The requested webhook does not exist
 */
class UnknownWebhook extends Error
{
}
