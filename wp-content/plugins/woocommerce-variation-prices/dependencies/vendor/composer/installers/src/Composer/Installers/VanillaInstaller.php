<?php

namespace Barn2\Plugin\WC_Variation_Prices\Dependencies\Composer\Installers;

class VanillaInstaller extends BaseInstaller
{
    protected $locations = array('plugin' => 'plugins/{$name}/', 'theme' => 'themes/{$name}/');
}
