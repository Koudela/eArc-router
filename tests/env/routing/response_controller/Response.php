<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 * event tree component
 *
 * @package earc/event-tree
 * @link https://github.com/Koudela/eArc-eventTree/
 * @copyright Copyright (c) 2018-2021 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\RouterTests\env\routing\response_controller;

use eArc\Router\Interfaces\ResponseInterface;

class Response implements ResponseInterface
{
    public array $response;

    public function __construct(array $response)
    {
        $this->response = $response;
    }
}
