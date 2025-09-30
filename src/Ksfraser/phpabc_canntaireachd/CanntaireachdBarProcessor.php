<?php
namespace Ksfraser\PhpabcCanntaireachd;

class CanntaireachdBarProcessor
{
    protected $dictionary;

    public function __construct($dictionary)
    {
        $this->dictionary = $dictionary;
    }

    /**
     * Convert an array of ABC tokens to a canntaireachd string.
     * @param array $tokens
     * @return string
     */
    /**
     * Convert an array of AbcNote objects to a canntaireachd string.
     * Each AbcNote will have its cannt property set if mapping is found.
     * @param array $notes Array of AbcNote objects
     * @return string
     */
    public function notesToCanntaireachd(array $notes): string
    {
        $canntArr = [];
        foreach ($notes as $note) {
            if (!is_object($note) || !method_exists($note, 'get_body_out')) {
                continue;
            }
            // Build dictionary key: gracenote+pitch
            $decorator = isset($note->decorator) ? $note->decorator : '';
            $pitch = isset($note->pitch) ? $note->pitch : '';
            $key = '';
            if ($decorator !== '' && strpos($decorator, '{') === 0) {
                $key = $decorator . $pitch;
            } else {
                $key = $pitch;
            }
            // Normalize key
            $normKey = \Ksfraser\PhpabcCanntaireachd\TokenNormalizer::normalize($key);
            $cannt = $this->dictionary[$normKey] ?? null;
            if ($cannt !== null) {
                $note->cannt = $cannt;
                $canntArr[] = $cannt;
            } else {
                $note->cannt = $note->get_body_out();
                $canntArr[] = $note->cannt;
            }
        }
        return implode(' ', $canntArr);
    }

    /**
     * Lookup canntaireachd syllable for a token.
     * @param string $token
     * @return string|null
     */
    protected function getCannt(string $token): ?string
    {
        // Normalize token: strip duration, keep gracenotes and pitch
        $normalized = $token;
        // Remove trailing digits, slashes, dots (duration)
        $normalized = preg_replace('/[0-9.\/]+$/', '', $normalized);
        // Remove spaces
        $normalized = trim($normalized);
        return $this->dictionary[$normalized] ?? $token;
    }
}
