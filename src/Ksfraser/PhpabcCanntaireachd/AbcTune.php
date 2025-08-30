<?php
namespace Ksfraser\PhpabcCanntaireachd;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderX;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderT;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderC;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderB;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderM;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderL;
use Ksfraser\PhpabcCanntaireachd\Header\AbcFixVoiceHeader;

class AbcTune extends AbcItem {
    /**
     * Render this tune as an ABC string (stub implementation)
     */
    public function renderSelf(): string {
        $out = '';
        // Render headers in defined order
        foreach (self::$headerOrder as $key => $class) {
            if (isset($this->headers[$key])) {
                $out .= $this->headers[$key]->render();
            }
        }
        // Render voice headers (V: lines)
        foreach ($this->voices as $voiceId => $meta) {
            $name = $meta['name'] ?? $voiceId;
            $sname = $meta['sname'] ?? $name;
            $out .= "V:$voiceId name=\"$name\" sname=\"$sname\"\n";
        }
        // Render any other headers not in headerOrder
        foreach ($this->headers as $k => $h) {
            if (!isset(self::$headerOrder[$k]) && method_exists($h, 'render')) {
                $out .= $h->render();
            }
        }
        // Ensure header/body separator
        $out .= "\n";
        return $out;
    }
    /**
     * Config option: number of bars per interleave block
     * @var int
     */
    protected $interleaveWidth = 1;

    /**
     * Config option: render solfege (do-re-mi) for non-bagpipe voices
     * @var bool
     */
    protected $renderSolfege = false;

    /**
     * Per-voice array of AbcBar objects
     * @var array
     */
    protected $voiceBars = [];

    /**
     * Array of header objects
     * @var array
     */
    protected $headers = [];

    /**
     * Array of voice objects
     * @var array
     */
    protected $voices = [];

    /**
     * Header order mapping
     * @var array
     */
    protected static $headerOrder = [
        'X' => \Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderX::class,
        'T' => \Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderT::class,
        'C' => \Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderC::class,
        'B' => \Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderB::class,
        'Q' => \Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderQ::class,
        'L' => \Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderL::class,
        'M' => \Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderM::class,
        'R' => \Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderR::class,
        'O' => \Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderO::class,
        'Z' => \Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderZ::class,
        'K' => \Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderK::class,
    ];

    /**
     * Set config option for interleave width
     */
    public function setInterleaveWidth($width = 1)
    {
        $this->interleaveWidth = max(1, (int)$width);
    }

    /**
     * Set config option for rendering solfege
     */
    public function setRenderSolfege($render = true)
    {
        $this->renderSolfege = $render;
    }

