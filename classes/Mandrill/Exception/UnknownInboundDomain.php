<?php

namespace MandrillModule\Exception;

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * The requested inbound domain does not exist
 */
class UnknownInboundDomain extends Error
{
}
