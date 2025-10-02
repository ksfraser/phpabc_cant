<?php
namespace Ksfraser\PhpabcCanntaireachd;

/**
 * Represents a comment in ABC notation (full-line or inline).
 *
 * @property string $text The comment text (without comment char)
 * @property bool $inline True if this is an inline comment
 */
class AbcComment extends AbcToken
{
    /**
     * Render the comment as a string, with the correct comment character.
     * @return string
     */
    public function render(): string {
        return CommentSyntax::COMMENT_CHAR . $this->text . "\n";
    }
    /** @var string */
    protected $text;
    /** @var bool */
    protected $inline;

    /**
     * @param string $text The comment text (without comment char)
     * @param bool $inline True if inline, false if full-line
     */
    public function __construct($text, $inline = false) {
        $this->text = $text;
        $this->inline = $inline;
    }

    /**
     * Get the comment text.
     * @return string
     */
    public function getText() {
        return $this->text;
    }

    /**
     * Is this an inline comment?
     * @return bool
     */
    public function isInline() {
        return $this->inline;
    }
}
