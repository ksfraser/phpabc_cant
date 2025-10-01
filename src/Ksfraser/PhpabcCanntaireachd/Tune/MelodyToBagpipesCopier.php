<?php
namespace Ksfraser\PhpabcCanntaireachd\Tune;

use Ksfraser\PhpabcCanntaireachd\Tune\VoiceDetectionContext;

/**
 * Copies melody lines to a Bagpipes voice in ABC tune data.
 * SRP: Handles only the melody-to-bagpipes transformation.
 */
class MelodyToBagpipesCopier {
    /**
     * @param array $lines ABC lines for a single tune
     * @return array Modified lines with Bagpipes voice added if needed
     */
    public function copy(array $lines): array {
        $output = [];
        $ctx = new VoiceDetectionContext();
        // First pass: find melody voice and check for Bagpipes header
        foreach ($lines as $line) {
            if (preg_match('/^V:([^ \n]+)/', $line, $m)) {
                $voiceId = $m[1];
                if ($voiceId === 'M' || $voiceId === 'Melody' || preg_match('/name=.*Melody/', $line)) {
                    $ctx->hasMelody = true;
                    $ctx->melodyVoiceId = $voiceId;
                    $ctx->melodyHeaderLine = $line;
                }
                if ($voiceId === 'Bagpipes') {
                    $ctx->bagpipesHeaderPresent = true;
                    $ctx->bagpipesHeader = $line;
                }
            }
        }
        if (!$ctx->hasMelody) {
            return $lines;
        }
        // Insert Bagpipes header if missing
        foreach ($lines as $line) {
            if (!$ctx->bagpipesHeaderPresent && !$ctx->inserted && $ctx->melodyHeaderLine !== null && $line === $ctx->melodyHeaderLine) {
                // Insert after melody header
                $output[] = 'V:Bagpipes name="Bagpipes" clef=treble octave=0';
                $ctx->inserted = true;
            }
            $output[] = $line;
        }
        if ($ctx->bagpipesHeaderPresent) {
            $output = $lines;
        }
        // Second pass: copy melody content for Bagpipes
        $melodyContent = [];
        if ($ctx->melodyVoiceId !== null) {
            foreach ($lines as $line) {
                if (preg_match('/^\[V:' . preg_quote($ctx->melodyVoiceId, '/') . '\](.*)$/i', $line, $m)) {
                    // This is melody content, copy it for bagpipe
                    $bagpipeLine = '[V:Bagpipes]' . $m[1];
                    $melodyContent[] = $bagpipeLine;
                }
            }
        }
        // Add bagpipe voice with copied content (header line should be added in header logic, not here)
        if (!empty($melodyContent)) {
            $output[] = '%canntaireachd: <add your canntaireachd here>';
            $output = array_merge($output, $melodyContent);
        }
        return $output;
    }
}
