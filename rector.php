<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Ternary\TernaryToBooleanOrFalseToBooleanAndRector;
use Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector;

return RectorConfig::configure()
    ->withPaths(
        [
            __DIR__ . '/packages/kernel/src',
            __DIR__ . '/packages/kernel/tests',
            __DIR__ . '/packages/phpstan-rules/src',
            __DIR__ . '/packages/phpstan-rules/tests',
        ]
    )
    ->withCache(cacheDirectory: __DIR__ . '/.rector.cache')
    ->withImportNames(removeUnusedImports: true)
    ->withSkip(
        [
            RemoveExtraParametersRector::class,
            TernaryToBooleanOrFalseToBooleanAndRector::class,
            SimplifyUselessVariableRector::class,
            // NewlineBetweenClassLikeStmtsRector — add once exact FQCN is verified on first run.
        ]
    )
    ->withPreparedSets(codingStyle: true)
    ->withDeadCodeLevel(47)
    ->withCodeQualityLevel(40)
    ->withTypeCoverageLevel(20);
