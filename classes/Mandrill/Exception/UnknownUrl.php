<?php

namespace MandrillModule\Exception;

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * The requested URL has not been seen in a tracked link
 */
class UnknownUrl extends Error
{
}
