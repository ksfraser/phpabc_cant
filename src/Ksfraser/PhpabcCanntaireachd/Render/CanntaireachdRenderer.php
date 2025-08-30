<?php
namespace Ksfraser\PhpabcCanntaireachd\Render;

class CanntaireachdRenderer {
    public function render($canntaireachd) {
        return $canntaireachd ? "W:" . $canntaireachd : '';
    }
}
