<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_0_8_7($module)
{
    $module->uninstallOverrides();
    $module->installOverrides();

    return true;
}