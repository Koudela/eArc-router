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

namespace eArc\Router\Traits;

/**
 * Implements the getters of the RouteInformationInterface
 */
trait RouteInformationTrait
{
    /** @var array */
    protected $realArgv = [];

    /** @var array */
    protected $virtualArgv = [];

    /**
     * @inheritdoc
     */
    public function cntRealArgv(): int
    {
        return count($this->realArgv);
    }

    /**
     * @inheritdoc
     */
    public function getRealArg(int $pos): ?string
    {
        return isset($this->realArgv[$pos]) ? $this->realArgv[$pos] : null;
    }

    /**
     * @inheritdoc
     */
    public function getRealArgv(): array
    {
        return $this->realArgv;
    }

    /**
     * @inheritdoc
     */
    public function cntVirtualArgv(): int
    {
        return count($this->virtualArgv);
    }

    /**
     * @inheritdoc
     */
    public function getVirtualArg(int $pos): ?string
    {
        return isset($this->virtualArgv[$pos]) ? $this->virtualArgv[$pos] : null;
    }

    /**
     * @inheritdoc
     */
    public function getVirtualArgv(): array
    {
        return $this->virtualArgv;
    }
}
