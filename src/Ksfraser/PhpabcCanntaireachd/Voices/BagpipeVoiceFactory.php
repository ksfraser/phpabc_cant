<?php
namespace Ksfraser\PhpabcCanntaireachd\Voices;

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
