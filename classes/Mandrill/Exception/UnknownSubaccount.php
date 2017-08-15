<?php

namespace MandrillModule\Exception;

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * The provided subaccount id does not exist.
 */
class UnknownSubaccount extends Error
{
}
