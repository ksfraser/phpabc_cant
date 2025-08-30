<?php
namespace Ksfraser\PhpabcCanntaireachd\Render;

class LyricsRenderer {
    public function render($lyrics) {
        return $lyrics ? "w:" . $lyrics : '';
    }
}
