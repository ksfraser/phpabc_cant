#!/bin/sh

#for x in DaCoda 
for x in Accent Diminuendo Fine LowerMordent PrallTriller Snap Trill Wedge Arpeggio Crescendo Downbow InvertedFermata MediumPhrase Roll Staccato Turn Breath DaCapo DS InvertedTurn Mordent Segno Tenuto Turnx CaCoda DaCoda Emphasis InvertedTurnX Open ShortPhrase Thumb Upbow Coda DC Fermata LongPhrase Plus Slide Tremolo UpperMordent
do 
  cat <<EOF > ${x}Decorator.php
<?php
namespace Ksfraser\PhpabcCanntaireachd\Decorator;

class ${x}Decorator {
    public function render() {
        return '!${x}!';
    }
}
EOF

done

