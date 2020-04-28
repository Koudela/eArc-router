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

    public function __construct(?string $type = null, ?array $argv = null)
    {
        $this->type = $type ?? ($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->argv = $argv ?? $_REQUEST;
    }
}
