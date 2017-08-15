<?php

namespace MandrillModule\Exception;

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * The user hasn't started sending yet.
 */
class NoSendingHistory extends Error
{
}
