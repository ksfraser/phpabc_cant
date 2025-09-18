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

    // Override add() to handle AbcBar objects specially
    public function add(AbcItem $item) {
        if ($item instanceof \Ksfraser\PhpabcCanntaireachd\AbcBar) {
            $this->bars[] = $item;
        } else {
            parent::add($item);
        }
    }
    protected function renderSelf(): string {
        $out = '';
        if ($this->headerLine) {
            $out .= rtrim($this->headerLine, "\n") . "\n";
        }
        if (!empty($this->bars)) {
            $barStrs = [];
            foreach ($this->bars as $barObj) {
                $barContent = '';
                if (method_exists($barObj, 'renderSelf')) {
                    $barContent = trim($barObj->renderSelf());
                } else {
                    $barContent = trim((string)$barObj);
                }
                // Don't add | for comments or instructions
                if (preg_match('/^%%/', $barContent) || preg_match('/^%/', $barContent)) {
                    $out .= $barContent . "\n";
                } else {
                    $barStrs[] = $barContent;
                }
            }
            if (!empty($barStrs)) {
                $out .= '|' . implode('|', $barStrs) . "|\n";
            }
        }
        // Only add a blank line if this is a true blank (no header, no bars)
        if (!$this->headerLine && empty($this->bars)) {
            $out .= "\n";
        }
        return $out;
    }
    public function hasContent(): bool {
        return !empty($this->headerLine) || !empty($this->bars);
    }
    // Add line-level sanity checks here
}
