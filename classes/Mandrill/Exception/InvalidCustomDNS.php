<?php

namespace MandrillModule\Exception;

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * The domain name is not configured for use as the dedicated IP's custom reverse DNS.
 */
class InvalidCustomDNS extends Error
{
}
