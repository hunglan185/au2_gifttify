<?php

namespace Barn2\Plugin\WC_Variation_Prices\Dependencies\Composer\Installers;

class AglInstaller extends BaseInstaller
{
    protected $locations = array('module' => 'More/{$name}/');
    /**
     * Format package name to CamelCase
     */
    public function inflectPackageVars($vars)
    {
        $vars['name'] = \preg_replace_callback('/(?:^|_|-)(.?)/', function ($matches) {
            return \strtoupper($matches[1]);
        }, $vars['name']);
        return $vars;
    }
}