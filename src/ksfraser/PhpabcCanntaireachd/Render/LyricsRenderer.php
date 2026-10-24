<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Render;

class LyricsRenderer {
    public function render($lyrics) {
        return $lyrics ? "w:" . $lyrics : '';
    }
}
