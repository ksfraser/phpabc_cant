<?php
namespace Ksfraser\PhpabcCanntaireachd\Midi;

use Ksfraser\PhpabcCanntaireachd\AbcLineParser;
use Ksfraser\PhpabcCanntaireachd\AbcTune;
use Ksfraser\PhpabcCanntaireachd\AbcMidiLine;
/**
 * Parser for MIDI directive lines (%%MIDI, etc.)
 */
class MidiParser implements \Ksfraser\PhpabcCanntaireachd\AbcLineParser {
    public function canParse(string $line): bool {
        return preg_match('/^%%MIDI/i', trim($line));
    }

    public function parse(string $line, AbcTune $tune): bool {
        if (!preg_match('/^%%MIDI/i', trim($line))) {
            return false;
        }

        // Try specialized MIDI parsers in order of specificity
        $midiParsers = [
            new MidiProgramParser(),
            new MidiVolumeParser(),
            new MidiGraceDividerParser(),
            new MidiGchordParser(),
            new MidiBeatStringParser(),
            new MidiChannelParser(),
            new MidiTransposeParser(),
            new MidiMiddleCParser(),
            new MidiAccompanimentProgramParser(),
            new MidiAccompanimentVolumeParser(),
            new MidiGchordControlParser(),
            new MidiBeatParser(),
            new MidiRatioParser(),
            new MidiBarlinesParser(),
        ];

        foreach ($midiParsers as $parser) {
            if ($parser->canParse($line)) {
                return $parser->parse($line, $tune);
            }
        }

        // Fallback: handle unknown MIDI directives
        $tune->add(new AbcMidiLine($line));
        return true;
    }

    public function validate(string $line): bool {
        if (!preg_match('/^%%MIDI/i', trim($line))) {
            return false;
        }

        // Try specialized MIDI parsers for validation
        $midiParsers = [
            new MidiProgramParser(),
            new MidiVolumeParser(),
            new MidiGraceDividerParser(),
            new MidiGchordParser(),
            new MidiBeatStringParser(),
            new MidiChannelParser(),
            new MidiTransposeParser(),
            new MidiMiddleCParser(),
            new MidiAccompanimentProgramParser(),
            new MidiAccompanimentVolumeParser(),
            new MidiGchordControlParser(),
            new MidiBeatParser(),
            new MidiRatioParser(),
            new MidiBarlinesParser(),
        ];

        foreach ($midiParsers as $parser) {
            if ($parser->canParse($line)) {
                return $parser->validate($line);
            }
        }

        // Unknown MIDI directive - consider valid for now
        return true;
    }
}
