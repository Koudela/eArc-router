<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 * router component
 *
 * @package earc/router
 * @link https://github.com/Koudela/earc-router/
 * @copyright Copyright (c) 2018-2021 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\RouterTests;

use eArc\Core\Configuration;
use eArc\DI\DI;
use eArc\DI\Exceptions\InvalidArgumentException;
use eArc\EventTree\Exceptions\InvalidObserverNodeException;
use eArc\EventTree\Exceptions\IsDispatchedException;
use eArc\EventTree\Interfaces\TreeEventInterface;
use eArc\Router\Interfaces\RequestInformationInterface;
use eArc\Router\Interfaces\RouteInformationInterface;
use eArc\Router\Interfaces\RouterEventInterface;
use eArc\Router\RouterEvent;
use eArc\RouterEventTreeRoot\earc\lifecycle\router\ExecuteCallListener;
use eArc\RouterTests\env\Collector;
use eArc\RouterTests\env\TestLifecycleEvent;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * This is no unit test. It is an integration test.
 */
class RouterTest extends TestCase
{
    /** @var string[] */
    public static $collector = [];

    /**
     * @throws InvalidArgumentException
     * @throws IsDispatchedException
     */
    public function testIntegration()
    {
        $this->bootstrap();
        $this->runUseAssertions();
        $this->runSpecialCharactersAssertions();
        $this->runRerouteAssertions();
        $this->runLifeCycleHooksAssertions();
        $this->runRoutingDirectoryAssertions();
        $this->runSerializingAssertions();
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function bootstrap()
    {
        $vendorDir = dirname(__DIR__).'/vendor';

        if (!is_dir($vendorDir)) {
            $vendorDir = dirname(__DIR__, 3);
        }

        require_once $vendorDir.'/autoload.php';

        DI::init();
        Configuration::build('../tests/env/.earc-config.php');
    }

    /**
     * @throws IsDispatchedException
     */
    protected function runUseAssertions()
    {
        di_clear_cache();

        $collector = new Collector();
        di_mock(Collector::class, $collector);
        $event = new RouterEvent('/admin/backoffice/user/edit/1', 'GET');
        $event->dispatch();
        $this->assertEquals([
            'eArc\\RouterTests\\env\\routing\\ListenerStart',
            'eArc\\RouterTests\\env\\routing\\admin\\ListenerBefore',
            'eArc\\RouterTests\\env\\routing\\admin\\backoffice\\user\\Controller',
        ], $collector->calledListener);
        $this->assertTrue($collector->payload['route'] instanceof RouteInformationInterface);
        $this->assertTrue($collector->payload['request'] instanceof RequestInformationInterface);
    }

    /**
     * @throws IsDispatchedException
     */
    protected function runSpecialCharactersAssertions()
    {
        $collector = new Collector();
        di_mock(Collector::class, $collector);
        $event = new RouterEvent('/special_characters', 'GET');
        try {
            $event->dispatch();
            $this->assertTrue(false);
        } catch (Exception $exception) {
            $this->assertTrue($exception instanceof InvalidObserverNodeException);
        }

        $collector = new Collector();
        di_mock(Collector::class, $collector);
        $event = new RouterEvent('/spe~ci-al.chars', 'GET');
        $event->dispatch();
        $this->assertEquals([
            'eArc\\RouterTests\\env\\routing\\ListenerStart',
            'eArc\\RouterTests\\env\\routing\\special_characters\\Controller',
        ], $collector->calledListener);
    }

    /**
     * @throws IsDispatchedException
     */
    protected function runRerouteAssertions()
    {
        $collector = new Collector();
        di_mock(Collector::class, $collector);
        $event = new RouterEvent('/login', 'GET');
        $event->dispatch();
        $this->assertEquals([
            'eArc\\RouterTests\\env\\routing\\ListenerStart',
            'eArc\\RouterTests\\env\\routing\\login\\Controller',
            'eArc\\RouterTests\\env\\routing\\ListenerStart',
            'eArc\\RouterTests\\env\\routing\\error_pages\\access_denied\\Controller',
        ], $collector->calledListener);
    }

    /**
     * @throws IsDispatchedException
     */
    protected function runLifeCycleHooksAssertions()
    {
        di_clear_cache();
        di_import_param(['earc' => ['event_tree' => ['blacklist' => [
            ExecuteCallListener::class => true,
        ]]]]);

        $collector = new Collector();
        di_mock(Collector::class, $collector);
        $event = new TestLifecycleEvent('/lifecycle/parametrized/test/123', 'GET');
        $event->dispatch();
        $this->assertEquals([
            'eArc\\RouterTests\\env\\earc\\lifecycle\\router\\PreProcessingListener',
            'eArc\\RouterTests\\env\\earc\\lifecycle\\router\\ExecuteCallListener',
            'eArc\\RouterTests\\env\\routing\\ListenerStart',
            'eArc\\RouterTests\\env\\earc\\lifecycle\\router\\PostProcessingListener',
            'eArc\\RouterTests\\env\\earc\\lifecycle\\router\\PreProcessingListener',
            'eArc\\RouterTests\\env\\earc\\lifecycle\\router\\ExecuteCallListener',
            'eArc\\RouterTests\\env\\routing\\lifecycle\\Listener',
            'eArc\\RouterTests\\env\\earc\\lifecycle\\router\\PostProcessingListener',
            'eArc\\RouterTests\\env\\earc\\lifecycle\\router\\PreProcessingListener',
            'eArc\\RouterTests\\env\\earc\\lifecycle\\router\\ExecuteCallListener',
            'eArc\\RouterTests\\env\\routing\\lifecycle\\parametrized\\ParametrizedController',
            'eArc\\RouterTests\\env\\earc\\lifecycle\\router\\PostProcessingListener',
        ], $collector->calledListener);
        $this->assertEquals([
            'preProcessing(eArc\\RouterTests\\env\\TestLifecycleEvent)',
            'postProcessing(eArc\\RouterTests\\env\\TestLifecycleEvent)',
            'preProcessing(eArc\\RouterTests\\env\\TestLifecycleEvent)',
            'postProcessing(eArc\\RouterTests\\env\\TestLifecycleEvent)',
            'preProcessing(eArc\\RouterTests\\env\\TestLifecycleEvent)',
            'testAction(eArc\\RouterTests\\env\\TestLifecycleEvent)',
            'postProcessing(eArc\\RouterTests\\env\\TestLifecycleEvent)',
        ], $collector->calledMethods);
        di_import_param(['earc' => ['event_tree' => ['blacklist' => [
            ExecuteCallListener::class => false,
        ]]]]);
        di_clear_cache();
    }

    /**
     * @throws IsDispatchedException
     */
    protected function runRoutingDirectoryAssertions()
    {
        di_import_param(['earc' => [
            'router' => [
                'routing_directory' => [
                    TreeEventInterface::class => 'v2/routing',
                    RouterEventInterface::class => 'v2_routing',
                ]
            ]
        ]]);

        $collector = new Collector();
        di_mock(Collector::class, $collector);
        $event = new RouterEvent('/test/123', 'GET');
        $event->dispatch();
        $this->assertEquals([
            'eArc\\RouterTests\\env\\v2\\routing\\test\\Controller'
        ], $collector->calledListener);

        di_import_param(['earc' => [
            'router' => [
                'routing_directory' => [
                    TreeEventInterface::class => 'routing',
                ]
            ]
        ]]);
    }

    /**
     * @throws IsDispatchedException
     */
    protected function runSerializingAssertions()
    {
        $collector = new Collector();
        di_mock(Collector::class, $collector);
        $event = new RouterEvent('/serialize/hit');
        $event->dispatch();
        $this->assertEquals([
            'eArc\RouterTests\env\routing\ListenerStart',
            'eArc\RouterTests\env\routing\serialize\Listener',
            'eArc\RouterTests\env\routing\ListenerStart',
            'eArc\RouterTests\env\routing\unserialize\Controller',
            'eArc\RouterTests\env\routing\ListenerStart',
            'eArc\RouterTests\env\routing\serialize\Listener',
            'eArc\RouterTests\env\routing\serialize\hit\Controller',
        ], $collector->calledListener);
    }

    public function testResponseControllerAssertions()
    {
        // TODO: implement;
    }
}
