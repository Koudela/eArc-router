<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 * router component
 *
 * @package earc/router
 * @link https://github.com/Koudela/earc-router/
 * @copyright Copyright (c) 2018-2020 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\Router\Immutables;

use eArc\EventTree\Exceptions\InvalidObserverNodeException;
use eArc\EventTree\Util\CompositeDir;
use eArc\EventTree\Util\DirectiveReader;
use eArc\Router\Interfaces\RouteInformationInterface;
use eArc\Router\Traits\RouteInformationTrait;

/**
 * The Route class processes the url to extract information about the
 * associated routes. The object is immutable and exposes its information as
 * described in the RouteInformationInterface.
 */
class Route implements RouteInformationInterface
{
    use RouteInformationTrait;

    /** @var string */
    protected $path;

    /**
     * @param string $path
     * @param string $routingEventTreeDir
     */
    public function __construct(string $path, string $routingEventTreeDir)
    {
        $this->path = $path;

        $this->getRouteInformation($routingEventTreeDir);
    }

    /**
     * Calculate the route and route parameters.
     *
     * @param string $routingEventTreeDir
     *
     * @return void
     */
    protected function getRouteInformation(string $routingEventTreeDir): void
    {
        $dirFactory = di_static(CompositeDir::class);
        $directiveReader = di_static(DirectiveReader::class);

        $path = $routingEventTreeDir;

        $this->params = explode('/', '/'.trim($this->path, '/'));

        $param = array_shift($this->params);

        do
        {
            if ($param) {
                try {
                    $path = $directiveReader::getRedirect($path)->getPathForLeaf($param);
                } catch (InvalidObserverNodeException $exception) {
                    $path .= '/'.$param;
                }

                $this->dirs[] = $param;
            }

            if (empty($dirFactory::getSubDirNames($path)))
            {
                break;
            }
        }
        while ($param = array_shift($this->params));
    }
}
