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
        'X' => \Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderX::class,
        'T' => \Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderT::class,
        'C' => \Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderC::class,
        'B' => \Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderB::class,
        'K' => \Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderK::class,
        'Q' => \Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderQ::class,
        'L' => \Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderL::class,
        'M' => \Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderM::class,
        'R' => \Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderR::class,
        'O' => \Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderO::class,
        'Z' => \Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderZ::class,
    ];

    public function __construct() {
        // Initialize all mandatory headers as empty
        foreach (self::$headerOrder as $key => $class) {
            switch ($key) {
                case 'K':
                    $this->headers[$key] = new $class('HP');
                    break;
                case 'Q':
                    $this->headers[$key] = new $class('1/4=90');
                    break;
                case 'L':
                    $this->headers[$key] = new $class('1/8');
                    break;
                case 'M':
                    $this->headers[$key] = new $class('2/4');
                    break;
                case 'R':
                    $this->headers[$key] = new $class('March');
                    break;
                case 'O':
                    $this->headers[$key] = new $class('Scots Guards I');
                    break;
                case 'Z':
                    $this->headers[$key] = new $class('');
                    break;
                default:
                    $this->headers[$key] = new $class();
            }
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
