<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector;
use Rector\CodingStyle\Rector\ClassLike\NewlineBetweenClassLikeStmtsRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Ternary\TernaryToBooleanOrFalseToBooleanAndRector;
use Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector;

return RectorConfig::configure()
    ->withPaths(
        [
            __DIR__ . '/src',
            __DIR__ . '/tests',
        ],
    )
    ->withCache(cacheDirectory: __DIR__ . '/.rector.cache')
    ->withImportNames(removeUnusedImports: true)
    ->withSkip(
        [
            RemoveExtraParametersRector::class,
            TernaryToBooleanOrFalseToBooleanAndRector::class,
            SimplifyUselessVariableRector::class,
            NewlineBetweenClassLikeStmtsRector::class,
        ],
    )
    ->withPreparedSets(codingStyle: true)
    ->withDeadCodeLevel(47)
    ->withCodeQualityLevel(40)
    ->withTypeCoverageLevel(20);
