<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 * router component
 *
 * @package earc/router
 * @link https://github.com/Koudela/earc-router/
 * @copyright Copyright (c) 2018-2021 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\Router\Service;

use eArc\ParameterTransformer\Interfaces\ParameterTransformerFactoryServiceInterface;

class ParameterTransformerFactoryService implements ParameterTransformerFactoryServiceInterface
{
    public function buildFromParameter(string $fQCN, $parameter): object|null
    {
        /** @noinspection PhpUndefinedClassInspection */
        /** @noinspection PhpUndefinedNamespaceInspection */
        if (function_exists('data_load')
            && interface_exists(eArc\Data\Entity\Interfaces\EntityInterface::class)
            && is_a($fQCN, eArc\Data\Entity\Interfaces\EntityInterface::class)
        ) {
            return data_load($fQCN, $parameter);
        }

        return null;
    }
}
