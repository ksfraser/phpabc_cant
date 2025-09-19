<?php
namespace Ksfraser\PhpabcCanntaireachd;

class BlankLineRenderer {
    private $count;
    
    public function __construct($count) {
        $this->count = $count;
    }
    
    public function render() {
        return array_fill(0, $this->count, '');
    }
}
