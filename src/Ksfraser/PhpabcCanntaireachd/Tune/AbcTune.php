    /**
     * Get all Voice objects, keyed by voice ID.
     * @return array<string, \Ksfraser\PhpabcCanntaireachd\Voices\AbcVoice>
     */
    public function getVoices(): array {
        // If $this->voiceObjs exists, return it; otherwise, build from voiceBars
        if (property_exists($this, 'voiceObjs') && is_array($this->voiceObjs)) {
            return $this->voiceObjs;
        }
        $result = [];
        // If voiceBars contains objects with getVoiceIndicator, use those
        if (isset($this->voiceBars) && is_array($this->voiceBars)) {
            foreach ($this->voiceBars as $voiceId => $bars) {
                if (is_array($bars) && count($bars) > 0 && method_exists($bars[0], 'getVoiceIndicator')) {
                    // Try to get the voice object from the first bar (if it stores a reference)
                    if (property_exists($bars[0], 'voice') && $bars[0]->voice instanceof \Ksfraser\PhpabcCanntaireachd\Voices\AbcVoice) {
                        $result[$voiceId] = $bars[0]->voice;
                    }
                }
            }
        }
        // Fallback: if $this->voices is an array of metadata, create AbcVoice objects
        if (empty($result) && isset($this->voices) && is_array($this->voices)) {
            foreach ($this->voices as $voiceId => $meta) {
                $result[$voiceId] = new \Ksfraser\PhpabcCanntaireachd\Voices\AbcVoice($voiceId, $meta['name'] ?? '', $meta['sname'] ?? '');
            }
        }
        return $result;
    }
<?php
namespace Ksfraser\PhpabcCanntaireachd\Tune;
/**
 * Class AbcTune
 *
 * Represents a parsed ABC tune, including headers, voices, bars, and rendering logic.
 * Supports header management, voice assignment, MIDI instrument mapping, and body line parsing via handler classes.
 *
 * SOLID: Single Responsibility (tune model), Dependency Injection (configurable options), DRY (delegates to handlers).
 *
 * @package Ksfraser\PhpabcCanntaireachd\Tune
 *
 * @property array $headers Array of header objects
 * @property array $voices Array of voice metadata
 * @property array $voiceBars Per-voice array of AbcBar objects
 * @property int $interleaveWidth Number of bars per interleave block
 * @property bool $renderSolfege Render solfege for non-bagpipe voices
 *
 * @method renderSelf(): string Render this tune as an ABC string
 * @method setInterleaveWidth(int $width) Set interleave width
 * @method setRenderSolfege(bool $render) Set solfege rendering
 * @method parseBodyLines(array $lines) Parse body lines using handler classes
 * @method fixVoiceHeaders(): string Fix missing name/sname in V: header lines
 * @method addHeader(string $key, $value) Add header object
 * @method replaceHeader(string $key, $value) Replace header object
 * @method getHeaders(): array Get all header objects
 * @method getLines(): array Get all subitems/lines
 * @method getVoiceBars(): array Get all voice bars
 * @method copyVoice(string $from, string $to): void Copy voice bars
 * @method ensureVoiceInsertedFirst(string $voiceId, array $bars): void Prepend voice bars
 * @method addVoiceHeader(string $voiceId, ?string $name, ?string $sname): void Add voice header
 * @method updateVoiceNamesFromMidi() Replace generic voice names with MIDI instrument names
 *
 * @uml
 * @startuml
 * class AbcTune {
 *   - headers: array
 *   - voices: array
 *   - voiceBars: array
 *   - interleaveWidth: int
 *   - renderSolfege: bool
 *   + renderSelf(): string
 *   + setInterleaveWidth(width: int)
 *   + setRenderSolfege(render: bool)
 *   + parseBodyLines(lines: array)
 *   + fixVoiceHeaders(): string
 *   + addHeader(key: string, value)
 *   + replaceHeader(key: string, value)
 *   + getHeaders(): array
 *   + getLines(): array
 *   + getVoiceBars(): array
 *   + copyVoice(from: string, to: string)
 *   + ensureVoiceInsertedFirst(voiceId: string, bars: array)
 *   + addVoiceHeader(voiceId: string, name: string, sname: string)
 *   + updateVoiceNamesFromMidi()
 * }
 * AbcTune --|> AbcItem
 * AbcTune --> AbcBar
 * AbcTune --> AbcHeaderX
 * AbcTune --> AbcHeaderT
 * AbcTune --> AbcHeaderC
 * AbcTune --> AbcHeaderB
 * AbcTune --> AbcHeaderM
 * AbcTune --> AbcHeaderL
 * AbcTune --> AbcHeaderGeneric
 * AbcTune --> MidiInstrumentMapper
 * AbcTune --> BodyLineHandler
 * @enduml
 */

