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
use eArc\Router\Interfaces\ParameterFactoryInterface;
use eArc\Router\Interfaces\RequestInformationInterface;
use eArc\Router\Interfaces\ResponseInterface;
use eArc\Router\Interfaces\RouteInformationInterface;
use eArc\Router\Interfaces\RouterEventInterface;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use Throwable;

abstract class AbstractResponseController extends AbstractController
{
    const USE_REQUEST_KEYS = null;

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
            throw new MethodNotFoundException(sprintf('`%s` has to implement a `respond()` method.', static::class));
        }
    }

    /**
     * @param RouterEventInterface $event
     *
     * @throws ReturnTypeException
     */
    public function process(RouterEventInterface $event): void
    {
        $argv = [];
        $pos = 0;

        foreach ($this->reflectionMethod->getParameters() as $parameter) {
            $name = $parameter->getType()->getName();
            if (is_a($name, RouterEventInterface::class, true)
                || is_a($name, RouteInformationInterface::class, true)
                || is_a($name, RequestInformationInterface::class, true)) {
                $pos--;
            }
            $argv[] = $this->transform($event, $pos++, $parameter);
        }

        $response = $this->respond(...$argv);

        if (!is_null($response) && !$response instanceof ResponseInterface) {
            throw new ReturnTypeException(sprintf('`%s::respond()` has to return an instance of %s', static::class, ResponseInterface::class));
        }

        $event->setResponse($response);
    }

    protected function transform(RouterEventInterface $event, int $pos, ReflectionParameter $parameter)
    {
        $requestKeysCnt = !empty(static::USE_REQUEST_KEYS) ? count(static::USE_REQUEST_KEYS) : 0;
        $value = $requestKeysCnt > max($pos, 0) ?
            $event->getRequest()->getArg(static::USE_REQUEST_KEYS[$pos]) :
            $event->getRoute()->getParam($pos - $requestKeysCnt);

        try {
            $type = $parameter->getType();
            if ('null' === $value) {
                if ($type->allowsNull()) {
                    return null;
                }

                if ($parameter->isDefaultValueAvailable()) {
                    return $parameter->getDefaultValue();
                }
            }

            if ($type->isBuiltin()) {
                switch ($type->getName()) {
                    case 'int':case 'integer': return (int) $value;
                    case 'bool':case 'boolean': return (bool) $value;
                    case 'float':case 'double':case 'real':  return (float) $value;
                    default: return $value;
                }
            }

            $name = $this->transformSpecialName($type->getName());

            if (is_a($name, RouterEventInterface::class, true)) {
                return $event;
            }

            if (is_a($name, RouteInformationInterface::class, true)) {
                return $event->getRoute();
            }

            if (is_a($name, RequestInformationInterface::class, true)) {
                return $event->getRequest();
            }

            if (is_a($name, ParameterFactoryInterface::class, true)) {
                return $name::buildFromParameter($value);
            }
        } catch (Throwable $throwable) {
            return $value;
        }

            return $value;
    }

    protected function transformSpecialName(string $name)
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
