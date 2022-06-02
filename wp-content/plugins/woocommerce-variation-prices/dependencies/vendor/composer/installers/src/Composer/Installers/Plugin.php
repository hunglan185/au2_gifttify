<?php

namespace Barn2\Plugin\WC_Variation_Prices\Dependencies\Composer\Installers;

use Barn2\Plugin\WC_Variation_Prices\Dependencies\Composer\Composer;
use Barn2\Plugin\WC_Variation_Prices\Dependencies\Composer\IO\IOInterface;
use Barn2\Plugin\WC_Variation_Prices\Dependencies\Composer\Plugin\PluginInterface;
class Plugin implements PluginInterface
{
    private $installer;
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->installer = new Installer($io, $composer);
        $composer->getInstallationManager()->addInstaller($this->installer);
    }
    public function deactivate(Composer $composer, IOInterface $io)
    {
        $composer->getInstallationManager()->removeInstaller($this->installer);
    }
    public function uninstall(Composer $composer, IOInterface $io)
    {
    }
}
