<?php
namespace Ksfraser\PhpabcCanntaireachd;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderX;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderT;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderC;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderB;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderM;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderL;

class AbcTune extends AbcItem {
    /**
     * Config option: number of bars per interleave block
     * @var int
     */
    protected $interleaveWidth = 1;

    /**
     * Set config option for interleave width
     */
    public function setInterleaveWidth($width = 1)
    {
        $this->interleaveWidth = max(1, (int)$width);
    }
    /**
     * Config option: render solfege (do-re-mi) for non-bagpipe voices
     * @var bool
     */
    protected $renderSolfege = false;

    /**
     * Set config option for rendering solfege
     */
    public function setRenderSolfege($render = true)
    {
        $this->renderSolfege = $render;
    }

    /**
     * Parse body lines, track current voice, lyrics, and canntaireachd
     */
    public function parseBodyLines(array $lines)
    {
        $currentVoice = null;
        $currentBar = 0;
        foreach ($lines as $line) {
            $trimmed = trim($line);
            // Voice change: V:xx or [V:xx]
            if (preg_match('/^(?:\[)?V:([^\s\]]+)(?:\])?/', $trimmed, $m)) {
                $currentVoice = $m[1];
                if (!isset($this->voiceBars[$currentVoice])) {
                    $this->voiceBars[$currentVoice] = [];
                }
                continue;
            }
            // Bar line
            if (preg_match('/^\|/', $trimmed)) {
                $currentBar++;
                $barObj = new AbcBar($currentBar, $trimmed);
                $this->voiceBars[$currentVoice][$currentBar] = $barObj;
                continue;
            }
            // Lyrics (w:)
            if (preg_match('/^w:(.*)$/i', $trimmed, $m)) {
                $lyrics = $m[1];
                if ($this->forceBarLinesInLyrics) {
                    $lyricBars = preg_split('/\|/', $lyrics);
                    foreach ($lyricBars as $i => $lyricBar) {
                        $barNum = $currentBar + $i;
                        if (!isset($this->voiceBars[$currentVoice][$barNum])) {
                            $this->voiceBars[$currentVoice][$barNum] = new AbcBar($barNum);
                        }
                        $this->voiceBars[$currentVoice][$barNum]->setLyrics(trim($lyricBar));
                    }
                    $currentBar += count($lyricBars) - 1;
                } else {
                    if (!isset($this->voiceBars[$currentVoice][$currentBar])) {
                        $this->voiceBars[$currentVoice][$currentBar] = new AbcBar($currentBar);
                    }
                    $this->voiceBars[$currentVoice][$currentBar]->setLyrics($lyrics);
                }
                continue;
            }
            // Canntaireachd (W:)
            if (preg_match('/^W:(.*)$/i', $trimmed, $m)) {
                $cannt = $m[1];
                if (!isset($this->voiceBars[$currentVoice][$currentBar])) {
                    $this->voiceBars[$currentVoice][$currentBar] = new AbcBar($currentBar);
                }
                $this->voiceBars[$currentVoice][$currentBar]->setCanntaireachd($cannt);
                continue;
            }
            // Solfege (S:)
            if (preg_match('/^S:(.*)$/i', $trimmed, $m)) {
                $solfege = $m[1];
                if (!isset($this->voiceBars[$currentVoice][$currentBar])) {
                    $this->voiceBars[$currentVoice][$currentBar] = new AbcBar($currentBar);
                }
                $this->voiceBars[$currentVoice][$currentBar]->setSolfege($solfege);
                continue;
            }
            // Notes/body
            if (!isset($this->voiceBars[$currentVoice][$currentBar])) {
                $this->voiceBars[$currentVoice][$currentBar] = new AbcBar($currentBar);
            }
            $this->voiceBars[$currentVoice][$currentBar]->addNote($trimmed);
        }
    }
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

    protected $voices = [];

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
    /**
     * Per-voice array of AbcBar objects
     */
    protected $voiceBars = [];

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
        
    // ...existing code...
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
            }
