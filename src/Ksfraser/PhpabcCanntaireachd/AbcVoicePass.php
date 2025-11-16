<?php
namespace Ksfraser\PhpabcCanntaireachd;

use Ksfraser\PhpabcCanntaireachd\Log\FlowLog;

class AbcVoicePass {
    public function process(array $lines, $logFlow = false): array {
        FlowLog::log('AbcVoicePass::process ENTRY', true);
        
        // Parse → Transform → Render approach
        $abcText = implode("\n", $lines);
        $tune = \Ksfraser\PhpabcCanntaireachd\Tune\AbcTune::parse($abcText);
        
        if (!$tune) {
            FlowLog::log('AbcVoicePass: Failed to parse tune, returning original lines', true);
            return $lines;
        }
        
        // Find Melody voice with bars (music content, not just header)
        $melodyVoiceId = null;
        foreach (['M', 'Melody'] as $id) {
            $bars = $tune->getBarsForVoice($id);
            if ($bars && count($bars) > 0) {
                $melodyVoiceId = $id;
                if ($logFlow) {
                    FlowLog::log("AbcVoicePass: Found melody voice '$id' with " . count($bars) . " bars", true);
                }
                break;
            }
        }
        
        // Check if Bagpipes voice already has bars (music content)
        $bagpipesExists = false;
        foreach (['Bagpipes', 'Pipes', 'P'] as $id) {
            $bars = $tune->getBarsForVoice($id);
            if ($bars && count($bars) > 0) {
                $bagpipesExists = true;
                if ($logFlow) {
                    FlowLog::log("AbcVoicePass: Found bagpipes voice '$id' with bars", true);
                }
                break;
            }
        }
        
        // Copy melody bars to Bagpipes voice if needed
        if ($melodyVoiceId && !$bagpipesExists) {
            if ($logFlow) {
                FlowLog::log('AbcVoicePass: Copying melody bars to Bagpipes voice', true);
            }
            
            $melodyBars = $tune->getBarsForVoice($melodyVoiceId);
            
            // Add Bagpipes voice metadata
            $tune->voices['Bagpipes'] = [
                'name' => 'Bagpipes',
                'sname' => 'Bagpipes'
            ];
            
            // Copy bars to Bagpipes voice
            $tune->voiceBars['Bagpipes'] = $melodyBars;
            
            if ($logFlow) {
                FlowLog::log('AbcVoicePass: Copied ' . count($melodyBars) . ' bars to Bagpipes', true);
            }
        }
        
        // Render back to lines
        $rendered = $tune->renderSelf();
        $result = preg_split('/\r?\n/', $rendered);
        // Remove empty trailing line if present
        if (end($result) === '') {
            array_pop($result);
        }
        
        FlowLog::log('AbcVoicePass::process EXIT', true);
        return $result;
    }
}
