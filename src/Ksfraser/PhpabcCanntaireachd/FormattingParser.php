<?php
namespace Ksfraser\PhpabcCanntaireachd;

/**
 * Parser for formatting directive lines (%%landscape, %%portrait, etc.)
 */
class FormattingParser implements AbcLineParser {
    public function canParse(string $line): bool {
        return preg_match('/^%%(landscape|portrait|continueall|breakall|newpage|leftmargin|rightmargin|topmargin|bottommargin|pagewidth|pageheight|scale|staffwidth|score)/i', trim($line));
    }

    public function parse(string $line, AbcTune $tune): bool {
        if (!$this->canParse($line)) {
            return false;
        }

        $tune->add(new AbcFormattingLine($line));
        return true;
    }

    public function validate(string $line): bool {
        // Formatting directives are always valid if they match our pattern
        return $this->canParse($line);
    }
}
