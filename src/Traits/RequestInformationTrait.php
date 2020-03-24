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
 * Implements the getters of the RequestInformationInterface
 */
trait RequestInformationTrait
{
    /** @var string */
    protected $type = '';

    /** @var array */
    protected $argv = [];

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function hasArg(string $name): bool
    {
        return isset($this->argv[$name]);
    }

    /**
     * @inheritdoc
     */
    public function getArg(string $name)
    {
        return $this->argv[$name];
    }

    /**
     * @inheritdoc
     */
    public function getArgv(): array
    {
        return $this->argv;
    }
}
