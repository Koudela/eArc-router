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

use eArc\Router\Exceptions\MethodNotFoundException;
use eArc\Router\Exceptions\ReturnTypeException;
use eArc\Router\Exceptions\TypeHintException;
use eArc\Router\Interfaces\ParameterFactoryInterface;
use eArc\Router\Interfaces\RequestInformationInterface;
use eArc\Router\Interfaces\ResponseInterface;
use eArc\Router\Interfaces\RouteInformationInterface;
use eArc\Router\Interfaces\RouterEventInterface;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;

abstract class AbstractResponseController extends AbstractController
{
    const USE_REQUEST_KEYS = [];

    /** @var ReflectionMethod */
    protected $reflectionMethod;

    /**
     * @throws MethodNotFoundException
     */
    public function __construct()
    {
        try {
            $this->reflectionMethod = new ReflectionMethod($this, 'respond');
        } catch (ReflectionException $exception) {
            throw new MethodNotFoundException(sprintf(
                '{38dd166b-f94d-4e8d-9e64-69be72d077f9} `%s` has to implement a `respond()` method.',
                static::class
            ));
        }
    }

    /**
     * @throws ReturnTypeException
     * @throws TypeHintException
     */
    public function process(RouterEventInterface $event): void
    {
        $argv = [];
        $pos = 0;

        foreach ($this->reflectionMethod->getParameters() as $parameter) {
            $argv[] = $this->transform($event, $pos, $parameter);
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $response = $this->respond(...$argv);

        if (!is_null($response) && !$response instanceof ResponseInterface) {
            throw new ReturnTypeException(sprintf(
                '{e16aa04d-c06f-4e85-b9b3-7cf5ad5b6989} `%s::respond()` has to return an instance of %s',
                static::class,
                ResponseInterface::class
            ));
        }

        $event->setResponse($response);
    }

    /**
     * @throws TypeHintException
     */
    protected function transform(RouterEventInterface $event, int &$pos, ReflectionParameter $parameter)
    {
        $type = $parameter->getType();

        if (class_exists(ReflectionUnionType::class) && $type instanceof ReflectionUnionType) {
            throw new TypeHintException(
                '{083859dc-41ee-435a-b1c0-40784c0bff7f} AbstractResponseControllers::respond() does not support union-type type hints.'
            );
        }

        $name = $type instanceof ReflectionNamedType ? $type->getName() : '';

        if (is_a($name, RouterEventInterface::class, true)
            || is_a($name, RouteInformationInterface::class, true)
            || is_a($name, RequestInformationInterface::class, true)) {
            $value = null;
        } else {
            $requestKeysCnt = count(static::USE_REQUEST_KEYS);
            $value = $requestKeysCnt > $pos ?
                $event->getRequest()->getArg(static::USE_REQUEST_KEYS[$pos]) :
                $event->getRoute()->getParam($pos - $requestKeysCnt);
            $pos++;

            if ('null' === $value && $type->allowsNull()) {
                $value = null;
            }

            if (is_null($value)) {
                if ($parameter->isDefaultValueAvailable()) {
                    try {
                        return $parameter->getDefaultValue();
                    } catch (ReflectionException $exception) {
                        return null;
                    }
                }
            }
        }

        if ($type->isBuiltin()) {
            switch ($name) {
                case 'int':case 'integer': return (int) $value;
                case 'bool':case 'boolean': return (bool) $value;
                case 'float':case 'double':case 'real':  return (float) $value;
                default: return $value;
            }
        }

        $transformedName = $this->transformSpecialName($name);

        if (is_a($transformedName, RouterEventInterface::class, true)) {
            return $event;
        }

        if (is_a($transformedName, RouteInformationInterface::class, true)) {
            return $event->getRoute();
        }

        if (is_a($transformedName, RequestInformationInterface::class, true)) {
            return $event->getRequest();
        }

        if (is_a($transformedName, ParameterFactoryInterface::class, true)) {
            return $transformedName::buildFromParameter($value);
        }

        /** @noinspection PhpUndefinedClassInspection */
        /** @noinspection PhpUndefinedNamespaceInspection */
        if (function_exists('data_load')
            && interface_exists(eArc\Data\Entity\Interfaces\EntityInterface::class)
            && is_a($name, eArc\Data\Entity\Interfaces\EntityInterface::class)
        ) {
            $entity = data_load($name, $value);

            return !is_null($entity) || $type->allowsNull() ? $entity : $value;
        }

        return $value;
    }

    protected function transformSpecialName(string $name): string
    {
        if ('parent' === $name) {
            return $this->reflectionMethod->getDeclaringClass()->getParentClass()->getName();
        }

        if ('self' === $name) {
            return $this->reflectionMethod->getDeclaringClass()->getName();
        }

        return $name;
    }
}
