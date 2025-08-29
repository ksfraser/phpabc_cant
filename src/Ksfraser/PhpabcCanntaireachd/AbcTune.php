<?php
namespace Ksfraser\PhpabcCanntaireachd;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderX;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderT;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderC;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderB;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderM;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderL;

class AbcTune extends AbcItem {
    /** @var object|null */
    public $config = null;
    /**
     * Fix missing name/sname in V: header lines and log actions
     * @return string log of fixes applied
     */
    public function fixVoiceHeaders() {
        $log = '';
        foreach ($this->getLines() as $lineObj) {
            if (method_exists($lineObj, 'getBars')) {
                foreach ($lineObj->getBars() as $barObj) {
                    // No voice headers in bars
                }
            }
            if (method_exists($lineObj, 'renderSelf')) {
                $line = $lineObj->renderSelf();
                if (preg_match('/^V:([^\s]+)(.*)$/', trim($line), $m)) {
                    $voiceId = $m[1];
                    $rest = $m[2];
                    $needsName = !preg_match('/name="[^"]+"/', $rest);
                    $needsSname = !preg_match('/sname="[^"]+"/', $rest);
                    if ($needsName || $needsSname) {
                        $log .= "Voice $voiceId missing name or sname. ";
                        $newRest = $rest;
                        if ($needsName) {
                            $newRest .= ' name="' . $voiceId . '"';
                            $log .= "Applied name=\"$voiceId\". ";
                        }
                        if ($needsSname) {
                            $newRest .= ' sname="' . $voiceId . '"';
                            $log .= "Applied sname=\"$voiceId\". ";
                        }
                        // Update lineObj to use new header
                        if (method_exists($lineObj, 'setHeaderLine')) {
                            $lineObj->setHeaderLine('V:' . $voiceId . $newRest);
                        }
                        $log .= "\n";
                    }
                }
            }
        }
        return $log;
    }
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
        // Load text file defaults first
        $defaults = array();
        $configFile = __DIR__ . '/../../config/header_defaults.txt';
        if (file_exists($configFile)) {
            $lines = file($configFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (preg_match('/^([A-Z]):\s*(.+)$/', $line, $m)) {
                    $defaults[$m[1]] = $m[2];
                }
            }
        }
        // Merge DB values, overwriting text file values
        $dsn = 'mysql:host=localhost;dbname=phpabc';
        $dbuser = 'phpabc';
        $dbpass = 'phpabc';
        $dbConfigFile = __DIR__ . '/../../config/db_config.php';
        if (file_exists($dbConfigFile)) {
            $dbConfig = include($dbConfigFile);
            if (isset($dbConfig['dsn'])) $dsn = $dbConfig['dsn'];
            if (isset($dbConfig['username'])) $dbuser = $dbConfig['username'];
            if (isset($dbConfig['password'])) $dbpass = $dbConfig['password'];
        }
        try {
            $pdo = new \PDO($dsn, $dbuser, $dbpass);
            $stmt = $pdo->query('SELECT field_name, field_value FROM abc_header_fields');
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $defaults[$row['field_name']] = $row['field_value'];
            }
        } catch (\Exception $e) {
            // DB not available, use text file only
        }
        foreach (self::$headerOrder as $key => $class) {
            if (isset($defaults[$key])) {
                $this->headers[$key] = new $class($defaults[$key]);
            } else {
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
        // Render all headers first
        foreach (self::$headerOrder as $key => $class) {
            $out .= rtrim($this->headers[$key]->render(), "\n") . "\n";
        }
        // Determine output style
        $config = $this->config ?? null;
        $style = ($config && isset($config->voiceOutputStyle)) ? $config->voiceOutputStyle : 'grouped';
        // Collect voice lines and bars
        $voices = [];
        $bars = [];
        foreach ($this->getLines() as $lineObj) {
            if (method_exists($lineObj, 'renderSelf')) {
                $line = $lineObj->renderSelf();
                $line = rtrim($line, "\n");
                if (preg_match('/^V:([^\s]+)/', trim($line), $m)) {
                    $voiceId = $m[1];
                    if (!isset($voices[$voiceId])) {
                        $voices[$voiceId] = $line;
                    }
                } else if (preg_match('/^\|/', $line)) {
                    $bars[] = $line;
                }
            }
        }
        if ($style === 'interleaved') {
            // Interleaved: alternate bars for each voice
            foreach ($voices as $voiceLine) {
                $out .= $voiceLine . "\n";
            }
            foreach ($bars as $barLine) {
                $out .= $barLine . "\n";
            }
        } else {
            // Grouped: all V: lines, then all bars
            foreach ($voices as $voiceLine) {
                $out .= $voiceLine . "\n";
            }
            foreach ($bars as $barLine) {
                $out .= $barLine . "\n";
            }
        }
        // Remove trailing blank lines
        return rtrim($out, "\n") . "\n";
    }
    // Add tune-level sanity checks here
}
