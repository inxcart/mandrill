<?php

namespace MandrillModule\Exception;

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * A custom DNS change for this dedicated IP is currently pending.
 */
class InvalidCustomDNSPending extends Error
{
}
