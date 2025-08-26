<?php
namespace Ksfraser\PhpabcCanntaireachd;

class AbcProcessorConfig {
    public $voiceOutputStyle = 'grouped'; // 'grouped' or 'interleaved'
    public $interleaveBars = 1; // X bars per voice before switching (if interleaved)
    public $barsPerLine = 4; // How many bars per ABC line
    public $joinBarsWithBackslash = false; // true: use \ to join bars, false: one line per typeset line
    public $tuneNumberWidth = 5; // Number of digits for X: tune numbers, left-filled with 0s
}
