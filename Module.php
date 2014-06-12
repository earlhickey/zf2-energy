<?php

namespace Energy;

use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Energy\Mapper\Energy;
use Energy\Mapper\EnergyTable;
use Energy\Mapper\EnergyDay;
use Energy\Mapper\EnergyDayTable;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

class Module
{
    // Override default layout for this module
    public function init(ModuleManager $moduleManager)
    {
        $sharedEvents = $moduleManager->getEventManager()->getSharedManager();
        $sharedEvents->attach('Energy', 'dispatch', function($e) {
            $controller = $e->getTarget();
            $controller->layout('layout/energy');
        }, 100);
    }

    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Energy\Mapper\EnergyTable' =>  function($sm) {
                    $tableGateway = $sm->get('EnergyTableGateway');
                    $table = new EnergyTable($tableGateway);
                    return $table;
                },
                'EnergyTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Energy());
                    return new TableGateway('energy', $dbAdapter, null, $resultSetPrototype);
                },
                'Energy\Mapper\EnergyDayTable' =>  function($sm) {
                    $tableGateway = $sm->get('EnergyDayTableGateway');
                    $table = new EnergyDayTable($tableGateway);
                    return $table;
                },
                'EnergyDayTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new EnergyDay());
                    return new TableGateway('energy_day', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }
}
