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

use eArc\EventTree\Util\CompositeDir;
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
     */
    public function __construct(string $path)
    {
        $this->path = $path;

        $this->getRouteInformation();
    }

    /**
     * Calculate the route and route parameters.
     *
     * @return void
     */
    protected function getRouteInformation(): void
    {
        $path = 'routing';

        $this->virtualArgv = explode('/', '/'.trim($this->path, '/'));

        $param = array_shift($this->virtualArgv);

        do
        {
            if ($param) {
                $path .= '/' . $param;

                $this->realArgv[] = $param;
            }

            if (empty(CompositeDir::getSubDirNames($path)))
            {
                break;
            }
        }
        while ($param = array_shift($this->virtualArgv));
    }
}
