<?php
namespace Ksfraser\PhpabcCanntaireachd\Render;

class NotesRenderer {
    public function render(array $notes) {
        return implode(' ', $notes);
    }
}
