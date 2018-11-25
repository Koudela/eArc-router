<?php
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/router
 * @link https://github.com/Koudela/earc-router/
 * @copyright Copyright (c) 2018 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\Router\Immutables;

use eArc\eventTree\Tree\ObserverLeaf;
use eArc\eventTree\Tree\ObserverTree;
use eArc\Router\Api\RouteInformationInterface;
use eArc\Router\Traits\RouteInformationTrait;

/**
 * The Route class processes the url to extract information about the
 * associated routes. The object is immutable and exposes its information as
 * described in the RouteInformationInterface.
 */
class Route implements RouteInformationInterface
{
    use RouteInformationTrait;

    /** @var ObserverTree */
    protected $observerTree;

    /** @var string */
    protected $url;

    /**
     * @param ObserverTree $observerTree
     * @param string $url
     */
    public function __construct(ObserverTree $observerTree, string $url)
    {
        $this->observerTree = $observerTree;
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
        /** @var ObserverLeaf $leaf */
        $leaf = $this->observerTree;

        $this->virtualArgs = \explode('/', \trim($this->url, '/'));

        while ($param = array_shift($this->virtualArgs))
        {
            if ($leaf = $leaf->getChild($param))
            {
                $this->realArgs[] = $param;
                continue;
            }
            array_unshift($this->virtualArgs, $param);
            break;
        }
    }
}
