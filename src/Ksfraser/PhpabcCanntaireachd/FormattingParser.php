<?php
namespace Ksfraser\PhpabcCanntaireachd;

/**
 * Parser for formatting directive lines (%%landscape, %%portrait, etc.)
 */
class FormattingParser implements AbcLineParser {
    /**
     * @param mixed $line
     * @return bool
     */
    public function canParse($line) {
        return preg_match('/^%%(landscape|portrait|continueall|breakall|newpage|leftmargin|rightmargin|topmargin|bottommargin|pagewidth|pageheight|scale|staffwidth|score)/i', trim($line));
    }

    /**
     * @param mixed $line
     * @param mixed $tune
     * @return bool
     */
    public function parse($line, $tune) {
        if (!$this->canParse($line)) {
            return false;
        }

        $tune->add(new AbcFormattingLine($line));
        return true;
    }

    /**
     * @param mixed $line
     * @return bool
     */
    public function validate($line) {
        // Formatting directives are always valid if they match our pattern
        return $this->canParse($line);
    }
}
