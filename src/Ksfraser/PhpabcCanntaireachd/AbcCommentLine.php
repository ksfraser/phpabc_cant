<?php
namespace Ksfraser\PhpabcCanntaireachd;

/**
 * @deprecated Use AbcComment instead. This class is retained for backward compatibility.
 *
 * Represents a single comment line in an ABC file.
 * Use AbcComment for all new code (handles both inline and full-line comments).
 *
 * @package Ksfraser\\PhpabcCanntaireachd
 */
class AbcCommentLine extends AbcItem {
    /**
     * @var string The comment text
     */
    protected $comment;

    /**
     * AbcCommentLine constructor.
     * @param string $comment The comment text, possibly with leading '%'
     */
    public function __construct($comment) {
        // Remove leading '%' and any whitespace after it
        $this->comment = ltrim(preg_replace('/^%+\s*/', '', $comment));
    }

    /**
     * Render the comment line as a string, prefixed with '%'.
     * @return string
     */
    protected function renderSelf(): string {
        return '%' . $this->comment . "\n";
    }
}
