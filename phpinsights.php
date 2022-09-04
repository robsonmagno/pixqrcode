<?php

declare(strict_types=1);


return [
    'preset' => 'default',

    'exclude' => [
        'phpinsights.php',
        'vendor',
        'config',
        'bootstrap',
        'resources',
        'storage',
        'public',
        'tests',
    ],

    'add' => [
        
    ],

    'remove' => [
        AlphabeticallySortedUsesSniff::class,
        DeclareStrictTypesSniff::class,
        DisallowMixedTypeHintSniff::class,
        ForbiddenDefineFunctions::class,
        ForbiddenNormalClasses::class,
        ForbiddenTraits::class,
        ParameterTypeHintSniff::class,
        PropertyTypeHintSniff::class,
        ReturnTypeHintSniff::class,
        UselessFunctionDocCommentSniff::class,
        UnusedParameterSniff::class,
        LineLengthSniff::class,
        DocCommentSpacingSniff::class,
        ClassInstantiationSniff::class,
        NewWithBracesFixer::class,
        NullableTypeForNullDefaultValueSniff::class,
        DisallowArrayTypeHintSyntaxSniff::class,
        NoEmptyCommentFixer::class,
        DisallowShortTernaryOperatorSniff::class,
        ForbiddenPublicPropertySniff::class,
        DisallowEmptySniff::class
    ],

    'config' => [
        Rmagnoprado\Pixqrcode\Payload::class => [
            'maxComplexity' => 7,
        ],
/*
        FunctionLengthSniff::class => [
            'maxLength' => 30,
        ],

        MethodPerClassLimitSniff::class => [
            'maxCount' => 12,
        ],*/
    ]
];