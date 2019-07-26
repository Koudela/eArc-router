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
 * Implements the getters of the RouteInformationInterface
 */
trait RouteInformationTrait
{
    /** @var array */
    protected $realArgs = [];

    /** @var array */
    protected $virtualArgs = [];

    /**
     * @inheritdoc
     */
    public function cntRealArgs(): int
    {
        return count($this->realArgs);
    }

    /**
     * @inheritdoc
     */
    public function getRealArg(int $pos): ?string
    {
        return isset($this->realArgs[$pos]) ? $this->realArgs[$pos] : null;
    }

    /**
     * @inheritdoc
     */
    public function getRealArgs(): array
    {
        return $this->realArgs;
    }

    /**
     * @inheritdoc
     */
    public function cntVirtualArgs(): int
    {
        return count($this->virtualArgs);
    }

    /**
     * @inheritdoc
     */
    public function getVirtualArg(int $pos): ?string
    {
        return isset($this->virtualArgs[$pos]) ? $this->virtualArgs[$pos] : null;
    }

    /**
     * @inheritdoc
     */
    public function getVirtualArgs(): array
    {
        return $this->virtualArgs;
    }
}
