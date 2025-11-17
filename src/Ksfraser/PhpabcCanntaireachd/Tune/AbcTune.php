<?php
namespace Ksfraser\PhpabcCanntaireachd\Tune;

// Exception to signal a line was handled by a handler
class LineHandledException extends \Exception {}

// Example handler class for header lines
class HeaderLineHandler {
    public function matches($line) {
        $trimmed = trim($line);
        return ($trimmed === '' || $trimmed[0] === '%' || preg_match('/^(X:|T:|M:|K:|V:)/', $trimmed));
    }
    public function parse($line, &$context) {
        $trimmed = trim($line);
        $context['headerLines'][] = $line;
        
        // Handle V: voice definition lines
        if (preg_match('/^V:([\w\-]+)/', $trimmed, $m)) {
            $voiceId = $m[1];
            if (!isset($context['voices'][$voiceId])) {
                // Create appropriate voice object
                if (strcasecmp($voiceId, 'Bagpipes') === 0 || strcasecmp($voiceId, 'Pipes') === 0 || strcasecmp($voiceId, 'P') === 0) {
                    $context['voices'][$voiceId] = new \Ksfraser\PhpabcCanntaireachd\Voices\BagpipeVoice($voiceId, $voiceId, '');
                } else {
                    $context['voices'][$voiceId] = new \Ksfraser\PhpabcCanntaireachd\Voices\AbcVoice($voiceId, $voiceId, '');
                }
            }
            // Set as current voice
            $context['currentVoice'] = $voiceId;
        }
        
        // If K: header, mark header as done
        if (preg_match('/^K:/', $trimmed)) {
            $context['inHeader'] = false;
        }
        throw new LineHandledException();
    }
    }
