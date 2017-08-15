<?php

namespace MandrillModule\Exception;

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * The requested sender does not exist
 */
class UnknownSender extends Error
{
}
