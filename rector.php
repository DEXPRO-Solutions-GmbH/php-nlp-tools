<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector;
use Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Rector\Visibility\Rector\ClassConst\ChangeConstantVisibilityRector;
use Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector;
use Rector\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector;
use Rector\CodingStyle\Rector\FuncCall\StrictArraySearchRector;
use Rector\CodingStyle\Rector\String_\SymplifyQuoteEscapeRector;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector;
use Rector\PHPUnit\Set\PHPUnitSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    // uncomment to reach your current PHP version
    ->withPhpSets()
    ->withRules([
        AddVoidReturnTypeWhereNoReturnRector::class,
        ChangeConstantVisibilityRector::class,
        RenameForeachValueVariableToMatchExprVariableRector::class,
        ReturnTypeFromReturnNewRector::class,
        CountArrayToEmptyArrayComparisonRector::class,
        StrictArraySearchRector::class,
        SymplifyQuoteEscapeRector::class,
        DeclareStrictTypesRector::class,
    ])
    ->withSets([
        PHPUnitSetList::PHPUNIT_110,
    ])
    ->withPhpSets()
    ->withPHPStanConfigs(['phpstan.neon'])
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        privatization: true,
        naming: true,
        instanceOf: true,
        earlyReturn: true,
        strictBooleans: true
    )
    ->withSkip([
        __DIR__ . '/tests/sentiment_maxent.php',
        // PorterStemmer::step4() ends with a statement after the `switch` that the rule wrongly treats as
        // unreachable, because every case leaves the switch with `return` or `break`. Applying it silently
        // stops the stemmer from removing -ement, -ism, ... (see PorterStemmerTest).
        RemoveUnreachableStatementRector::class => [
            __DIR__ . '/src/NlpTools/Stemmers/PorterStemmer.php',
        ],
        // The property names (and their order) of TrainingDocument are the serialization format of
        // TrainingSet files that applications keep on disk; promoting or renaming them breaks loading
        // those files (see TrainingSetSerializationTest).
        ClassPropertyAssignToConstructorPromotionRector::class => [
            __DIR__ . '/src/NlpTools/Documents/TrainingDocument.php',
        ],
        RenamePropertyToMatchTypeRector::class => [
            __DIR__ . '/src/NlpTools/Documents/TrainingDocument.php',
        ],
    ]);
