<?php

namespace Membership;

use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\ModuleManager\ModuleManagerInterface;
use Zend\Console\Adapter\AdapterInterface as Console;

class Module implements ConsoleUsageProviderInterface
{
    /**
     * Init
     */
    public function init(ModuleManagerInterface $moduleManager)
    {}

    /**
     * Return autoloader config array
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\ClassMapAutoloader' => [
                __DIR__ . '/autoload_classmap.php'
            ],
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__
                ]
            ]
        ];
    }

    /**
     * Return service config array
     *
     * @return array
     */
    public function getServiceConfig()
    {
        return [
        ];
    }

    /**
     * Init view helpers
     */
    public function getViewHelperConfig()
    {
        return [
        ];
    }

    /**
     * Return path to config file
     *
     * @return boolean
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * Get console usage info
     *
     * @param object $console
     * @return array
     */
    public function getConsoleUsage(Console $console)
    {
        return [
            // describe available commands
            'membership clean expired connections [--verbose|-v]' => 'Clean expired membership connections',
            // describe expected parameters
            ['--verbose|-v', '(optional) turn on verbose mode']
        ];
    }
}