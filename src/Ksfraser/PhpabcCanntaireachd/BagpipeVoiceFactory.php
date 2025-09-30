// MOVED TO Voices/BagpipeVoiceFactory.php
<?php
namespace Ksfraser\PhpabcCanntaireachd;

/**
 * BagpipeVoiceFactory: Creates AbcVoice objects with Bagpipes defaults.
 *
 * Extends VoiceFactory to provide Bagpipes-specific defaults.
 */
class BagpipeVoiceFactory extends VoiceFactory {
    public function __construct() {
        parent::__construct(
            'Bagpipes',
            'Bagpipes',
            'Bagpipes',
            'down',
            'up',
            0,
            0,
            null,
            null
        );
    }
}
