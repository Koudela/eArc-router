<?php
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/router
 * @link https://github.com/Koudela/earc-router/
 * @copyright Copyright (c) 2018 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\router;

use \eArc\core\exceptions\NoControllerFoundException;

/**
 * The Router class processes the request to extract information about the
 * associated routes.
 */
class Router extends AbstractRouter
{
    protected $url;
    protected $routingBasePath;
    protected $requestNames = [
        'GET' => ['get.php', 'main.php'],
        'HEAD' => ['head.php', 'get.php', 'main.php'],
        'POST' => ['post.php', 'main.php'],
        'PUT' => ['put.php', 'main.php'],
        'PATCH' => ['patch.php', 'main.php'],
        'DELETE' => ['delete.php', 'main.php']
    ];

    /**
     * Constructor
     *
     * @param string $routingBasePath
     * @param string $requestType
     * @param string $url
     * @param array|null $requestNames
     * @throws \RuntimeException if the routingBasePath is not a directory or an
     * unsupported requestType (REQUEST_METHOD) is used
     * @throws NoControllerFoundException if the url maps to no main controller
     */
    public function __construct(string $routingBasePath, string $requestType, string $url, ?array $requestNames)
    {
        if (!\is_dir($routingBasePath)) {
            throw new \RuntimeException('Not a directory: ' . $routingBasePath);
        }

        if ($requestNames) $this->requestNames = $requestNames;
        if (!isset($this->requestNames[$requestType])) {
            throw new \RuntimeException('Unknown request type: ' . $requestType);
        }

        $this->routingBasePath = $routingBasePath;
        $this->requestType = $requestType;
        $this->url = $this->normalizeUrl($url);

        $this->createRoute();
        $this->setAccessControllers();
    }

    /**
     * Normalize the given url string, such that it starts with a slash and
     * ends with no slash. (e.g. /a/route/to/somewhere)
     *
     * @param string $url
     * @return string
     */
    private function normalizeUrl(string $url): string
    {
        if ($url[0] !== '/') $url = '/' . $url;

        while (\substr($url, -1) === '/') {
            $url = \substr($url, 0, -1);
        }

        return $url;
    }

    /**
     * Set the access controller closures. 
     *
     * @return void
     */
    private function setAccessControllers(): void
    {
        $this->absolutePathToAccessControllers = [];
        $route = $this->routingBasePath;

        foreach ($this->getRealArgs() as $arg)
        {
            $route .= $arg . '/';
            $path = $route . 'access.php';

            if (\is_file($path)) {
                array_unshift($this->absolutePathToAccessControllers, $path);
                break;
            }
        }
    }

    /**
     * Calculate the route and route parameters.
     *
     * @throws NoControllerFoundException if no route maps to a main controller
     * @return void
     */
    private function createRoute(): void
    {
        $this->virtualArgs = [];
        $this->realArgs = \explode('/', $this->url);

        do {
            if ($this->setMainController(
                $this->routingBasePath . implode('/', $this->realArgs)
            )) return;

            array_unshift($this->virtualArgs, array_pop($this->realArgs));
        }
        while (count($this->realArgs) > 0);

        throw new NoControllerFoundException();
    }

    /**
     * Set the main controller closure to the most fitting controller.
     *
     * @param string $route
     * 
     * @return bool true if a controller was found, false otherwise
     */
    private function setMainController(string $route): bool
    {
        if (!\is_dir($route)) return false;

        foreach ($this->requestNames[$this->requestType] as $requestName)
        {
            $path = $route . '/' . $requestName;

            if (\is_file($path)) {
                $this->absolutePathToMainController = $path;
                return true;
            }
        }
        return false;
    }
}
