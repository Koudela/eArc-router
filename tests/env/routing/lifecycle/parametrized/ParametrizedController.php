<?php


namespace eArc\RouterTests\env\routing\lifecycle\parametrized;


use eArc\Router\Interfaces\RouterEventInterface;
use eArc\RouterTests\env\Collector;
use eArc\RouterTests\env\ParametrizedMethodCallingController;

class ParametrizedController extends ParametrizedMethodCallingController
{
    public function testAction(RouterEventInterface $event): void
    {
        $collector = di_get(Collector::class);

        $collector->calledListener[] = static::class;
        $collector->calledMethods[] = 'testAction('.get_class($event).')';
    }
}
