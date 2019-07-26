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

namespace eArc\Router\Traits;

/**
 * Implements the getters of the RequestInformationInterface
 */
trait RequestInformationTrait
{
    /** @var string */
    protected $requestType = '';

    /** @var array */
    protected $requestArgs = [];

    /**
     * @inheritdoc
     */
    public function getRequestType(): string
    {
        return $this->requestType;
    }

    /**
     * @inheritdoc
     */
    public function hasRequestArg(string $name): bool
    {
        return isset($this->requestArgs[$name]);
    }

    /**
     * @inheritdoc
     */
    public function getRequestArg(string $name)
    {
        return $this->requestArgs[$name];
    }

    /**
     * @inheritdoc
     */
    public function getRequestArgs(): array
    {
        return $this->requestArgs;
    }
}