// Example handler class for body/music lines (simplified)
class BarLineHandler {
    public function matches($line) {
        $trimmed = trim($line);
        return ($trimmed !== '' && $trimmed[0] !== '%' && !preg_match('/^(X:|T:|M:|K:|V:|%%|I:|Q:|L:)/', $trimmed));
    }
    public function parse($line, &$context) {
        $trimmed = trim($line);
        
        // Check for inline voice marker [V:id] at start of line
        if (preg_match('/^\[V:([\w\-]+)\]/', $trimmed, $m)) {
            $voiceId = $m[1];
            if (!isset($context['voices'][$voiceId])) {
                // Create appropriate voice object
                if (strcasecmp($voiceId, 'Bagpipes') === 0 || strcasecmp($voiceId, 'Pipes') === 0 || strcasecmp($voiceId, 'P') === 0) {
                    $context['voices'][$voiceId] = new \Ksfraser\PhpabcCanntaireachd\Voices\BagpipeVoice($voiceId, $voiceId, '');
                } else {
                    $context['voices'][$voiceId] = new \Ksfraser\PhpabcCanntaireachd\Voices\AbcVoice($voiceId, $voiceId, '');
                }
            }
            $context['currentVoice'] = $voiceId;
            // Remove the inline marker from the line for bar parsing
            $trimmed = preg_replace('/^\[V:[\w\-]+\]\s*/', '', $trimmed);
        }
        
        $currentVoice = $context['currentVoice'];
        if ($currentVoice === null) {
            // Skip bars if no voice is set (shouldn't happen with proper ABC files)
            return;
        }
        
        // Parse bars from the line
        $barTexts = preg_split('/\|/', $trimmed);
        foreach ($barTexts as $barText) {
            $barText = trim($barText);
            if ($barText !== '' && isset($context['voices'][$currentVoice])) {
                $bar = new \Ksfraser\PhpabcCanntaireachd\Tune\AbcBar($barText, '|');
                $context['voices'][$currentVoice]->bars[] = $bar;
            }
        }
        throw new LineHandledException();
    }
}
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
use Ksfraser\PhpabcCanntaireachd\Tune\AbcBar;
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
        // Fallback: if $this->voices is an array of metadata or AbcVoice objects
        if (empty($result) && isset($this->voices) && is_array($this->voices)) {
            foreach ($this->voices as $voiceId => $meta) {
                if ($meta instanceof \Ksfraser\PhpabcCanntaireachd\Voices\AbcVoice) {
                    $result[$voiceId] = $meta;
                } else {
                    $result[$voiceId] = new \Ksfraser\PhpabcCanntaireachd\Voices\AbcVoice($voiceId, $meta['name'] ?? '', $meta['sname'] ?? '');
                }
            }
        }
        return $result;
    }
    
    /**
     * Add a voice with metadata and bars
     * @param string $voiceId Voice identifier
     * @param array $metadata Voice metadata (name, sname, etc.)
     * @param array $bars Array of bar objects
     */
    public function addVoice(string $voiceId, array $metadata, array $bars): void {
        $this->voices[$voiceId] = $metadata;
        $this->voiceBars[$voiceId] = $bars;
    }
    
    /**
     * Get bars for a specific voice by ID
     * @param string $voiceId Voice identifier
     * @return array|null Array of bars or null if voice doesn't exist
     */
    public function getBarsForVoice(string $voiceId): ?array {
        return $this->voiceBars[$voiceId] ?? null;
    }
    
    /**
     * Check if a voice exists
     * @param string $voiceId Voice identifier
     * @return bool True if voice exists
     */
    public function hasVoice(string $voiceId): bool {
        return isset($this->voices[$voiceId]) || isset($this->voiceBars[$voiceId]);
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
        // Context for handlers
        $context = [
            'headerLines' => [],
            'formattingLines' => [],
            'voices' => [],
            'currentVoice' => null,
            'inHeader' => true
        ];
        // Handler chain (add more as needed)
        $handlers = [
            new \Ksfraser\PhpabcCanntaireachd\Tune\HeaderLineHandler(),
            new \Ksfraser\PhpabcCanntaireachd\Tune\BarLineHandler(),
            // Add: InstructionHandler, InfoFieldHandler, VoiceLineHandler, etc.
        ];
        foreach ($lines as $line) {
            foreach ($handlers as $handler) {
                try {
                    if ($handler->matches($line)) {
                        $handler->parse($line, $context);
                    }
                } catch (\Ksfraser\PhpabcCanntaireachd\Tune\LineHandledException $e) {
                    // Line was handled, move to next line
                    break;
                }
            }
        }
        // Assign context to tune properties
        $tune->headerLines = $context['headerLines'];
        $tune->formattingLines = $context['formattingLines'];
        
        // Convert Voice objects to metadata arrays and populate voiceBars
        $voiceMetadata = [];
        foreach ($context['voices'] as $voiceId => $voiceObj) {
            if (is_object($voiceObj)) {
                // Store metadata as array for renderSelf compatibility
                $voiceMetadata[$voiceId] = [
                    'name' => method_exists($voiceObj, 'getName') ? $voiceObj->getName() : $voiceId,
                    'sname' => $voiceId
                ];
                // Populate voiceBars from Voice objects' bars for rendering
                if (isset($voiceObj->bars) && is_array($voiceObj->bars)) {
                    $tune->voiceBars[$voiceId] = $voiceObj->bars;
                }
            }
        }
        $tune->voices = $voiceMetadata;
        
        return $tune;

    }

    // Helper: is this a header line?
    private static function isHeaderLine($trimmed) {
        return ($trimmed === '' || $trimmed[0] === '%' || preg_match('/^(X:|T:|M:|K:|V:)/', $trimmed));
    }
    // Helper: is this an instruction line (%% or I:)?
    private static function isInstructionLine($trimmed) {
        return (preg_match('/^(%%|I:)/', $trimmed));
    }
    // Helper: is this an info field (M:, L:, Q:)?
    private static function isInfoField($trimmed) {
        return (preg_match('/^(M:|L:|Q:)/', $trimmed));
    }
    // Helper: is this a voice line?
    private static function isVoiceLine($trimmed) {
        return (preg_match('/^V:([\w\-]+)/', $trimmed));
    }
    // Helper: is this a bar/music line?
    private static function isBarLine($trimmed) {
        // Not a header, instruction, info, or voice line, and not empty/comment
        return ($trimmed !== '' && $trimmed[0] !== '%' && !preg_match('/^(X:|T:|M:|K:|V:|%%|I:|Q:|L:)/', $trimmed));
    }
    // Helper: handle instruction line
    private static function handleInstructionLine($trimmed, &$currentVoice, &$voices, &$formattingLines) {
        // For now, just add to formattingLines. TODO: handle %%MIDI and attach to voice.
        $formattingLines[] = $trimmed;
    }
    // Helper: handle info field (M, L, Q)
    private static function handleInfoField($trimmed, $currentVoice, &$voices, $currentM, $currentL, $currentQ) {
        if (preg_match('/^M:(.*)/', $trimmed, $m)) {
            $currentM = trim($m[1]);
        }
        if (preg_match('/^L:(.*)/', $trimmed, $m)) {
            $currentL = trim($m[1]);
        }
        if (preg_match('/^Q:(.*)/', $trimmed, $m)) {
            $currentQ = trim($m[1]);
        }
        // TODO: Attach to currentVoice if needed
        return [$currentM, $currentL, $currentQ];
    }
    // Helper: handle voice line
    private static function handleVoiceLine($trimmed, &$voices) {
        if (preg_match('/^V:([\w\-]+)/', $trimmed, $m)) {
            $voiceId = $m[1];
            if (!isset($voices[$voiceId])) {
                if (strcasecmp($voiceId, 'Bagpipes') === 0) {
                    $voices[$voiceId] = new \Ksfraser\PhpabcCanntaireachd\Voices\BagpipeVoice($voiceId, 'Bagpipes', '');
                } else {
                    $voices[$voiceId] = new \Ksfraser\PhpabcCanntaireachd\Voices\AbcVoice($voiceId, $voiceId, '');
                }
            }
            return $voiceId;
        }
        return null;
    }
    // Helper: parse bars from a line
    private static function parseBarsFromLine($line, $currentM, $currentL, $currentQ, $currentVoice, &$voices, &$softErrors) {
        if ($currentVoice === null) {
            $softErrors[] = 'No voice defined for bar line: ' . $line;
            return [];
        }
        $barTexts = preg_split('/\|/', $line);
        $bars = [];
        foreach ($barTexts as $barText) {
            $barText = trim($barText);
            if ($barText !== '') {
                $bar = new AbcBar($barText, '|');
                $bar->meter = $currentM;
                $bar->length = $currentL;
                $bar->tempo = $currentQ;
                $voices[$currentVoice]->bars[] = $bar;
                $bars[] = $bar;
            }
        }
        return $bars;
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
            // Handle both array format and Voice object format
            if (is_array($meta)) {
                $name = $meta['name'] ?? $voiceId;
                $sname = $meta['sname'] ?? $name;
            } elseif (is_object($meta) && method_exists($meta, 'getName')) {
                $name = $meta->getName() ?? $voiceId;
                // Voice objects don't expose sname via getter, use name as fallback
                $sname = $name;
            } else {
                $name = $voiceId;
                $sname = $voiceId;
            }
            $out .= "V:$voiceId name=\"$name\" sname=\"$sname\"\n";
        }
        // Render any other headers not in headerOrder
        foreach ($this->headers as $k => $h) {
            if (!isset(self::$headerOrder[$k]) && method_exists($h, 'render')) {
                $out .= $h->render();
            }
        }
        // Render body/music lines for each voice with [V:ID] inline markers
        foreach ($this->voiceBars as $voiceId => $bars) {
            if (empty($bars)) {
                continue;
            }
            
            // Output [V:ID] marker before this voice's bars
            $out .= "[V:$voiceId]";
            
            // Render bars and collect canntaireachd syllables
            $barLines = [];
            $canntLines = [];
            
            foreach ($bars as $barObj) {
                // Render the bar music
                if (method_exists($barObj, 'render')) {
                    $barLines[] = $barObj->render();
                } elseif (is_string($barObj)) {
                    $barLines[] = $barObj;
                } else {
                    $barLines[] = '';
                }
                
                // Extract canntaireachd from notes in this bar
                $barCannt = $this->extractCanntaireachdFromBar($barObj);
                $canntLines[] = $barCannt;
            }
            
            // Output bars on the same line as [V:ID]
            $out .= implode('', $barLines);
            
            // Output w: lines if any bars have canntaireachd
            $hasAnyCannt = false;
            foreach ($canntLines as $line) {
                if (!empty($line)) {
                    $hasAnyCannt = true;
                    break;
                }
            }
            
            if ($hasAnyCannt) {
                $out .= "w: " . implode('|', $canntLines) . "\n";
            } else {
                // No canntaireachd, just end the line
                $out .= "\n";
            }
        }
        return $out;
    }
    
    /**
     * Extract canntaireachd syllables from a bar's notes
     * 
     * @param mixed $barObj Bar object
     * @return string Space-separated canntaireachd syllables for this bar
     */
    private function extractCanntaireachdFromBar($barObj): string
    {
        $syllables = [];
        
        // Check if bar has notes property
        if (!isset($barObj->notes) || !is_array($barObj->notes)) {
            return '';
        }
        
        // Extract canntaireachd from each note
        foreach ($barObj->notes as $note) {
            if (method_exists($note, 'getCanntaireachd')) {
                $cannt = $note->getCanntaireachd();
                if (!empty($cannt)) {
                    $syllables[] = $cannt;
                }
            } elseif (method_exists($note, 'renderCanntaireachd')) {
                $cannt = $note->renderCanntaireachd();
                if (!empty($cannt)) {
                    $syllables[] = $cannt;
                }
            }
        }
        
        return implode(' ', $syllables);
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

    /**
     * Set voice bars (replaces existing voice bars)
     *
     * @param array $voiceBars Array of voice bars keyed by voice ID
     * @return void
     */
    public function setVoiceBars(array $voiceBars): void {
        $this->voiceBars = $voiceBars;
    }

    /**
     * Set voice metadata (replaces existing voices array)
     *
     * @param array $voices Array of voice metadata keyed by voice ID
     * @return void
     */
    public function setVoices(array $voices): void {
        $this->voices = $voices;
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
