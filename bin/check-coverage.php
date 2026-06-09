#!/usr/bin/env php
<?php

declare(strict_types=1);

[, $file, $threshold] = $argv + [null, null, null];
if ($file === null || $threshold === null) {
    fwrite(STDERR, "Usage: check-coverage.php <clover.xml> <min-percent>\n");
    exit(1);
}

$xml = simplexml_load_file($file);
if ($xml === false) {
    fwrite(STDERR, "Could not read {$file}\n");
    exit(1);
}

$metrics = $xml->project->metrics;
$total = (int) $metrics['statements'];
$covered = (int) $metrics['coveredstatements'];

if ($total === 0) {
    fwrite(STDERR, "No statements found in {$file}\n");
    exit(1);
}

$coverage = $covered / $total * 100;
$min = (float) $threshold;

printf("Line coverage: %.2f%% (%d/%d), required: %.2f%%\n", $coverage, $covered, $total, $min);

if ($coverage < $min) {
    exit(1);
}