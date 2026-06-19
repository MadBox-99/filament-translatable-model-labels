<?php

declare(strict_types=1);

use MadBox99\FilamentTranslatableModelLabels\Rector\AddTranslatesFilamentModelLabelsTraitRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withImportNames(importShortClasses: false, removeUnusedImports: false)
    ->withRules([AddTranslatesFilamentModelLabelsTraitRector::class]);
