<?php
namespace Ksfraser\PhpabcCanntaireachd;

class AbcFormattingLine extends AbcItem {
    protected $directive;

    public function __construct($directive) {
        $this->directive = $directive;
    }

    protected function renderSelf(): string {
        $instruction = $this->directive;
        
        // Handle formatting directives that imply "on" when no on/off specified
        if (preg_match('/^%%(landscape|portrait|continueall|breakall|newpage|leftmargin|rightmargin|topmargin|bottommargin|pagewidth|pageheight|scale|staffwidth)(\s|$)/i', $instruction, $matches)) {
            $directive = strtolower($matches[1]);
            // Check if "on" or "off" is already specified
            if (!preg_match('/\s+(on|off)$/i', $instruction)) {
                // No on/off specified, add " on"
                $instruction .= ' on';
            }
        }
        
        return $instruction . "\n";
    }
}
