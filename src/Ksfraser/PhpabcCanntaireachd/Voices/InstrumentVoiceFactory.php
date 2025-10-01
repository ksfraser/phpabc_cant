<?php
namespace Ksfraser\PhpabcCanntaireachd\Voices;

class InstrumentVoiceFactory
{
    /**
     * Create an AbcVoice for a given instrument, indicator, name, sname, etc.
     * Falls back to GenericVoiceFactory if instrument is unknown.
     */
    public static function createVoiceFromParams($indicator, $name = '', $sname = '', $stem = null, $gstem = null, $octave = 0, $transpose = 0, $callback = null, $clef = null)
    {
        $iname = strtolower($name ?: $sname ?: $indicator);
        switch (true) {
            case $iname === 'melody' || ($indicator === 'M' && ($name === 'Melody' || $sname === 'Melody')):
                // Melody voice
                return (new VoiceFactory('M', 'Melody', 'Melody', 'down', 'up', 0, 0, 'add_melody'))->createVoice();
            case $iname === 'bagpipes' || $indicator === 'Bagpipes':
                return (new VoiceFactory('Bagpipes', 'Bagpipes', 'Bagpipes', 'down', 'up', 0, 0, 'add_bagpipes'))->createVoice();
            case $iname === 'trumpet' || $indicator === 'Tpt':
                return (new VoiceFactory('Tpt', 'Trumpet', 'Trumpet', 'up', 'up', 0, 0, 'add_trumpet', 'treble'))->createVoice();
            case $iname === 'guitar' || $indicator === 'Gtr':
                return (new VoiceFactory('Gtr', 'Guitar', 'Guitar', 'down', 'down', 0, 0, 'add_guitar', 'treble'))->createVoice();
            case $iname === 'piano' || $indicator === 'Pno':
                return (new VoiceFactory('Pno', 'Piano', 'Piano', 'down', 'up', 0, 0, 'add_piano', 'treble'))->createVoice();
            case $iname === 'bass' || $indicator === 'B':
                return (new VoiceFactory('B', 'Bass', 'Bass', 'down', 'up', -1, 0, 'add_bass', 'bass'))->createVoice();
            case $iname === 'tenor' || $indicator === 'T':
                return (new VoiceFactory('T', 'Tenor', 'Tenor', 'down', 'up', 0, 0, 'add_tenor', 'tenor'))->createVoice();
            case $iname === 'snare' || $indicator === 'S':
                return (new VoiceFactory('S', 'Snare', 'Snare', 'down', 'up', 0, 0, 'add_snare'))->createVoice();
            case $iname === 'harmony' || $indicator === 'H':
                return (new VoiceFactory('H', 'Harmony', 'Harmony', 'down', 'up', 0, 0, 'add_harmony'))->createVoice();
            case $iname === 'c-harmony' || $indicator === 'C':
                return (new VoiceFactory('C', 'C-Harmony', 'C-Harmony', 'down', 'up', 0, 0, 'add_c_harmony'))->createVoice();
            // Add more instruments as needed
            default:
                return (new GenericVoiceFactory())->createVoice();
        }
    }
}
