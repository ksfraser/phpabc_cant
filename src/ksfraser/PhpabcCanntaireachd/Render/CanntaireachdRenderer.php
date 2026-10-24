<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Render;

class CanntaireachdRenderer {
    public function render($canntaireachd) {
        return $canntaireachd ? "W:" . $canntaireachd : '';
    }
}
