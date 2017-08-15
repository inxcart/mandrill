<?php

namespace MandrillModule\Exception;

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * The provided message id does not exist.
 */
class UnknownMessage extends Error
{
}
