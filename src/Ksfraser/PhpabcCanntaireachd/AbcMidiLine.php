<?php
namespace Ksfraser\PhpabcCanntaireachd;

class AbcMidiLine extends AbcItem {
    protected $instruction;

    public function __construct($instruction) {
        $this->instruction = $instruction;
    }

    protected function renderSelf(): string {
        $instruction = $this->instruction;
        
        // Enhance MIDI program lines with instrument names
        if (preg_match('/^%%MIDI\s+program\s+(\d+)/i', $instruction, $matches)) {
            $program = (int)$matches[1];
            $instrument = MidiInstrumentMapper::getInstrument($program);
            if ($instrument) {
                // Add instrument name as comment if not already present
                if (!preg_match('/%\s*' . preg_quote($instrument['name'], '/') . '/i', $instruction)) {
                    $instruction .= " % " . $instrument['name'];
                }
            }
        }
        
        return $instruction . "\n";
    }
}
