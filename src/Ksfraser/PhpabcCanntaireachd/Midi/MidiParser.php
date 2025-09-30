<?php
namespace Ksfraser\PhpabcCanntaireachd\Midi;

use Ksfraser\PhpabcCanntaireachd\AbcLineParser;
use Ksfraser\PhpabcCanntaireachd\AbcTune;
use Ksfraser\PhpabcCanntaireachd\AbcMidiLine;
/**
 * Parser for MIDI directive lines (%%MIDI, etc.)
 */
class MidiParser implements \Ksfraser\PhpabcCanntaireachd\AbcLineParser {
    /**
     * @param mixed $line
     * @return bool
     */
    public function canParse($line) {
        return preg_match('/^%%MIDI/i', trim($line));
    }

    /**
     * @param mixed $line
     * @param mixed $tune
     * @return bool
     */
    public function parse($line, $tune) {
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

    /**
     * @param mixed $line
     * @return bool
     */
    public function validate($line) {
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
