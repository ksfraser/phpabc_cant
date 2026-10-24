<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd;

use ksfraser\PhpabcCanntaireachd\Log\FlowLog;
use ksfraser\PhpabcCanntaireachd\VoiceOrdering\VoiceOrderingStrategy;
use ksfraser\PhpabcCanntaireachd\VoiceOrdering\SourceOrderStrategy;
use ksfraser\PhpabcCanntaireachd\VoiceOrdering\OrchestralOrderStrategy;
use ksfraser\PhpabcCanntaireachd\VoiceOrdering\CustomOrderStrategy;
use ksfraser\PhpabcCanntaireachd\Tune\AbcTune;

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

        // Split the lines into a preamble and one block per voice.
        [$preamble, $blocks] = $this->splitIntoVoiceBlocks($lines);

        if ($blocks === []) {
            FlowLog::log('AbcVoiceOrderPass: No voices found, returning original lines', $logFlow);
            return $lines;
        }

        // Apply voice ordering strategy
        $voices = [];
        foreach ($blocks as $voiceId => $block) {
            $voices[] = new \ksfraser\PhpabcCanntaireachd\Voices\AbcVoice(
                $voiceId,
                $block['name'],
                $block['sname']
            );
        }
        $orderedVoices = $this->strategy->orderVoices($voices);

        if ($logFlow) {
            $originalOrder = implode(', ', array_map(function ($v) {
                return $v->getVoiceIndicator();
            }, $voices));
            $newOrder = implode(', ', array_map(function ($v) {
                return $v->getVoiceIndicator();
            }, $orderedVoices));
            FlowLog::log("AbcVoiceOrderPass: Voice order: $originalOrder -> $newOrder", $logFlow);
        }

        // Reassemble the lines in the new order. The blocks themselves are
        // untouched, so nothing about the music changes.
        $result = $preamble;
        foreach ($orderedVoices as $voice) {
            $voiceId = $voice->getVoiceIndicator();
            if (isset($blocks[$voiceId])) {
                foreach ($blocks[$voiceId]['lines'] as $blockLine) {
                    $result[] = $blockLine;
                }
            }
        }
        // Voices the strategy dropped are kept, in their original order.
        foreach ($blocks as $voiceId => $block) {
            if (!in_array($voiceId, array_map(function ($v) {
                return $v->getVoiceIndicator();
            }, $orderedVoices), true)) {
                foreach ($block['lines'] as $blockLine) {
                    $result[] = $blockLine;
                }
            }
        }

        FlowLog::log('AbcVoiceOrderPass::process EXIT', $logFlow);
        return $result;
    }

    /**
     * Split ABC lines into a preamble and one block of lines per voice.
     *
     * A voice block starts at its "V:" header and runs until the next "V:"
     * header. The preamble is everything before the first voice.
     *
     * @param string[] $lines
     * @return array{0: string[], 1: array<string, array{lines: string[], name: string, sname: string}>}
     */
    private function splitIntoVoiceBlocks(array $lines): array
    {
        $preamble = [];
        $blocks = [];
        $currentId = null;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (preg_match('/^V:\s*(\S+)(.*)$/', $trimmed, $matches) === 1) {
                $currentId = $matches[1];
                $name = '';
                $sname = '';
                if (preg_match('/\bname="([^"]*)"/i', $matches[2], $nameMatch) === 1) {
                    $name = $nameMatch[1];
                }
                if (preg_match('/\bsname="([^"]*)"/i', $matches[2], $snameMatch) === 1) {
                    $sname = $snameMatch[1];
                }
                $blocks[$currentId] = [
                    'lines' => [$line],
                    'name' => $name,
                    'sname' => $sname,
                ];
                continue;
            }
            if ($currentId === null) {
                $preamble[] = $line;
                continue;
            }
            $blocks[$currentId]['lines'][] = $line;
        }

        return [$preamble, $blocks];
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
