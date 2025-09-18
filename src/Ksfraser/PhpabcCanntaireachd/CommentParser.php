<?php
namespace Ksfraser\PhpabcCanntaireachd;

/**
 * Parser for comment lines (%)
 */
class CommentParser implements AbcLineParser {
    public function canParse(string $line): bool {
        return preg_match('/^%/', trim($line));
    }

    public function parse(string $line, AbcTune $tune): bool {
        if (!preg_match('/^%/', trim($line))) {
            return false;
        }

        $tune->add(new AbcCommentLine($line));
        return true;
    }

    public function validate(string $line): bool {
        // Comment lines are always valid if they match our pattern
        return $this->canParse($line);
    }
}
