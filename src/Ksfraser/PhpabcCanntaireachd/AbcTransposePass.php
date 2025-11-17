<?php
namespace Ksfraser\PhpabcCanntaireachd;

use Ksfraser\PhpabcCanntaireachd\Transpose\TransposeStrategy;
use Ksfraser\PhpabcCanntaireachd\Transpose\MidiTransposeStrategy;
use Ksfraser\PhpabcCanntaireachd\Transpose\BagpipeTransposeStrategy;
use Ksfraser\PhpabcCanntaireachd\Transpose\OrchestralTransposeStrategy;
use Ksfraser\PhpabcCanntaireachd\Tune\AbcTune;
use Ksfraser\PhpabcCanntaireachd\Log\FlowLog;

/**
 * Transpose Pass - Applies transpose values to voice headers
 * 
 * Uses transpose strategies to calculate and apply transpose values
 * based on the selected mode (MIDI, Bagpipe, or Orchestral).
 * 
 * Supports per-voice overrides via configuration.
 */
class AbcTransposePass
{
    /**
     * @var TransposeStrategy Transpose calculation strategy
     */
    private $strategy;

    /**
     * @var array Per-voice transpose overrides ['VoiceName' => transposeValue]
     */
    private $overrides;

    /**
     * Constructor
     *
     * @param TransposeStrategy|null $strategy Transpose strategy (optional)
     * @param AbcProcessorConfig|null $config Configuration (optional)
     */
    public function __construct(TransposeStrategy $strategy = null, AbcProcessorConfig $config = null)
    {
        if ($strategy !== null) {
            $this->strategy = $strategy;
            $this->overrides = [];
        } elseif ($config !== null) {
            $this->strategy = $this->createStrategyFromConfig($config);
            $this->overrides = $config->transposeOverrides ?? [];
        } else {
            /* Default to MIDI mode */
            $this->strategy = new MidiTransposeStrategy();
            $this->overrides = array();
        }
    }

    /**
     * Process ABC lines and apply transpose values
     *
     * @param array $lines ABC notation lines
     * @return array Processed ABC lines with transpose values
     */
    public function process(array $lines): array
    {
        FlowLog::log('AbcTransposePass::process ENTER', defined('PHPABC_VERBOSE') && PHPABC_VERBOSE);

        /* Parse the ABC content into a tune object */
        $abcText = implode("\n", $lines);
        $parser = new AbcFileParser();
        $tunes = $parser->parse($abcText);

        if (empty($tunes)) {
            FlowLog::log('AbcTransposePass::process EXIT - No tunes found', defined('PHPABC_VERBOSE') && PHPABC_VERBOSE);
            return $lines;
        }

        $tune = $tunes[0];

        /* Get voices from the tune */
        $voices = $tune->getVoices();

        if (empty($voices)) {
            FlowLog::log('AbcTransposePass::process EXIT - No voices found', defined('PHPABC_VERBOSE') && PHPABC_VERBOSE);
            return $lines;
        }

        /* Apply transpose values to voice metadata */
        $updatedVoices = [];
        foreach ($voices as $voiceId => $voiceMeta) {
            /* Get voice name for transpose calculation */
            $voiceName = $voiceId;
            if (is_array($voiceMeta) && isset($voiceMeta['name'])) {
                $voiceName = $voiceMeta['name'];
            } elseif (is_object($voiceMeta) && method_exists($voiceMeta, 'getName')) {
                $voiceName = $voiceMeta->getName();
            }

            /* Check for per-voice override first */
            if (isset($this->overrides[$voiceId]) || isset($this->overrides[$voiceName])) {
                $transposeValue = $this->overrides[$voiceId] ?? $this->overrides[$voiceName];
            } else {
                /* Use strategy to calculate transpose */
                $transposeValue = $this->strategy->getTranspose($voiceName);
            }

            /* Update voice metadata with transpose value */
            if (is_array($voiceMeta)) {
                $voiceMeta['transpose'] = $transposeValue;
                $updatedVoices[$voiceId] = $voiceMeta;
            } else {
                /* Keep original if not array format */
                $updatedVoices[$voiceId] = $voiceMeta;
            }
        }

        /* Update tune with new voice metadata */
        $tune->setVoices($updatedVoices);

        /* Render back to lines */
        $result = explode("\n", $tune->render());

        FlowLog::log('AbcTransposePass::process EXIT', defined('PHPABC_VERBOSE') && PHPABC_VERBOSE);
        return $result;
    }

    /**
     * Create strategy from configuration
     *
     * @param AbcProcessorConfig $config
     * @return TransposeStrategy
     */
    private function createStrategyFromConfig(AbcProcessorConfig $config): TransposeStrategy
    {
        $mode = $config->transposeMode ?? 'midi';

        switch (strtolower($mode)) {
            case 'bagpipe':
                return new BagpipeTransposeStrategy();

            case 'orchestral':
                return new OrchestralTransposeStrategy();

            case 'midi':
            default:
                return new MidiTransposeStrategy();
        }
    }

    /**
     * Set the transpose strategy
     *
     * @param TransposeStrategy $strategy
     * @return void
     */
    public function setStrategy(TransposeStrategy $strategy): void
    {
        $this->strategy = $strategy;
    }

    /**
     * Get the current strategy
     *
     * @return TransposeStrategy
     */
    public function getStrategy(): TransposeStrategy
    {
        return $this->strategy;
    }

    /**
     * Set per-voice transpose overrides
     *
     * @param array $overrides ['VoiceName' => transposeValue]
     * @return void
     */
    public function setOverrides(array $overrides): void
    {
        $this->overrides = $overrides;
    }

    /**
     * Get per-voice transpose overrides
     *
     * @return array
     */
    public function getOverrides(): array
    {
        return $this->overrides;
    }
}
