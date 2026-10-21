<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Cleanup;

use ksfraser\PhpabcCanntaireachd\Cleanup\Model\AbcBodyLine;
use ksfraser\PhpabcCanntaireachd\Cleanup\Model\AbcDocument;
use ksfraser\PhpabcCanntaireachd\Cleanup\Model\AbcNoteModel;
use ksfraser\PhpabcCanntaireachd\Cleanup\Model\AbcTuneModel;

/**
 * Runs the clean up stages over the tunes of a document.
 *
 * Every stage is a class of its own, and every stage is injected, so that a
 * caller can use the ones it needs and leave the rest out. The pipeline only
 * puts them in order and collects what they report.
 *
 * @package ksfraser\PhpabcCanntaireachd\Cleanup
 *
 * @since 2.2.0
 */
class TuneCleanupPipeline
{
    /** @var HeaderOrderNormaliser */
    private HeaderOrderNormaliser $headerOrder;

    /** @var IncludeBlockWriter */
    private IncludeBlockWriter $includes;

    /** @var VoiceAttributeNormaliser */
    private VoiceAttributeNormaliser $voiceNormaliser;

    /** @var DrumVoiceNormaliser */
    private DrumVoiceNormaliser $drums;

    /** @var BagpipeScaleChecker */
    private BagpipeScaleChecker $scale;

    /** @var BarReflower */
    private BarReflower $reflower;

    /**
     * @param HeaderOrderNormaliser|null $headerOrder
     * @param IncludeBlockWriter|null $includes
     * @param VoiceAttributeNormaliser|null $voiceNormaliser
     * @param DrumVoiceNormaliser|null $drums
     * @param BagpipeScaleChecker|null $scale
     * @param BarReflower|null $reflower
     */
    public function __construct(
        ?HeaderOrderNormaliser $headerOrder = null,
        ?IncludeBlockWriter $includes = null,
        ?VoiceAttributeNormaliser $voiceNormaliser = null,
        ?DrumVoiceNormaliser $drums = null,
        ?BagpipeScaleChecker $scale = null,
        ?BarReflower $reflower = null
    ) {
        $this->headerOrder = $headerOrder ?? new HeaderOrderNormaliser();
        $this->includes = $includes ?? new IncludeBlockWriter();
        $this->voiceNormaliser = $voiceNormaliser ?? new VoiceAttributeNormaliser();
        $this->drums = $drums ?? new DrumVoiceNormaliser();
        $this->scale = $scale ?? new BagpipeScaleChecker();
        $this->reflower = $reflower ?? new BarReflower();
    }

    /**
     * Clean up the tunes of a document.
     *
     * @param AbcDocument $document
     * @param string $tuneDirectory The directory the tunes are written to; the
     *                               include paths are relative to it.
     * @return array{document: AbcDocument, report: array<int, array<string, string>>}
     */
    public function cleanup(AbcDocument $document, string $tuneDirectory = '.'): array
    {
        $report = [];
        $tunes = $document->getTunes();

        foreach ($document->getIncludes() as $include) {
            foreach ($this->includes->extractTargets([$include]) as $target) {
                $path = $this->includes->resolve($tuneDirectory, $target);
                if ($path !== null && !file_exists($path)) {
                    $report[] = [
                        'tune' => '',
                        'kind' => 'include',
                        'message' => "the include file $target does not exist",
                    ];
                }
            }
        }
        $document->setIncludes($this->includes->build($tuneDirectory));

        foreach ($tunes as $index => $tune) {
            $tuneNumber = $tune->getTuneNumber();

            $this->cleanVoices($tune, $tuneNumber, $report);
            $this->cleanDrums($tune, $tuneNumber, $report);
            $this->checkScale($tune, $tuneNumber, $report);
            $this->relayout($tune, $tuneNumber, $report);
        }

        return ['document' => $document, 'report' => $report];
    }

    /**
     * The header fields, in the order they are written in.
     *
     * @return string[]
     */
    public function getHeaderOrder(): array
    {
        return $this->headerOrder->getOrder();
    }

