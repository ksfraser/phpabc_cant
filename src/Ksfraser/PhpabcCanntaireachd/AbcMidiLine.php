<?php
namespace Ksfraser\PhpabcCanntaireachd;

class AbcMidiLine extends AbcItem {
    protected $instruction;

    public function __construct($instruction) {
        $this->instruction = $instruction;
    }

    protected function renderSelf(): string {
        return $this->instruction . "\n";
    }
}
