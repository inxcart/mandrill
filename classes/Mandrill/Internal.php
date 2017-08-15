<?php

namespace MandrillModule;

if (!defined('_TB_VERSION_')) {
    exit;
}

class Internal
{
    public function __construct(Mandrill $master)
    {
        $this->master = $master;
    }

}


