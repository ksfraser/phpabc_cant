<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Render;

class SolfegeRenderer {
    public function render($solfege) {
        return $solfege ? "S:" . $solfege : '';
    }
}
