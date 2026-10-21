<?php
namespace ksfraser\PhpabcCanntaireachd\Render;

class SolfegeRenderer {
    public function render($solfege) {
        return $solfege ? "S:" . $solfege : '';
    }
}
