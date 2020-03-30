<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 * event tree component
 *
 * @package earc/event-tree
 * @link https://github.com/Koudela/eArc-eventTree/
 * @copyright Copyright (c) 2018-2020 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\RouterTests;

use eArc\DI\DI;
use eArc\DI\Exceptions\InvalidArgumentException;
use eArc\EventTree\Exceptions\IsDispatchedException;
use eArc\Router\Interfaces\RequestInformationInterface;
use eArc\Router\Interfaces\RouteInformationInterface;
use eArc\Router\RouterEvent;
use eArc\RouterTests\env\Collector;
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
        $this->runSomeAssertions();
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

        di_import_param(['earc' => [
            'vendor_directory' => $vendorDir,
            'event_tree' => [
                'directories' => [
                    '../earc-event-tree' => 'eArc\\RouterEventTreeRoot',
                    '../tests/env' => 'eArc\\RouterTests\\env',
                ]
            ]
        ]]);
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

    protected function runSomeAssertions()
    {
        $collector = new Collector();
        di_mock(Collector::class, $collector);
        $event = new RouterEvent('', 'GET');
        $event->dispatch();
        var_dump($collector->calledListener);

        $collector = new Collector();
        di_mock(Collector::class, $collector);
        $event = new RouterEvent('/', 'GET');
        $event->dispatch();
        var_dump($collector->calledListener);
    }
}
