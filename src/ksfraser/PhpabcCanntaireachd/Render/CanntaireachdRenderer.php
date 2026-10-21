<?php
namespace ksfraser\PhpabcCanntaireachd\Render;

class CanntaireachdRenderer {
    public function render($canntaireachd) {
        return $canntaireachd ? "W:" . $canntaireachd : '';
    }
}
