<?php
namespace Ksfraser\PhpabcCanntaireachd;

class AbcCommentLine extends AbcItem {
    protected $comment;

    public function __construct($comment) {
        $this->comment = $comment;
    }

    protected function renderSelf(): string {
        return $this->comment . "\n";
    }
}
