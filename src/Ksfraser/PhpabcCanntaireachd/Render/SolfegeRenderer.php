<?php
namespace Ksfraser\PhpabcCanntaireachd\Render;

class SolfegeRenderer {
    public function render($solfege) {
        return $solfege ? "S:" . $solfege : '';
    }
}
