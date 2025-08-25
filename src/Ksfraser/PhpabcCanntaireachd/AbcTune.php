<?php
namespace Ksfraser\PhpabcCanntaireachd;
class AbcTune extends AbcItem {
    public $headers = [];
    public function addHeader($key, $value) {
        $this->headers[$key] = $value;
    }
    public function getHeaders() {
        return $this->headers;
    }
    public function getLines() {
        return $this->subitems;
    }
    protected function renderSelf(): string {
        $out = '';
        foreach ($this->headers as $k => $v) {
            $out .= "$k:$v\n";
        }
        return $out;
    }
    // Add tune-level sanity checks here
}
