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

namespace eArc\Router\Immutables;

use eArc\Router\Interfaces\RequestInformationInterface;
use eArc\Router\Traits\RequestInformationTrait;

/**
 * The Request class processes the request to extract information about the
 * request. The object is immutable and exposes its information as described in
 * the RequestInformationInterface.
 */
class Request implements RequestInformationInterface
{
    use RequestInformationTrait;

    /**
     * @param array|null $requestArgs
     * @param string $requestType
     */
    public function __construct(array $requestArgs = null, string $requestType = 'GET')
    {
        $this->requestType = $requestType;
        $this->requestArgs = $requestArgs ?? $this->importRequestArgs();
    }

    /**
     * Imports the basic request variables.
     *
     * @return array
     */
    protected function importRequestArgs(): array
    {
        $type = '_'.$this->requestType;

        if (!isset($$type) || !is_array($$type)) {
            return [];
        }

        $requestArgs = [];

        foreach ($$type as $key => $value)
        {
            if ($arg = filter_input('INPUT' . $type, $key, FILTER_UNSAFE_RAW))
            {
                $requestArgs[$key] = $arg;
            }
        }

        return $requestArgs;
    }
}
