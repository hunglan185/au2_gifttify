<?php

namespace Barn2\Plugin\WC_Variation_Prices\Dependencies\Composer\Installers;

class StarbugInstaller extends BaseInstaller
{
    protected $locations = array('module' => 'modules/{$name}/', 'theme' => 'themes/{$name}/', 'custom-module' => 'app/modules/{$name}/', 'custom-theme' => 'app/themes/{$name}/');
}
