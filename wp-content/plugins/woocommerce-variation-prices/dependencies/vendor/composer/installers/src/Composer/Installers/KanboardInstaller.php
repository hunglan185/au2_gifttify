<?php

namespace Barn2\Plugin\WC_Variation_Prices\Dependencies\Composer\Installers;

/**
 *
 * Installer for kanboard plugins
 *
 * kanboard.net
 *
 * Class KanboardInstaller
 * @package Composer\Installers
 */
class KanboardInstaller extends BaseInstaller
{
    protected $locations = array('plugin' => 'plugins/{$name}/');
}
