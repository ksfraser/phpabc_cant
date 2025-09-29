<?php
namespace Ksfraser\PhpabcCanntaireachd\Tune;

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
	//	This was adding a blank line between headers and body!!
	//$out .= "\n";
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
        $context = new \Ksfraser\PhpabcCanntaireachd\ParseContext($this->voiceBars);
        $barLines = \Ksfraser\PhpabcCanntaireachd\Render\BarLineRenderer::getSupportedBarLines();
        $handlers = [
            new \Ksfraser\PhpabcCanntaireachd\BodyLineHandler\BarLineHandler($barLines),
            new \Ksfraser\PhpabcCanntaireachd\BodyLineHandler\LyricsHandler($this->forceBarLinesInLyrics ?? false),
            new \Ksfraser\PhpabcCanntaireachd\BodyLineHandler\CanntaireachdHandler(),
            new \Ksfraser\PhpabcCanntaireachd\BodyLineHandler\SolfegeHandler(),
            new \Ksfraser\PhpabcCanntaireachd\BodyLineHandler\NoteHandler(),
		//There should be a MIDI and Instruction %% and Comment % handler here!!
        ];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            // Voice change: V:xx or [V:xx] or possibly continuation of same voice
            if (preg_match('/^(?:\[)?V:([^\s\]]+)(?:\])?/', $trimmed, $m)) {
		//Ensures that context->voiceBars has an array for the voice
		//also sets the current voice pointer to the voice string
                $context->getOrCreateVoice($m[1]);
                continue;
            }
            // If we have not seen a voice yet, create a default melody voice when content appears
            if ($context->currentVoice === null && $trimmed !== '' && !preg_match('/^[A-Z]:/i', $trimmed)) {
		//If the line isn't a header line then it should be a voice/music line.  
		//Unless its a comment/instruction.  If it is a V: line it will have been created above! (currentVoice != NULL)
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
