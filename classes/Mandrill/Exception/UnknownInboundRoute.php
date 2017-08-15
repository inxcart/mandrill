<?php

namespace MandrillModule\Exception;

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * The provided inbound route does not exist.
 */
class UnknownInboundRoute extends Error
{
}