    /**
     * Parse body lines using handler classes for each line type (SOLID/DRY)
     * Handler classes should be placed in src/Ksfraser/PhpabcCanntaireachd/BodyLineHandler/
     */
    public function parseBodyLines(array $lines)
    {
        $context = new \Ksfraser\PhpabcCanntaireachd\ParseContext($this->voiceBars);
        $barLines = \Ksfraser\PhpabcCanntaireachd\Render\BarLineRenderer::getSupportedBarLines();
        $handlers = [
            new \Ksfraser\PhpabcCanntaireachd\BodyLineHandler\BarLineHandler($barLines),
            new \Ksfraser\PhpabcCanntaireachd\BodyLineHandler\LyricsHandler($this->forceBarLinesInLyrics ?? false),
            new \Ksfraser\PhpabcCanntaireachd\BodyLineHandler\CanntaireachdHandler(),
            new \Ksfraser\PhpabcCanntaireachd\BodyLineHandler\SolfegeHandler(),
            new \Ksfraser\PhpabcCanntaireachd\BodyLineHandler\NoteHandler(),
        ];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            // Voice change: V:xx or [V:xx]
            if (preg_match('/^(?:\[)?V:([^\s\]]+)(?:\])?/', $trimmed, $m)) {
                $context->getOrCreateVoice($m[1]);
                continue;
            }
            // If we have not seen a voice yet, create a default melody voice when content appears
            if ($context->currentVoice === null && $trimmed !== '' && !preg_match('/^[A-Z]:/i', $trimmed)) {
                $context->getOrCreateVoice('M');
            }
            foreach ($handlers as $handler) {
                if ($handler->matches($line)) {
                    $handler->handle($context, $line);
                    break;
                }
            }
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
                /*
                // Use public render() so we always get a string
                $line = $lineObj->render();
                if (preg_match('/^V:([^\s]+)(.*)$/', trim($line), $m)) {
                    $voiceId = $m[1];
                    $rest = $m[2];
                    // Detect any existing name/sname values
                        $existingName = null;
                        $existingSname = null;
                        if (preg_match('/name="([^"]+)"/', $rest, $mm)) {
                            $existingName = $mm[1];
                        }
                        else
                        {
                            $needsName = true;
                        }   
                        if (preg_match('/sname="([^"]+)"/', $rest, $mm2)) {
                            $existingSname = $mm2[1];
                        }
                        else
                        {
                            $needsSname = true;
                        }
                    if ($needsName || $needsSname) {
                        $log .= "Voice $voiceId missing name or sname. ";
                        $newRest = $rest;
                        // If name is missing, prefer to use sname if present, otherwise fall back to voiceId
                        if ($needsName) {
                            $useName = $existingSname ?? $voiceId;
                            $newRest .= ' name="' . $useName . '"';
                            $log .= "Applied name=\"$useName\". ";
                        }

                        // If sname is missing, prefer to use name if present, otherwise fall back to voiceId
                        if ($needsSname) {
                            $useSname = $existingName ?? $voiceId;
                            $newRest .= ' sname="' . $useSname . '"';
                            $log .= "Applied sname=\"$useSname\". ";
                        }

                        // Update lineObj to use new header
                        if (method_exists($lineObj, 'setHeaderLine')) {
                            $lineObj->setHeaderLine('V:' . $voiceId . $newRest);
                        }
                        $log .= "\n";
                    }
                }
                    */
                $log .= AbcFixVoiceHeader::fixHeader($lineObj);
            }
        }
        return $log;
    }

    // Header management
    public function addHeader(string $key, $value) {
        $class = '\\Ksfraser\\PhpabcCanntaireachd\\Header\\AbcHeader' . $key;
        if (class_exists($class)) {
            // Multi-value headers (B,C) extend AbcHeaderMultiField
            $obj = new $class($value);
            if (isset($this->headers[$key]) && method_exists($this->headers[$key], 'add')) {
                $this->headers[$key]->add($value);
            } else {
                $this->headers[$key] = $obj;
            }
        } else {
            // Fallback: store as simple header-like object
            $h = new \Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderX('');
            $h->set($value);
            $this->headers[$key] = $h;
        }
    }

    public function replaceHeader(string $key, $value) {
        $class = '\\Ksfraser\\PhpabcCanntaireachd\\Header\\AbcHeader' . $key;
        if (class_exists($class)) {
            $this->headers[$key] = new $class($value);
        } else {
            $h = new \Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderX('');
            $h->set($value);
            $this->headers[$key] = $h;
        }
    }

    public function getHeaders(): array {
        return $this->headers;
    }

    // Return lines/subitems (for fixVoiceHeaders and other operations)
    public function getLines(): array {
        return $this->subitems ?? [];
    }

    public function __construct() {
        // Load defaults from centralized HeaderDefaults (SQL schema + config overlay)
        $defaults = \Ksfraser\PhpabcCanntaireachd\HeaderDefaults::getDefaults();
        foreach (self::$headerOrder as $key => $class) {
            $value = $defaults[$key] ?? '';
            $this->headers[$key] = new $class($value);
        }
    }

    public function getVoiceBars(): array {
        return $this->voiceBars;
    }

    public function copyVoice(string $from, string $to): void {
        if (!isset($this->voiceBars[$from])) return;
        $this->voiceBars[$to] = [];
        foreach ($this->voiceBars[$from] as $barNum => $barObj) {
            // Shallow clone bar object
            $this->voiceBars[$to][$barNum] = clone $barObj;
        }
    }

    public function ensureVoiceInsertedFirst(string $voiceId, array $bars): void {
        // Remove any existing instance to avoid duplicates
        if (isset($this->voiceBars[$voiceId])) {
            unset($this->voiceBars[$voiceId]);
        }
        // Prepend this voice so it becomes the first in output ordering
        $this->voiceBars = array_merge([$voiceId => $bars], $this->voiceBars);
    }

    public function addVoiceHeader(string $voiceId, ?string $name = null, ?string $sname = null): void {
        $this->voices[$voiceId] = [
            'name' => $name ?? $voiceId,
            'sname' => $sname ?? ($name ?? $voiceId)
        ];
    }

    public function getVoicesMeta(): array {
        return $this->voices;
    }
}