    /**
     * Give every voice the attributes it should have.
     *
     * @param AbcTuneModel $tune
     * @param string $tuneNumber
     * @param array<int, array<string, string>> $report
     * @return void
     */
    private function cleanVoices(AbcTuneModel $tune, string $tuneNumber, array &$report): void
    {
        $voices = $tune->getHeader('V');
        if ($voices === []) {
            return;
        }
        $cleaned = [];
        foreach ($voices as $line) {
            $voice = VoiceHeader::parse($line);
            if ($voice === null) {
                $cleaned[] = $line;
                continue;
            }
            foreach ($this->voiceNormaliser->findChanges($voice) as $attribute => $change) {
                $report[] = [
                    'tune' => $tuneNumber,
                    'kind' => 'voice',
                    'message' => sprintf('voice %s: %s %s', $voice->getIndicator(), $attribute, $change),
                ];
            }
            $cleaned[] = $this->voiceNormaliser->normalise($line);
        }
        $tune->setHeader('V', $cleaned);
    }

    /**
     * Put the pipe band drums on their one line staff, and on A and c.
     *
     * @param AbcTuneModel $tune
     * @param string $tuneNumber
     * @param array<int, array<string, string>> $report
     * @return void
     */
    private function cleanDrums(AbcTuneModel $tune, string $tuneNumber, array &$report): void
    {
        $bodyLines = $tune->getBodyLines();
        if ($bodyLines === []) {
            return;
        }

        $voices = $tune->getHeader('V');
        $cleaned = [];
        foreach ($voices as $line) {
            $voice = VoiceHeader::parse($line);
            if ($voice === null) {
                $cleaned[] = $line;
                continue;
            }
            $voiceId = $voice->getIndicator();
            if (!$this->drums->isPipeBandDrum($voiceId, $voice->getName())) {
                $cleaned[] = $line;
                continue;
            }

            // The one line percussion staff.
            $staff = $this->drums->getPercussionStaff();
            if ($voice->get('clef') !== $staff['clef'] || $voice->get('stafflines') !== $staff['stafflines']) {
                $report[] = [
                    'tune' => $tuneNumber,
                    'kind' => 'drum',
                    'message' => sprintf(
                        'voice %s: the clef was changed to a one line percussion staff',
                        $voiceId
                    ),
                ];
                $voice->set('clef', $staff['clef']);
                $voice->set('stafflines', $staff['stafflines']);
                if ($voice->get('middle') === null) {
                    $voice->set('middle', $staff['middle']);
                }
            }

            // The notes.
            $pitches = $this->drums->collectPitches($bodyLines, $voiceId);
            $diagnosis = $this->drums->diagnose($pitches);
            if ($diagnosis['action'] === 'transpose') {
                $semitones = (int)$diagnosis['transposition'];
                $this->transposeVoice($bodyLines, $voiceId, $semitones);
                $report[] = [
                    'tune' => $tuneNumber,
                    'kind' => 'drum',
                    'message' => sprintf(
                        'voice %s: the part was transposed by %d semitones onto A and c',
                        $voiceId,
                        $semitones
                    ),
                ];
            } elseif ($diagnosis['action'] === 'case') {
                foreach ($diagnosis['notes'] ?? [] as $pitch) {
                    $this->fixCase($bodyLines, $voiceId, $pitch);
                }
                $report[] = [
                    'tune' => $tuneNumber,
                    'kind' => 'drum',
                    'message' => sprintf(
                        'voice %s: the case of a note was corrected (%s)',
                        $voiceId,
                        implode(', ', $diagnosis['notes'] ?? [])
                    ),
                ];
            } elseif ($diagnosis['action'] === 'typo') {
                $report[] = [
                    'tune' => $tuneNumber,
                    'kind' => 'drum',
                    'message' => sprintf(
                        'voice %s: the notes %s do not fit the drum staff and are most likely a typo',
                        $voiceId,
                        implode(', ', $diagnosis['notes'] ?? [])
                    ),
                ];
            }

            $cleaned[] = $voice->render();
        }
        $tune->setHeader('V', $cleaned);
    }

