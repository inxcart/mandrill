<?php

namespace MandrillModule\Exception;

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * The requested feature requires payment.
 */
class PaymentRequired extends Error
{
}
