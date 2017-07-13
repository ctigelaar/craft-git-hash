<?php
namespace Craft;

class SmToolsPlugin extends BasePlugin
{
    public function getName()
    {
         return 'Sitemind Tools';
    }

    public function getVersion()
    {
        return '1.0.6';
    }

    public function getDeveloper()
    {
        return 'Sitemind';
    }

    public function getDeveloperUrl()
    {
        return 'http://www.sitemind.nl';
    }

    public function addTwigExtension()
    {
        Craft::import('plugins.smtools.twigextensions.ToolsTwigExtension');
        return new ToolsTwigExtension();
    }
}
