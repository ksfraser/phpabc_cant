<?php
namespace Ksfraser\PhpabcCanntaireachd;

use Ksfraser\PhpabcCanntaireachd\Log\FlowLog;
use Ksfraser\PhpabcCanntaireachd\VoiceOrdering\VoiceOrderingStrategy;
use Ksfraser\PhpabcCanntaireachd\VoiceOrdering\SourceOrderStrategy;
use Ksfraser\PhpabcCanntaireachd\VoiceOrdering\OrchestralOrderStrategy;
use Ksfraser\PhpabcCanntaireachd\VoiceOrdering\CustomOrderStrategy;
use Ksfraser\PhpabcCanntaireachd\Tune\AbcTune;

class AbcVoiceOrderPass {
    /**
     * @var VoiceOrderingStrategy
     */
    private $strategy;

    /**
     * @var AbcProcessorConfig|null
     */
    private $config;

    /**
     * Constructor
     *
     * @param VoiceOrderingStrategy|null $strategy Voice ordering strategy (optional)
     * @param AbcProcessorConfig|null $config Processor configuration (optional)
     */
    public function __construct(?VoiceOrderingStrategy $strategy = null, ?AbcProcessorConfig $config = null) {
        $this->config = $config;
        
        if ($strategy !== null) {
            $this->strategy = $strategy;
        } elseif ($config !== null) {
            $this->strategy = $this->createStrategyFromConfig($config);
        } else {
            // Default to source order
            $this->strategy = new SourceOrderStrategy();
        }
    }

    /**
     * Process lines using voice ordering strategy
     *
     * @param array $lines Lines of ABC notation
     * @param bool $logFlow Enable flow logging
     * @return array Processed lines
     */
    public function process(array $lines, $logFlow = false): array {
        FlowLog::log('AbcVoiceOrderPass::process ENTRY', $logFlow);
        
        // Parse ABC text into tune object
        $abcText = implode("\n", $lines);
        $tune = AbcTune::parse($abcText);
        
        if (!$tune) {
            FlowLog::log('AbcVoiceOrderPass: Failed to parse tune, returning original lines', $logFlow);
            return $lines;
        }

        // Get voices from tune
        $voices = $tune->getVoices();
        if (empty($voices)) {
            FlowLog::log('AbcVoiceOrderPass: No voices found, returning original lines', $logFlow);
            return $lines;
        }

        // Apply voice ordering strategy
        $orderedVoices = $this->strategy->orderVoices(array_values($voices));
        
        // Log the reordering
        if ($logFlow) {
            $originalOrder = implode(', ', array_map(function($v) {
                return $v->getVoiceIndicator();
            }, array_values($voices)));
            $newOrder = implode(', ', array_map(function($v) {
                return $v->getVoiceIndicator();
            }, $orderedVoices));
            FlowLog::log("AbcVoiceOrderPass: Voice order: $originalOrder → $newOrder", $logFlow);
        }

        // Reconstruct voiceBars and voices metadata in new order
        $currentVoiceBars = $tune->getVoiceBars();
        $currentVoices = $tune->getVoices();
        $newVoiceBars = [];
        $newVoices = [];
        
        foreach ($orderedVoices as $voice) {
            $voiceId = $voice->getVoiceIndicator();
            if (isset($currentVoiceBars[$voiceId])) {
                $newVoiceBars[$voiceId] = $currentVoiceBars[$voiceId];
            }
            if (isset($currentVoices[$voiceId])) {
                $newVoices[$voiceId] = $currentVoices[$voiceId];
            }
        }
        
        // Update tune's voiceBars and voices with new order
        $tune->setVoiceBars($newVoiceBars);
        $tune->setVoices($newVoices);
        
        // Render back to lines
        $result = explode("\n", $tune->render());
        
        FlowLog::log('AbcVoiceOrderPass::process EXIT', $logFlow);
        return $result;
    }

    /**
     * Create strategy from configuration
     *
     * @param AbcProcessorConfig $config
     * @return VoiceOrderingStrategy
     */
    private function createStrategyFromConfig(AbcProcessorConfig $config): VoiceOrderingStrategy {
        $mode = $config->voiceOrderingMode ?? 'source';
        
        switch (strtolower($mode)) {
            case 'orchestral':
                return new OrchestralOrderStrategy();
            
            case 'custom':
                $customOrder = $config->customVoiceOrder ?? [];
                return new CustomOrderStrategy($customOrder);
            
            case 'source':
            default:
                return new SourceOrderStrategy();
        }
    }

    /**
     * Set the voice ordering strategy
     *
     * @param VoiceOrderingStrategy $strategy
     * @return void
     */
    public function setStrategy(VoiceOrderingStrategy $strategy): void {
        $this->strategy = $strategy;
    }

    /**
     * Get the current strategy
     *
     * @return VoiceOrderingStrategy
     */
    public function getStrategy(): VoiceOrderingStrategy {
        return $this->strategy;
    }
}
