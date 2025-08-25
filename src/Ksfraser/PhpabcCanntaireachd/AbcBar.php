<?php
namespace Ksfraser\PhpabcCanntaireachd;
class AbcBar extends AbcItem {
    private $notes;
    public function __construct($notes = '') {
        $this->notes = $notes;
    }
    protected function renderSelf(): string {
        return '|' . $this->notes;
    }
    // Add note pattern sanity check here
    public function isValidPattern(): bool {
        // Example: check for 4 notes in 4/4
        return true;
    }
}
