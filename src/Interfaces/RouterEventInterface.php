<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 * router component
 *
 * @package earc/router
 * @link https://github.com/Koudela/earc-router/
 * @copyright Copyright (c) 2018-2019 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\router\Interfaces {

    use eArc\EventTree\Interfaces\TreeEventInterface;
    use eArc\router\Exceptions\NoRequestInformationException;

    interface RouterEventInterface extends TreeEventInterface
    {
        /**
         * Get the request information of the event.
         *
         * @param string $requestType
         *
         * @return RequestInformationInterface
         *
         * @throws NoRequestInformationException
         */
        public function getRequestInformation(string $requestType = 'GET'): RequestInformationInterface;

        /**
         * Get the route information of the event.
         *
         * @return RouteInformationInterface
         */
        public function getRouteInformation(): RouteInformationInterface;
    }

}

namespace {

    use eArc\router\Exceptions\NoRouteException;

    if (!function_exists('earc_route')) {
        /**
         * @param string $fQCN
         *
         * @return string
         *
         * @throws NoRouteException
         */
        function earc_route(string $fQCN) {
            $root = di_param('earc.observer_tree.root_directory');
            $namespace = di_param('earc.observer.tree.directories.'.$root)
                .'\\'.di_param('earc.router.directory');
            $names = explode('\\', $fQCN);
            array_pop($names);

            foreach (explode('\\', $namespace) as $name) {
                if (array_unshift($names) !== $name) {
                    throw new NoRouteException(sprintf('%s is no valid fully qualified controller class name.', $fQCN));
                }
            }

            return implode('/', $names);
        }
    }
}
