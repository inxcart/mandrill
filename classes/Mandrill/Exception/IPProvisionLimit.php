<?php

namespace MandrillModule\Exception;

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * A dedicated IP cannot be provisioned while another request is pending.
 */
class IPProvisionLimit extends Error
{
}
