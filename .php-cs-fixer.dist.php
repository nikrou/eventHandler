<?php
/*
 *  -- BEGIN LICENSE BLOCK ----------------------------------
 *
 *  This file is part of eventHandler, a plugin for Dotclear 2.
 *
 *  Copyright(c) 2014-2023 Nicolas Roudaire <nikrou77@gmail.com> https://www.nikrou.net
 *
 *  Copyright (c) 2009-2013 Jean-Christian Denis and contributors
 *  contact@jcdenis.fr https://chez.jcdenis.fr/
 *
 *  Licensed under the GPL version 2.0 license.
 *  A copy of this license is available in LICENSE file or at
 *  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 *  -- END LICENSE BLOCK ------------------------------------
 */

// the final goal is to respect all PSR-2 rules.

$header = <<<'EOF'
     -- BEGIN LICENSE BLOCK ----------------------------------

     This file is part of eventHandler, a plugin for Dotclear 2.

     Copyright(c) 2014-2023 Nicolas Roudaire <nikrou77@gmail.com> https://www.nikrou.net

     Copyright (c) 2009-2013 Jean-Christian Denis and contributors
     contact@jcdenis.fr https://chez.jcdenis.fr/

     Licensed under the GPL version 2.0 license.
     A copy of this license is available in LICENSE file or at
     http://www.gnu.org/licenses/old-licenses/gpl-2.0.html

     -- END LICENSE BLOCK ------------------------------------
    EOF;

$finder = PhpCsFixer\Finder::create()->in(__DIR__);

$config = new PhpCsFixer\Config();

return $config
->setRules([
    '@PER-CS' => true,
    '@PHP82Migration' => true,
    'header_comment' => ['comment_type' => 'comment', 'header' => $header, 'location' => 'after_open', 'separate' => 'bottom'],
    'no_unused_imports' => true,
    'no_extra_blank_lines' => ['tokens' => ['extra', 'curly_brace_block', 'parenthesis_brace_block', 'square_brace_block', 'use']],

    // arrays
    'array_indentation' => true,
    'no_multiline_whitespace_around_double_arrow' => true,
    'trim_array_spaces' => true,
    'whitespace_after_comma_in_array' => true,

    // spaces
    'binary_operator_spaces' => ['operators' => ['=>' => 'single_space', '=' => 'single_space']],
    'concat_space' => ['spacing' => 'one'],
    'no_spaces_inside_parenthesis' => true,
])
->setFinder($finder);

//     // class
//     'class_attributes_separation' => ['elements' => ['method' => 'one']],
//     'class_definition' => ['single_line' => true],
//     'method_argument_space' => true,

//     // comments
//     'align_multiline_comment' => ['comment_type' => 'all_multiline'],
//     'no_trailing_whitespace' => true,
//     'single_line_comment_style' => true,

//     // global
//     'braces' => [
//         'position_after_control_structures' => 'same',
//         'position_after_functions_and_oop_constructs' => 'next',
//     ],
//     'constant_case' => true,
//     'combine_consecutive_issets' => true,
//     'combine_consecutive_unsets' => true,
//     // 'encoding' => true,
//     'elseif' => true,
//     // 'full_opening_tag' => true,
//     'heredoc_to_nowdoc' => true,
//     'lowercase_cast' => true,
//     'lowercase_keywords' => true,
//     'no_closing_tag' => true,
//     'no_leading_import_slash' => true,
//     'single_blank_line_at_eof' => true,
