<?php

namespace MandrillModule\Exception;

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * The requested export job does not exist
 */
class UnknownExport extends Error
{
}
