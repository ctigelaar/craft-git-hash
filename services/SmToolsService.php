<?php
namespace Craft;

class SmToolsService extends BaseApplicationComponent
{
    /**
     * Return current git commit
     *
     * @author Sitemind (www.sitemind.nl)
     * @return string
     */
    public function gitHash()
    {
        $hash = `git rev-parse --short --verify HEAD`;
        return trim($hash);
    }
}
