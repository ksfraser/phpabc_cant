<?php
namespace Ksfraser\PhpabcCanntaireachd;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderX;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderT;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderC;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderB;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderM;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderL;

class AbcTune extends AbcItem {
    protected $headers = [];
    protected static $headerOrder = [
        'X' => AbcHeaderX::class,
        'T' => AbcHeaderT::class,
        'C' => AbcHeaderC::class,
        'B' => AbcHeaderB::class,
        'M' => AbcHeaderM::class,
        'L' => AbcHeaderL::class,
    ];

    public function __construct() {
        // Initialize all mandatory headers as empty
        foreach (self::$headerOrder as $key => $class) {
            $this->headers[$key] = new $class();
        }
    }

    /**
     * Add or update a header field
     */
    public function addHeader($key, $value) {
        if (!isset(self::$headerOrder[$key])) return;
        $headerObj = $this->headers[$key];
        // Multi-value fields
        if (in_array($key, ['C', 'B'])) {
            $headerObj->add($value);
        } else {
            $headerObj->set($value);
        }
    }

    /**
     * Replace a header field
     */
    public function replaceHeader($key, $value) {
        if (!isset(self::$headerOrder[$key])) return;
        $headerObj = $this->headers[$key];
        $headerObj->set($value);
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function getLines() {
        return $this->subitems;
    }

    protected function renderSelf(): string {
        $out = '';
        foreach (self::$headerOrder as $key => $class) {
            $out .= $this->headers[$key]->render();
        }
        return $out;
    }
    // Add tune-level sanity checks here
}
