<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd;

/**
 * Parser for formatting directive lines (%%landscape, %%portrait, etc.)
 * Delegates to the specialized Formatting\FormattingParser
 */
class FormattingParser implements AbcLineParser {
    private $formattingParser;

    public function __construct() {
        $this->formattingParser = new \ksfraser\PhpabcCanntaireachd\Formatting\FormattingParser();
    }

    /**
     * @param mixed $line
     * @return bool
     */
    public function canParse($line) {
        return $this->formattingParser->canParse($line);
    }

    /**
     * @param mixed $line
     * @param mixed $tune
     * @return bool
     */
    public function parse($line, $tune) {
        return $this->formattingParser->parse($line, $tune);
    }

    /**
     * @param mixed $line
     * @return bool
     */
    public function validate($line) {
        return $this->formattingParser->validate($line);
    }
}
