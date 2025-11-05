<?php
namespace Ksfraser\PhpabcCanntaireachd;

/**
 * Class MidiParser
 *
 * General MIDI directive parser that delegates to specific parsers.
 * Follows SRP by coordinating MIDI directive parsing.
 *
 * @requirement FR10 (MIDI directive parsing)
 * @uml
 * @startuml
 * class MidiParser {
 *   - parsers: array
 *   + __construct()
 *   + parse(line: string): array|null
 * }
 * MidiParser --> MidiChannelParser : uses
 * MidiParser --> MidiProgramParser : uses
 * MidiParser --> MidiBeatParser : uses
 * @enduml
 */
class MidiParser {
    private $parsers;

    public function __construct() {
        $this->parsers = [
            'channel' => new MidiChannelParser(),
            'program' => new MidiProgramParser(),
            'beat' => new MidiBeatParser(),
            'transpose' => new MidiTransposeParser(),
            'volume' => new MidiVolumeParser(),
            'ratio' => new MidiRatioParser(),
            'middleC' => new MidiMiddleCParser(),
            'gchord' => new MidiGchordParser(),
            'gchordcontrol' => new MidiGchordControlParser(),
            'gracedivider' => new MidiGraceDividerParser(),
            'barlines' => new MidiBarlinesParser(),
            'beatstring' => new MidiBeatStringParser(),
            'accompanimentvolume' => new MidiAccompanimentVolumeParser(),
            'accompanimentprogram' => new MidiAccompanimentProgramParser()
        ];
    }

    /**
     * Parses any %%MIDI directive line.
     *
     * @param string $line The ABC line to parse.
     * @return array|null Array with 'type' and 'value' or null if not a MIDI directive.
     * @requirement FR10
     */
    public function parse(string $line): ?array {
        $trimmed = trim($line);
        if (!preg_match('/^%%MIDI\s+(\w+)\s+(.+)$/', $trimmed, $matches)) {
            return null;
        }
        $type = $matches[1];
        if (!isset($this->parsers[$type])) {
            return null;
        }
        $value = $this->parsers[$type]->parse($line);
        if ($value === null) {
            return null;
        }
        return ['type' => $type, 'value' => $value];
    }
}
