<?php
namespace Craft;

class SmToolsVariable
{
    /**
     * Return current git commit
     *
     * @author Sitemind (www.sitemind.nl)
     * @return string
     */
    public function gitHash()
    {
        return craft()->smTools->gitHash();
    }
}
