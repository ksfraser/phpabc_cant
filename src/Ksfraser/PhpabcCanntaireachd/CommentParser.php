<?php
namespace Ksfraser\PhpabcCanntaireachd;

/**
 * Parser for comment lines (%)
 */
class CommentParser implements AbcLineParser {
    /**
     * @param mixed $line
     * @return bool
     */
    public function canParse($line) {
        return preg_match('/^%/', trim($line));
    }

    /**
     * @param mixed $line
     * @param mixed $tune
     * @return bool
     */
    public function parse($line, $tune) {
        if (!preg_match('/^%/', trim($line))) {
            return false;
        }

    // Remove the comment character(s) from the start
    $commentChar = CommentSyntax::COMMENT_CHAR;
    $text = ltrim(preg_replace('/^' . preg_quote($commentChar, '/') . '+\s*/', '', $line));
    $tune->add(new AbcComment($text, false));
        return true;
    }

    /**
     * @param mixed $line
     * @return bool
     */
    public function validate($line) {
        // Comment lines are always valid if they match our pattern
        return $this->canParse($line);
    }
}
