<?php
namespace ksfraser\PhpabcCanntaireachd\Render;

class NotesRenderer {
    public function render(array $notes) {
        return implode(' ', $notes);
    }
}
