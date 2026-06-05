<?php

declare(strict_types=1);

return new PhpCsFixer\Config()
    ->setRiskyAllowed(true)
    ->setRules(
        [
            '@PER-CS2.0' => true,
            '@PER-CS2.0:risky' => true,
            'declare_strict_types' => true,
            'ordered_imports' => ['sort_algorithm' => 'alpha', 'imports_order' => ['class', 'function', 'const']],
            'no_unused_imports' => true,
            'trailing_comma_in_multiline' => ['elements' => ['arrays', 'arguments', 'parameters']],
        ]
    )
    ->setFinder(
        new PhpCsFixer\Finder()
            ->in(
                [
                    __DIR__ . '/src',
                    __DIR__ . '/tests',
                ]
            )
    )
    ->setCacheFile(__DIR__ . '/.php-cs-fixer.cache');
