<?php

$finder = (new PhpCsFixer\Finder())->in(__DIR__);

return (new PhpCsFixer\Config())
    ->setRules(
        [
            '@PSR12'   => true,
            '@Symfony' => true,
            // Only allowed if risky rules are enabled: 'strict_param' => true,
            'array_indentation'                                => true,
            'array_syntax'                                     => ['syntax' => 'short'],
            'binary_operator_spaces'                           => ['operators' => ['=>' => 'align_single_space_minimal']],
            'concat_space'                                     => ['spacing' => 'one'],
            'global_namespace_import'                          => ['import_classes' => true, 'import_constants' => true, 'import_functions' => true],
            'method_argument_space'                            => ['on_multiline' => 'ensure_fully_multiline'],
            'method_chaining_indentation'                      => true,
            'no_alias_language_construct_call'                 => false,
            'no_unneeded_control_parentheses'                  => false,
            'nullable_type_declaration_for_default_null_value' => ['use_nullable_type_declaration' => true],
            'phpdoc_annotation_without_dot'                    => true,
            'phpdoc_summary'                                   => false,
            'simplified_if_return'                             => true,
            'single_line_throw'                                => false,
        ],
    )

    ->setFinder($finder)
;
