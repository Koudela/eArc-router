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

namespace eArc\EventTreeTests;

use eArc\DI\DI;
use eArc\DI\Exceptions\InvalidArgumentException;
use eArc\EventTree\Exceptions\IsDispatchedException;
use eArc\Router\RouterEvent;
use PHPUnit\Framework\TestCase;

/**
 * This is no unit test. It is an integration test.
 */
class RouterTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     * @throws IsDispatchedException
     */
    public function testIntegration()
    {
        $this->bootstrap();
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
    protected function runSomeAssertions()
    {
        di_clear_cache();

        $event = new RouterEvent('/admin/backoffice/user/edit/1', 'GET');
        $event->dispatch();

        $event = new RouterEvent('', 'GET');
        $event->dispatch();

        $event = new RouterEvent('/', 'GET');
        $event->dispatch();
    }
}