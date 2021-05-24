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

namespace eArc\Router;

use eArc\ParameterTransformer\Configuration;
use eArc\ParameterTransformer\Exceptions\DiException;
use eArc\ParameterTransformer\Exceptions\FactoryException;
use eArc\ParameterTransformer\Exceptions\NoInputException;
use eArc\ParameterTransformer\Exceptions\NullValueException;
use eArc\ParameterTransformer\Interfaces\ParameterTransformerFactoryServiceInterface;
use eArc\ParameterTransformer\ParameterTransformer;
use eArc\Router\Exceptions\MethodNotFoundException;
use eArc\Router\Exceptions\ReturnTypeException;
use eArc\Router\Interfaces\RequestInformationInterface;
use eArc\Router\Interfaces\ResponseInterface;
use eArc\Router\Interfaces\RouteInformationInterface;
use eArc\Router\Interfaces\RouterEventInterface;
use eArc\Router\Service\ParameterTransformerFactoryService;
use ReflectionException;
use ReflectionMethod;

abstract class AbstractResponseController extends AbstractController
{
    protected ReflectionMethod $reflectionMethod;

    /**
     * @throws MethodNotFoundException
     */
    public function __construct()
    {
        try {
            $this->reflectionMethod = new ReflectionMethod($this, 'respond');
        } catch (ReflectionException) {
            throw new MethodNotFoundException(sprintf(
                '{38dd166b-f94d-4e8d-9e64-69be72d077f9} `%s` has to implement a `respond()` method.',
                static::class
            ));
        }

        di_tag(ParameterTransformerFactoryServiceInterface::class, ParameterTransformerFactoryService::class);
    }

    /**
     * @param RouterEventInterface $event
     * @throws DiException | FactoryException | NoInputException | NullValueException | ReflectionException | ReturnTypeException
     */
    public function process(RouterEventInterface $event): void
    {
        $argv = di_get(ParameterTransformer::class)
            ->callableTransform(
                $this->reflectionMethod,
                array_merge($event->getRequest()->getArgv(), $event->getRoute()->getParams()),
                $this->getParameterTransformerConfig($event),
            );

        $response = call_user_func_array([$this, 'respond'], $argv);

        if (!is_null($response) && !$response instanceof ResponseInterface) {
            throw new ReturnTypeException(sprintf(
                '{e16aa04d-c06f-4e85-b9b3-7cf5ad5b6989} `%s::respond()` has to return an instance of %s',
                static::class,
                ResponseInterface::class
            ));
        }

        $event->setResponse($response);
    }

    protected function getParameterTransformerConfig(RouterEventInterface $event): Configuration
    {
        return (new Configuration())
            ->setPredefinedTypeHints($this->getPredefinedTypeHints($event))
            ->setMapping($this->getInputKeyMapping($event))
            ->setNoInputIsAllowed(true)
            ->setNullIsAllowed(true)
        ;
    }

    /** @noinspection PhpUnusedParameterInspection */
    protected function getInputKeyMapping(RouterEventInterface $event): array
    {
        return [];
    }

    /** @noinspection PhpArrayShapeAttributeCanBeAddedInspection */
    protected function getPredefinedTypeHints(RouterEventInterface $event): array
    {
        return [
            RouterEventInterface::class => $event,
            RouteInformationInterface::class => $event->getRoute(),
            RequestInformationInterface::class => $event->getRequest(),
        ];
    }
}
