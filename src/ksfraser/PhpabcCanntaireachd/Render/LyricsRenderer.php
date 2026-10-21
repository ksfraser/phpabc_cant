<?php
namespace ksfraser\PhpabcCanntaireachd\Render;

class LyricsRenderer {
    public function render($lyrics) {
        return $lyrics ? "w:" . $lyrics : '';
    }
}
