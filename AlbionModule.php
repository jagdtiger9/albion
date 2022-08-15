<?php

namespace albion;

use MagicPro\PluginModules\Module\AbstractModule;

class AlbionModule extends AbstractModule
{
    public function modulePath(): string
    {
        return __DIR__;
    }

    public function getTitle(): string
    {
        return 'Альбион';
    }
}
