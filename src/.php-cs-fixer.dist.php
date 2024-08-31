<?php

$finder = (new PhpCsFixer\Finder())
    ->exclude('public')
    ->exclude('vendor')
    ->exclude('storage')
    ->exclude('bootstrap')
    ->exclude('database')
    ->notPath('server.php')
    ->in(__DIR__);

return (new PhpCsFixer\Config())
    ->setRules([
        // PSR2 Level
        '@PSR2' => true,
        // PHP arrays should be declared using the short syntax.
        'array_syntax' => ['syntax' => 'short'],
        // Binary operators should be surrounded by at least one space.
        'binary_operator_spaces' => true,
        // Ensure there is no code on the same line as the PHP open tag and it is followed by a blankline.
        'blank_line_after_opening_tag' => true,
        // An empty line feed should precede a return statement.
        'blank_line_before_statement' => ['statements' => ['return']],
        // A single space should be between cast and variable.
        'cast_spaces' => true,
        // No whitespace for concatenation.
        'concat_space' => ['spacing' => 'none'],
        // Include/Require and file path should be divided with a single space. File path should not be placed under brackets.
        'include' => true,
        // There should be no empty lines after class opening brace.
        'no_blank_lines_after_class_opening' => true,
        // There should not be blank lines between docblock and the documented element.
        'no_blank_lines_after_phpdoc' => true,
        // Remove duplicated semicolons.
        'no_empty_statement' => true,
        // Removes extra consecutive blank lines.
        'no_extra_blank_lines' => ['tokens' => ['use']],
        // Remove leading slashes in use clauses.
        'no_leading_import_slash' => true,
        // The namespace declaration line shouldn't contain leading whitespace.
        'no_leading_namespace_whitespace' => true,
        // No multiline whitespace around the double arrow.
        'no_multiline_whitespace_around_double_arrow' => true,
        // Multi-line whitespace before closing semicolon are prohibited.
        'multiline_whitespace_before_semicolons' => true,
        // Replace short-echo <?= with long format <?php echo syntax.
        'echo_tag_syntax' => ['format' => 'long'],
        // Single-line whitespace before closing semicolon are prohibited.
        'no_singleline_whitespace_before_semicolons' => true,
        // Remove trailing commas in list function calls.
        'no_trailing_comma_in_list_call' => true,
        // PHP single-line arrays should not have trailing comma.
        'no_trailing_comma_in_singleline_array' => true,
        // Unused use statements must be removed.
        'no_unused_imports' => true,
        // Remove trailing whitespace at the end of blank lines.
        'no_whitespace_in_blank_line' => true,
        // Logical NOT operators (!) should have one trailing whitespace.
        'not_operator_with_successor_space' => true,
        // There should not be space before or after object T_OBJECT_OPERATOR ->.
        'object_operator_without_whitespace' => true,
        // Use statements must be in order.
        'ordered_imports' => true,
        // Docblocks should have the same indentation as the documented subject.
        'phpdoc_indent' => true,
        // Fix phpdoc inline tags, make inheritdoc always inline.
        'general_phpdoc_tag_rename' => true,
        'phpdoc_inline_tag_normalizer' => true,
        'phpdoc_tag_type' => true,
        // @access annotations should be omitted from phpdocs.
        'phpdoc_no_access' => true,
        // No alias PHPDoc tags should be used.
        'phpdoc_no_alias_tag' => ['replacements' => ['type' => 'var']],
        // @package and @subpackage annotations should be omitted from phpdocs.
        'phpdoc_no_package' => true,
        // Scalar types should always be written in the same form. int not integer, bool not boolean, float not real or double.
        'phpdoc_scalar' => true,
        // Phpdocs summary should end in either a full stop, exclamation mark, or question mark.
        'phpdoc_summary' => true,
        // Docblocks should only be used on structural elements.
        'phpdoc_to_comment' => true,
        // Phpdocs should start and end with content, excluding the very first and last line of the docblocks.
        'phpdoc_trim' => true,
        // @var and @type annotations should not contain the variable name.
        'phpdoc_var_without_name' => true,
        // There should be exactly one blank line before a namespace declaration.
        'single_blank_line_before_namespace' => true,
        // Convert double quotes to single quotes for simple strings.
        'single_quote' => true,
        // Replace all <> with !=.
        'standardize_not_equals' => true,
        // Standardize spaces around ternary operator.
        'ternary_operator_spaces' => true,
        // PHP multi-line arrays should have a trailing comma.
        'trailing_comma_in_multiline' => ['elements' => ['arrays']],
        // Arrays should be formatted like function/method arguments, without leading or trailing single line space.
        'trim_array_spaces' => true,
        // Unary operators should be placed adjacent to their operands.
        'unary_operator_spaces' => true,
    ])
    ->setFinder($finder);
