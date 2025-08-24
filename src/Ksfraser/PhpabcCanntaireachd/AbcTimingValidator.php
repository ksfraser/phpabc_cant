<?php
namespace Ksfraser\PhpabcCanntaireachd;
/**
 * Validates bar timing in ABC notation using M: and L: headers.
 * Adds 'TIMING' at the end of bars with issues and logs errors.
 */
class AbcTimingValidator {
    /**
     * Validate bar timing for ABC content.
     * @param array $lines ABC file lines
     * @return array [lines, errors]
     */
    public function validate(array $lines): array {
        $meter = null;
        $unit = null;
        $errors = [];
        $output = [];
        // Find M: and L: headers
        foreach ($lines as $line) {
            if (preg_match('/^M:\s*([\d\/]+)/', $line, $m)) {
                $meter = $m[1];
            }
            if (preg_match('/^L:\s*([\d\/]+)/', $line, $l)) {
                $unit = $l[1];
            }
            if ($meter && $unit) break;
        }
        // Calculate expected beats per bar
        $beatsPerBar = $this->beatsPerBar($meter, $unit);
        $inVoice = false;
        $barNum = 0;
        foreach ($lines as $line) {
            $newLine = $line;
            if (preg_match('/^V:/', $line)) {
                $inVoice = true;
                $output[] = $newLine;
                continue;
            }
            if ($inVoice && preg_match('/[A-Ga-gzZ]/', $line)) {
                // Split line by bar
                $parts = preg_split('/(\|)/', $line, -1, PREG_SPLIT_DELIM_CAPTURE);
                $barNumInLine = 0;
                for ($i = 0; $i < count($parts); $i++) {
                    if ($parts[$i] === '|') {
                        $barNum++;
                        $barNumInLine++;
                        $bar = ($i+1 < count($parts)) ? $parts[$i+1] : '';
                        $beats = $this->countBeats($bar, $unit);
                        $isPickup = $barNum == 1 && $beats < $beatsPerBar;
                        $isLast = false;
                        if (!$isPickup && !$isLast && $beats != $beatsPerBar) {
                            $errors[] = "Bar $barNum: Expected $beatsPerBar beats, found $beats";
                            $parts[$i+1] = rtrim($bar) . ' TIMING';
                        }
                    }
                }
                $newLine = implode('', $parts);
                $output[] = $newLine;
                // DEBUG: print processed line
                file_put_contents(__DIR__ . '/../../../timing_debug.txt', $newLine."\n", FILE_APPEND);
            } else {
                $output[] = $newLine;
            }
        }
        // DEBUG: print errors
        file_put_contents(__DIR__ . '/../../../timing_debug.txt', "ERRORS: ".json_encode($errors)."\n", FILE_APPEND);
        return ['lines' => $output, 'errors' => $errors];
    }
    private function replaceNthBar($line, $n, $bar) {
        // Replace the nth bar in a line (1-based)
        $count = 0;
        return preg_replace_callback('/\|([^|]*)/', function($matches) use (&$count, $n, $bar) {
            $count++;
            if ($count === $n) {
                return '|' . $bar;
            }
            return $matches[0];
        }, $line);
    }
    private function beatsPerBar($meter, $unit) {
        // e.g. M:4/4, L:1/8 => 4 beats/bar, 1/8 note = 0.5 beat
        if (!$meter || !$unit) return 0;
        [$num, $den] = explode('/', $meter);
        [$uNum, $uDen] = explode('/', $unit);
        if ($den == 0 || $uDen == 0) return 0;
        $beatLength = $uNum / $uDen;
        return ($num / $den) / $beatLength;
    }
    private function countBeats($bar, $unit) {
        // Count notes/rests in bar, estimate beats
        if (!$unit) return 0;
        [$uNum, $uDen] = explode('/', $unit);
        $beatLength = $uNum / $uDen;
        $beats = 0;
        // Match notes/rests with optional length (e.g. A2, z1/2)
        preg_match_all('/([A-Ga-gzZ][,\']*)(\d+(?:\/\d+)?)?/', $bar, $matches, PREG_SET_ORDER);
        foreach ($matches as $m) {
            $len = $m[2] ?? '';
            if ($len) {
                if (strpos($len, '/') !== false) {
                    [$n, $d] = explode('/', $len);
                    $beats += ($n / $d) / $beatLength;
                } else {
                    $beats += ($len / 1) / $beatLength;
                }
            } else {
                $beats += 1 / $beatLength;
            }
        }
        return round($beats, 2);
    }
}
