<?php
namespace Ksfraser\PhpabcCanntaireachd;
/**
 * Class AbcFileParser
 *
 * Parses ABC files into AbcTune objects, handling multiple tunes per file.
 * Applies header defaults, supports configurable parsing policies, and delegates line parsing to specialized parsers.
 *
 * SOLID: Single Responsibility (parsing files into tunes), Dependency Injection (configurable policies), DRY (delegates to parsers).
 *
 * @package Ksfraser\PhpabcCanntaireachd
 *
 * @property string $singleHeaderPolicy Policy for single-value header fields ('first' or 'last')
 * @property bool $updateVoiceNamesFromMidi Whether to update voice names from MIDI program info
 *
 * @method __construct(array $config) Constructor with DI for config
 * @method array parse(string $abcContent) Parse ABC file content into array of AbcTune objects
 *
 * @uml
 * @startuml
 * class AbcFileParser {
 *   - singleHeaderPolicy: string
 *   - updateVoiceNamesFromMidi: bool
 *   + __construct(config: array)
 *   + parse(abcContent: string): AbcTune[]
 * }
 * AbcFileParser --> AbcTune
 * AbcFileParser --> HeaderParser
 * AbcFileParser --> FormattingParser
 * AbcFileParser --> MidiParser
 * AbcFileParser --> CommentParser
 * AbcFileParser --> BodyParser
 * @enduml
 */

use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderAll;
use Ksfraser\PhpabcCanntaireachd\Tune\AbcTune;
use Ksfraser\PhpabcCanntaireachd\Tune\AbcBar;
use Ksfraser\PhpabcCanntaireachd\Midi\MidiParser;

class AbcFileParser {
    /**
     * Recursively parse ABC file content into tunes, voices, bars, and notes.
     * @param string $abcContent
     * @return AbcTune[]
     */
    /**
     * @param string $abcContent
     * @return array AbcTune[]
     */
    public function parseRecursive($abcContent) {
        $lines = preg_split('/\r?\n/', $abcContent);
        $tunes = [];
        $currentTune = null;
        foreach ($lines as $idx => $line) {
            if (preg_match('/^X:/', $line)) {
                if ($currentTune) $tunes[] = $currentTune;
                $currentTune = new \Ksfraser\PhpabcCanntaireachd\Tune\AbcTune();
                $currentTune->addHeader('X', substr($line, 2));
                continue;
            }
            if (!$currentTune) continue;
            if (trim($line) === '') continue;
            $currentTune->parseLineRecursive($line);
        }
        if ($currentTune) $tunes[] = $currentTune;
        return $tunes;
    }
    /**
     * Config: 'first' or 'last' for single-value header fields
     */
    protected $singleHeaderPolicy = 'last';
    
    /**
     * Config: whether to update voice names from MIDI program information
     */
    protected $updateVoiceNamesFromMidi = false;

    /**
     * @var HeaderParser
     */
    protected $headerParser;
    /**
     * @var FormattingParser
     */
    protected $formattingParser;

    /**
     * @var MidiParser
     */
    protected $midiParser;

    /**
     * @var CommentParser
     */
    protected $commentParser;

    /**
     * @var BodyParser
     */
    protected $bodyParser;

    public function __construct($config = [], $headerParser = null, $formattingParser = null, $midiParser = null, $commentParser = null, $bodyParser = null) {
        if (isset($config['singleHeaderPolicy'])) {
            switch ($config['singleHeaderPolicy']) {
                case 'first':
                case 'last':
            		$this->singleHeaderPolicy = $config['singleHeaderPolicy'];
                    break;
                default:
                    throw new \InvalidArgumentException("Invalid singleHeaderPolicy: " . $config['singleHeaderPolicy']);
            }
        }
        
        if (isset($config['updateVoiceNamesFromMidi'])) {
            $this->updateVoiceNamesFromMidi = (bool)$config['updateVoiceNamesFromMidi'];
        }
        // Inject parsers or use defaults
        $this->headerParser = $headerParser ?: new HeaderParser();
        $this->formattingParser = $formattingParser ?: new FormattingParser();
        $this->midiParser = $midiParser ?: new MidiParser();
        $this->commentParser = $commentParser ?: new CommentParser();
        $this->bodyParser = $bodyParser ?: new BodyParser();
    }

    /**
     * Parse ABC file content into an array of AbcTune objects.
     * @param string $abcContent
     * @return AbcTune[]
     */
    /**
     * @param string $abcContent
     * @return array AbcTune[]
     */
    public function parse($abcContent) {
        $lines = preg_split('/\r?\n/', $abcContent);
        $tuneBlocks = [];
        $currentBlock = [];
        foreach ($lines as $line) {
            // Start of new tune (X: field)
            if (preg_match('/^X:/', $line) && count($currentBlock) > 0) {
                $tuneBlocks[] = implode("\n", $currentBlock);
                $currentBlock = [];
            }
            $currentBlock[] = $line;
        }
        if (count($currentBlock) > 0) {
            $tuneBlocks[] = implode("\n", $currentBlock);
        }
        $tunes = [];
        foreach ($tuneBlocks as $tuneText) {
            $tune = AbcTune::parse($tuneText);
            if ($tune) $tunes[] = $tune;
        }
        // Centralized header defaults loader
        $headerDefaults = \Ksfraser\PhpabcCanntaireachd\HeaderDefaults::getDefaults();
        // Fill missing header fields with defaults
        foreach ($tunes as $tune) {
            $headers = $tune->getHeaders();
            foreach ($headerDefaults as $key => $value) {
                // Apply default when header missing or present but empty
                if (!isset($headers[$key]) || (method_exists($headers[$key], 'get') && $headers[$key]->get() === '')) {
                    $tune->replaceHeader($key, $value);
                }
            }
        }
        // Remove nulls
        return array_filter($tunes);
    }
}
