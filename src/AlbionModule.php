<?php

namespace Aljerom\Albion;

use MagicPro\PluginModules\Module\AbstractModule;

class AlbionModule extends AbstractModule
{
    public function modulePath(): string
    {
        return __DIR__;
    }

    public function getName(): string
    {
        return 'albion';
    }

    public function getTitle(): string
    {
        return 'Альбион';
    }

    public function getIconPath(): string
    {
        return realpath($this->modulePath . '/../' . AbstractModule::MODULE_ICON);
    }
}
