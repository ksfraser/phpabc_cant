<?php
namespace Ksfraser\PhpabcCanntaireachd;
class AbcLine extends AbcItem {
    protected $headerLine = '';
    protected $bars = [];

    public function setHeaderLine($line) {
        $this->headerLine = $line;
    }
    public function getBars() {
        return $this->bars;
    }
    protected function renderSelf(): string {
        $out = '';
        if ($this->headerLine) {
            $out .= rtrim($this->headerLine, "\n") . "\n";
        }
        if (!empty($this->bars)) {
            $barStrs = [];
            foreach ($this->bars as $barObj) {
                if (method_exists($barObj, 'renderSelf')) {
                    $barStrs[] = trim($barObj->renderSelf());
                } else {
                    $barStrs[] = trim((string)$barObj);
                }
            }
            $out .= '|' . implode('|', $barStrs) . "|\n";
        }
        // Only add a blank line if this is a true blank (no header, no bars)
        if (!$this->headerLine && empty($this->bars)) {
            $out .= "\n";
        }
        return $out;
    }
    // Add line-level sanity checks here
}
