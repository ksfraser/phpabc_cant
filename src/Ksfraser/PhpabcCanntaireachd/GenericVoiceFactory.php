// MOVED TO Voices/GenericVoiceFactory.php
<?php
namespace Ksfraser\PhpabcCanntaireachd;

/**
 * GenericVoiceFactory: Creates AbcVoice objects with generic defaults.
 * Extends VoiceFactory with no additional defaults.
 */
class GenericVoiceFactory extends VoiceFactory {
    public function __construct() {
        parent::__construct();
    }
}
