<?php
namespace AGTI\Checkout\Adapter;

use AGTI\Checkout\Exception\ModuleNotFound;

class ModuleLoader
{
    public static function loadModule($module_name)
    {
        if (!file_exists(_PS_MODULE_DIR_ . "{$module_name}/{$module_name}.php") && \Module::isInstalled($module_name) && \Module::isEnabled($module_name)) {
            throw new ModuleNotFound("O módulo {$module_name} não foi localizado ou não está ativo na loja.");
        }
        
        require_once _PS_MODULE_DIR_ . "{$module_name}/{$module_name}.php";
        if (!class_exists($module_name)) {
            throw new ModuleNotFound("O módulo {$module_name} parece estar corrompido. Classe {$module_name} não localizada.");
        }
    }
}