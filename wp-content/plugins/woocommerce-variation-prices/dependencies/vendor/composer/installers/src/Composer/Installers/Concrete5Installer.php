<?php

namespace Barn2\Plugin\WC_Variation_Prices\Dependencies\Composer\Installers;

class Concrete5Installer extends BaseInstaller
{
    protected $locations = array('core' => 'concrete/', 'block' => 'application/blocks/{$name}/', 'package' => 'packages/{$name}/', 'theme' => 'application/themes/{$name}/', 'update' => 'updates/{$name}/');
}