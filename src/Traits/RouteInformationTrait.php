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
    protected $dirs = [];

    /** @var array */
    protected $params = [];

    /**
     * @inheritdoc
     */
    public function cntDirs(): int
    {
        return count($this->dirs);
    }

    /**
     * @inheritdoc
     */
    public function getDir(int $pos): ?string
    {
        return isset($this->dirs[$pos]) ? $this->dirs[$pos] : null;
    }

    /**
     * @inheritdoc
     */
    public function getDirs(): array
    {
        return $this->dirs;
    }

    /**
     * @inheritdoc
     */
    public function cntParams(): int
    {
        return count($this->params);
    }

    /**
     * @inheritdoc
     */
    public function getParam(int $pos): ?string
    {
        return isset($this->params[$pos]) ? $this->params[$pos] : null;
    }

    /**
     * @inheritdoc
     */
    public function getParams(): array
    {
        return $this->params;
    }
}
