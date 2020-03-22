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
    protected $url;

    /**
     * @param string $url
     */
    public function __construct(string $url)
    {
        $this->url = $url;

        $this->init();
    }

    /**
     * Calculate the route and route parameters.
     *
     * @return void
     */
    protected function init(): void
    {
        chdir(di_param('earc.project_dir'));
        chdir(di_param('earc.observer_tree.root_directory'));
        chdir(di_param('earc.router.directory'));

        $this->virtualArgs = explode('/', trim($this->url, '/'));

        while ($param = array_shift($this->virtualArgs))
        {
            if (is_dir($param))
            {
                array_unshift($this->virtualArgs, $param);
                break;
            }

            chdir($param);

            $this->realArgs[] = $param;
        }
    }
}
