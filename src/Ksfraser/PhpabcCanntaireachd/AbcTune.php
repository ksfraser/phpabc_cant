<?php
namespace Ksfraser\PhpabcCanntaireachd;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderX;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderT;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderC;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderB;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderM;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderL;
use Ksfraser\PhpabcCanntaireachd\Header\AbcFixVoiceHeader;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderGeneric;

class AbcTune extends AbcItem {
    /**
     * Render this tune as an ABC string (restored full implementation)
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
        foreach ($this->voices as $voiceId => $voice) {
            $name = $voice['name'] ?? $voiceId;
            $sname = $voice['sname'] ?? $name;
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

        // Always try to populate bars if empty and lines exist
        $hasBars = false;
        foreach ($this->voices as $voice) {
            if (!empty($voice['bars'])) {
                $hasBars = true;
                break;
            }
        }
        if (!$hasBars && !empty($this->getLines())) {
            $lines = [];
            foreach ($this->getLines() as $lineObj) {
                if (is_string($lineObj)) {
                    $lines[] = $lineObj;
                } elseif (method_exists($lineObj, 'render')) {
                    $lines[] = trim($lineObj->render());
                } else {
                    $lines[] = (string)$lineObj;
                }
            }
            if (!empty($lines)) {
                $this->parseBodyLines($lines);
            }
        }

        // Render body/music lines for each voice
        $voiceCount = count($this->voices);
        $anyBars = false;
        foreach ($this->voices as $voiceId => $voice) {
            if (!empty($voice['bars'])) {
                $anyBars = true;
                // Output voice header for each voice (except if only one voice)
                if ($voiceCount > 1) {
                    $out .= "V:$voiceId\n";
                }
                foreach ($voice['bars'] as $barObj) {
                    $out .= $barObj->renderSelf() . " ";
                }
                $out = rtrim($out) . "\n";
                // If Bagpipe voice, render canntaireachd line
                if (strtolower($voiceId) === 'p' || strtolower($voiceId) === 'bagpipes') {
                    $out .= "%%Canntaireachd\n";
                    foreach ($voice['bars'] as $barObj) {
                        if (method_exists($barObj, 'getCanntaireachd')) {
                            $out .= $barObj->getCanntaireachd() . " ";
                        }
                    }
                    $out = rtrim($out) . "\n";
                }
            }
        }
        if (!$anyBars) {
            // Fallback: render subitems (legacy)
            foreach ($this->getLines() as $lineObj) {
                if (method_exists($lineObj, 'render')) {
                    $line = $lineObj->render();
                    if ($line !== '' && substr($line, -1) !== "\n") {
                        $out .= $line . "\n";
                    } else {
                        $out .= $line;
                    }
                } elseif (is_string($lineObj)) {
                    $out .= rtrim($lineObj) . "\n";
                }
            }
        }
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
        'S' => \Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderS::class,
        'A' => \Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderA::class,
        'N' => \Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderN::class,
        'H' => \Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderH::class,
        'U' => \Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderU::class,
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
        // Use a custom context that writes to $this->voices[voiceId]['bars']
        $context = new class($this) extends \Ksfraser\PhpabcCanntaireachd\ParseContext {
            private $tune;
            public function __construct($tune) {
                $this->tune = $tune;
                $empty = [];
                parent::__construct($empty);
            }
            public function getOrCreateVoice($voiceId) {
                if (!isset($this->tune->voices[$voiceId])) {
                    $this->tune->voices[$voiceId] = [
                        'name' => $voiceId,
                        'sname' => $voiceId,
                        'bars' => []
                    ];
                }
                $this->currentVoice = $voiceId;
                return $voiceId;
            }
            public function addBar($barObj) {
                if ($this->currentVoice !== null) {
                    $this->tune->voices[$this->currentVoice]['bars'][] = $barObj;
                }
            }
        };
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
            $obj = new AbcHeaderGeneric( $value );
            if (isset($this->headers[$key]) && method_exists($this->headers[$key], 'add')) {
                $this->headers[$key]->add($value);
            } else {
                $obj->setLabel( $key );
                $this->headers[$key] = $obj;
            }
        }
    }

    public function replaceHeader(string $key, $value) {
        $class = '\\Ksfraser\\PhpabcCanntaireachd\\Header\\AbcHeader' . $key;
        if (class_exists($class)) {
            $this->headers[$key] = new $class($value);
        } else {
            $h = new \Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderGeneric('');
            $h->set($value);
            $h->setLabel( $key );
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
        // Don't set defaults here, they will be set later if missing
    }

    public function getVoiceBars(): array {
        $bars = [];
        foreach ($this->voices as $voiceId => $voice) {
            $bars[$voiceId] = $voice['bars'] ?? [];
        }
        return $bars;
    }

    public function copyVoice(string $from, string $to): void {
        if (!isset($this->voices[$from]['bars'])) return;
        $this->voices[$to] = $this->voices[$from];
        $this->voices[$to]['bars'] = [];
        foreach ($this->voices[$from]['bars'] as $barNum => $barObj) {
            $this->voices[$to]['bars'][$barNum] = clone $barObj;
        }
    }

    public function ensureVoiceInsertedFirst(string $voiceId, array $bars): void {
        // Remove any existing instance to avoid duplicates
        if (isset($this->voices[$voiceId])) {
            unset($this->voices[$voiceId]);
        }
        // Prepend this voice so it becomes the first in output ordering
        $this->voices = array_merge([
            $voiceId => [
                'name' => $voiceId,
                'sname' => $voiceId,
                'bars' => $bars
            ]
        ], $this->voices);
    }

    public function addVoiceHeader(string $voiceId, ?string $name = null, ?string $sname = null): void {
        if (!isset($this->voices[$voiceId])) {
            $this->voices[$voiceId] = [
                'name' => $name ?? $voiceId,
                'sname' => $sname ?? ($name ?? $voiceId),
                'bars' => []
            ];
        } else {
            if ($name !== null) $this->voices[$voiceId]['name'] = $name;
            if ($sname !== null) $this->voices[$voiceId]['sname'] = $sname;
        }
    }

    /**
     * Replace generic voice names with MIDI instrument names
     */
    public function updateVoiceNamesFromMidi() {
        $lines = $this->getLines();
        $voiceUpdates = [];

        // First pass: collect voice headers and their positions
        $voicePositions = [];
        foreach ($lines as $index => $lineObj) {
            if (method_exists($lineObj, 'renderSelf')) {
                $line = $lineObj->renderSelf();
                if (preg_match('/^V:([^\s]+)(.*)$/', $line, $matches)) {
                    $voiceId = $matches[1];
                    $voicePositions[] = [
                        'index' => $index,
                        'voiceId' => $voiceId,
                        'rest' => $matches[2],
                        'lineObj' => $lineObj
                    ];
                }
            }
        }

        // Second pass: find MIDI programs that follow each voice
        foreach ($voicePositions as $voiceIndex => $voice) {
            $midiProgram = null;
            $startIndex = $voice['index'] + 1;

            // Look for MIDI program in the next few lines after this voice
            for ($i = $startIndex; $i < count($lines) && $i < $startIndex + 10; $i++) {
                $lineObj = $lines[$i];
                if (method_exists($lineObj, 'renderSelf')) {
                    $line = $lineObj->renderSelf();
                    if (preg_match('/^%%MIDI\s+program\s+(\d+)/i', $line, $matches)) {
                        $midiProgram = (int)$matches[1];
                        break;
                    }
                    // Stop looking if we hit another voice header
                    if (preg_match('/^V:/', $line)) {
                        break;
                    }
                }
            }

            // If we found a MIDI program for this voice, prepare the update
            if ($midiProgram !== null) {
                $instrument = MidiInstrumentMapper::getInstrument($midiProgram);
                if ($instrument) {
                    $voiceUpdates[] = [
                        'voice' => $voice,
                        'instrument' => $instrument
                    ];
                }
            }
        }

        // Third pass: apply the updates
        foreach ($voiceUpdates as $update) {
            $voice = $update['voice'];
            $instrument = $update['instrument'];
            $voiceId = $voice['voiceId'];
            $rest = $voice['rest'];

            // Check if this is a generic voice (numbered or simple name)
            if (preg_match('/^\d+$/', $voiceId) ||
                in_array(strtolower($voiceId), ['voice', 'instrument', 'track'])) {

                $newName = $instrument['short'];
                $newSname = $instrument['short'];

                // Replace or add name/sname
                if (preg_match('/name="[^"]*"/', $rest)) {
                    $rest = preg_replace('/name="[^"]*"/', 'name="' . $newName . '"', $rest);
                } else {
                    $rest .= ' name="' . $newName . '"';
                }

                if (preg_match('/sname="[^"]*"/', $rest)) {
                    $rest = preg_replace('/sname="[^"]*"/', 'sname="' . $newSname . '"', $rest);
                } else {
                    $rest .= ' sname="' . $newSname . '"';
                }

                // Update the line
                if (method_exists($voice['lineObj'], 'setHeaderLine')) {
                    $voice['lineObj']->setHeaderLine('V:' . $voiceId . $rest);
                }
            }
        }
    }
}