use Ksfraser\PhpabcCanntaireachd\AbcItem;
use Ksfraser\PhpabcCanntaireachd\Midi\MidiInstrumentMapper;
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
     * Get all Voice objects, keyed by voice ID.
     * @return array<string, \Ksfraser\PhpabcCanntaireachd\Voices\AbcVoice>
     */
    public function getVoices(): array {
        // If $this->voiceObjs exists, return it; otherwise, build from voiceBars
        if (property_exists($this, 'voiceObjs') && is_array($this->voiceObjs)) {
            return $this->voiceObjs;
        }
        $result = [];
        // If voiceBars contains objects with getVoiceIndicator, use those
        if (isset($this->voiceBars) && is_array($this->voiceBars)) {
            foreach ($this->voiceBars as $voiceId => $bars) {
                if (is_array($bars) && count($bars) > 0 && method_exists($bars[0], 'getVoiceIndicator')) {
                    // Try to get the voice object from the first bar (if it stores a reference)
                    if (property_exists($bars[0], 'voice') && $bars[0]->voice instanceof \Ksfraser\PhpabcCanntaireachd\Voices\AbcVoice) {
                        $result[$voiceId] = $bars[0]->voice;
                    }
                }
            }
        }
        // Fallback: if $this->voices is an array of metadata, create AbcVoice objects
        if (empty($result) && isset($this->voices) && is_array($this->voices)) {
            foreach ($this->voices as $voiceId => $meta) {
                $result[$voiceId] = new \Ksfraser\PhpabcCanntaireachd\Voices\AbcVoice($voiceId, $meta['name'] ?? '', $meta['sname'] ?? '');
            }
        }
        return $result;
    }
    /**
     * Parse a block of ABC text into an AbcTune object (recursive descent entry point).
     * @param string $abcText
     * @return AbcTune|null
     */
    public static function parse($abcText)
    {
                $lines = preg_split('/\r?\n/', $abcText);
                $tune = new self();
                $headerDone = false;
                $headerLines = [];
                $bodyLines = [];
                // Split header and body
                foreach ($lines as $line) {
                    $trimmed = trim($line);
                    if (!$headerDone && ($trimmed === '' || preg_match('/^[A-Z]:/', $trimmed))) {
                        $headerLines[] = $line;
                        if ($trimmed !== '' && preg_match('/^K:/', $trimmed)) {
                            $headerDone = true;
                        }
                    } else {
                        $bodyLines[] = $line;
                    }
                }
                // Parse header fields
                foreach ($headerLines as $line) {
                    if (preg_match('/^([A-Z]):(.*)$/', trim($line), $m)) {
                        $key = $m[1];
                        $value = $m[2];
                        $tune->addHeader($key, $value);
                    }
                }
                // Initial context from headers
                $initialContext = [
                    'voice' => null,
                    'key' => (isset($tune->headers['K']) && $tune->headers['K']) ? $tune->headers['K']->get() : "HP",
                    'meter' => (isset($tune->headers['M']) && $tune->headers['M']) ? $tune->headers['M']->get() : "4/4",
                    'length' => (isset($tune->headers['L']) && $tune->headers['L']) ? $tune->headers['L']->get() : "1/8",
                ];
                $ctxMgr = new \Ksfraser\PhpabcCanntaireachd\ContextManager($initialContext);
                $voiceBlocks = [];
                $currentVoiceId = null;
                $currentVoiceLines = [];
                foreach ($bodyLines as $line) {
                    $trimmed = trim($line);
                    // Apply context changes
                    $ctxMgr->applyToken($trimmed);
                    // Voice change
                    if (preg_match('/^(?:\[)?V:([^\s\]]+)(?:\])?/', $trimmed, $m)) {
                        if ($currentVoiceId && count($currentVoiceLines) > 0) {
                            $voiceBlocks[$currentVoiceId] = $currentVoiceLines;
                        }
                        $currentVoiceId = $m[1];
                        $currentVoiceLines = [$line];
                        continue;
                    }
                    if ($currentVoiceId) {
                        $currentVoiceLines[] = $line;
                    }
                }
                if ($currentVoiceId && count($currentVoiceLines) > 0) {
                    $voiceBlocks[$currentVoiceId] = $currentVoiceLines;
                }
                // If no V: lines, treat as single default voice
                if (empty($voiceBlocks)) {
                    $voiceBlocks['default'] = $bodyLines;
                }
                $tune->voiceBars = [];
                foreach ($voiceBlocks as $voiceId => $voiceLines) {
                    // AbcVoice::parse does not exist; create AbcVoice and parse bars directly
                    $bars = [];
                    foreach ($voiceLines as $line) {
                        $line = trim($line);
                        if ($line === '' || preg_match('/^[A-Z]:/', $line)) continue;
                        // Split line into bars
                        $barTexts = preg_split('/\|/', $line);
                        foreach ($barTexts as $barText) {
                            $barText = trim($barText);
                            if ($barText !== '') {
                                $bar = new AbcBar($barText, '|');
                                $bar->parseBarRecursive($barText, $ctxMgr);
                                $bars[] = $bar;
                            }
                        }
                    }
                    $tune->voiceBars[$voiceId] = $bars;
                }
                return $tune;
            }
    protected $currentVoice = null;
    /**
     * Recursively parse a line into voices and bars.
     * @param string $line
     */
    public function parseLineRecursive($line) {
        // Voice change
        if (preg_match('/^(?:\[)?V:([^\s\]]+)(?:\])?/', trim($line), $m)) {
            $voiceId = $m[1];
            if (!isset($this->voiceBars[$voiceId])) {
                $this->voiceBars[$voiceId] = [];
            }
            $this->currentVoice = $voiceId;
            return;
        }
        // If no voice, create default
        if (!isset($this->currentVoice)) {
            $this->currentVoice = 'default';
            if (!isset($this->voiceBars[$this->currentVoice])) {
                $this->voiceBars[$this->currentVoice] = [];
            }
        }
        // Split line into bars
        $bars = preg_split('/\|/', $line);
        // Ensure $ctxMgr is available
        static $ctxMgrInstance = null;
        if ($ctxMgrInstance === null) {
            $initialContext = [
                'voice' => null,
                'key' => $this->headers['K']->get() ?? null,
                'meter' => $this->headers['M']->get() ?? null,
                'length' => $this->headers['L']->get() ?? null,
            ];
            $ctxMgrInstance = new \Ksfraser\PhpabcCanntaireachd\ContextManager($initialContext);
        }
        foreach ($bars as $barText) {
            $barText = trim($barText);
            if ($barText !== '') {
                $bar = new AbcBar($barText);
                $bar->parseBarRecursive($barText, $ctxMgrInstance);
                $this->voiceBars[$this->currentVoice][] = $bar;
            }
        }
    }
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
        // Render body/music lines for each voice
        foreach ($this->voiceBars as $voiceId => $bars) {
            // Output V: line for each voice (if not already output above)
            if (!isset($this->voices[$voiceId])) {
                $out .= "V:$voiceId\n";
            }
            foreach ($bars as $barObj) {
                if (method_exists($barObj, 'render')) {
                    $out .= $barObj->render();
                } elseif (is_string($barObj)) {
                    $out .= $barObj . "\n";
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
        // Voice change
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