    /**
     * Transpose the notes of a voice.
     *
     * @param AbcBodyLine[] $bodyLines
     * @param string $voiceId
     * @param int $semitones
     * @return void
     */
    private function transposeVoice(array $bodyLines, string $voiceId, int $semitones): void
    {
        foreach ($bodyLines as $bodyLine) {
            if ($bodyLine->getVoice() !== $voiceId) {
                continue;
            }
            foreach ($bodyLine->getBars() as $bar) {
                $notes = [];
                foreach ($bar->getNotes() as $note) {
                    $notes[] = $note->isRest()
                        ? $note
                        : $this->drums->transposeNote($note, $semitones);
                }
                $bar->setNotes($notes);
            }
        }
    }

    /**
     * Fix the case of the notes of a voice.
     *
     * @param AbcBodyLine[] $bodyLines
     * @param string $voiceId
     * @param string $pitch
     * @return void
     */
    private function fixCase(array $bodyLines, string $voiceId, string $pitch): void
    {
        $letter = $pitch[0] ?? '';
        $marks = substr($pitch, 1);
        foreach ($bodyLines as $bodyLine) {
            if ($bodyLine->getVoice() !== $voiceId) {
                continue;
            }
            foreach ($bodyLine->getBars() as $bar) {
                $notes = [];
                foreach ($bar->getNotes() as $note) {
                    if ($note->getPitch() === $letter && $note->getOctave() === $marks) {
                        $target = $this->drums->findCaseCorrection($pitch);
                        if ($target !== null) {
                            $pitchValue = new AbcPitch($letter, $marks);
                            $shifted = $pitchValue->transpose($target);
                            $notes[] = $note->withPitch($shifted->getLetter(), substr($shifted->render(), 1));
                            continue;
                        }
                    }
                    $notes[] = $note;
                }
                $bar->setNotes($notes);
            }
        }
    }

    /**
     * Check that the bagpipe part can be played.
     *
     * @param AbcTuneModel $tune
     * @param string $tuneNumber
     * @param array<int, array<string, string>> $report
     * @return void
     */
    private function checkScale(AbcTuneModel $tune, string $tuneNumber, array &$report): void
    {
        $bodyLines = $tune->getBodyLines();
        foreach ($tune->getHeader('V') as $line) {
            $voice = VoiceHeader::parse($line);
            if ($voice === null) {
                continue;
            }
            $name = strtolower($voice->getName());
            if (!in_array($name, ['melody', 'bagpipes', 'pipes', 'bagpipe'], true)) {
                continue;
            }
            $voiceId = $voice->getIndicator();

            $outOfRange = $this->scale->findOutOfRange($bodyLines, $voiceId);
            if ($outOfRange !== []) {
                $report[] = [
                    'tune' => $tuneNumber,
                    'kind' => 'scale',
                    'message' => sprintf(
                        'voice %s: the notes %s are outside the pipe scale (low G to high D)',
                        $voiceId,
                        implode(', ', $outOfRange)
                    ),
                ];
            }
            $outOfScale = $this->scale->findOutOfScale($bodyLines, $voiceId);
            if ($outOfScale !== []) {
                $report[] = [
                    'tune' => $tuneNumber,
                    'kind' => 'scale',
                    'message' => sprintf(
                        'voice %s: the notes %s are not in the pipe scale',
                        $voiceId,
                        implode(', ', $outOfScale)
                    ),
                ];
            }
        }
    }

    /**
     * Lay the bars out again.
     *
     * @param AbcTuneModel $tune
     * @param string $tuneNumber
     * @param array<int, array<string, string>> $report
     * @return void
     */
    private function relayout(AbcTuneModel $tune, string $tuneNumber, array &$report): void
    {
        $result = $this->reflower->reflow($tune);
        $tune->setBodyLines($result['lines']);
        foreach ($result['notes'] as $note) {
            $report[] = [
                'tune' => $tuneNumber,
                'kind' => 'layout',
                'message' => $note,
            ];
        }
    }
}
