<?php
namespace Ksfraser\PhpabcCanntaireachd\Header;

use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderMultiField;

/**
 * Voice Header Fields are kind of special
 */
class AbcHeaderV extends AbcHeaderMultiField {
    public static $label = 'V';
    protected $name;
    protected $sname;
    protected $stem;
    protected $gstem;
    protected $clef;
    protected $transpose;
    protected $middle;
    protected $octave;
    protected $stafflines;

    public function setName($name) { $this->name = $name; }
    public function setSname($name) { $this->sname = $name; }
    public function setStem($stem) { $this->stem = $stem; }
    public function setGstem($gstem) { $this->gstem = $gstem; }
    public function setClef($clef) { $this->clef = $clef; }
    public function setTranspose($transpose) { $this->transpose = $transpose; }
    public function setMiddle($middle) { $this->middle = $middle; }
    public function setOctave($octave) { $this->octave = $octave; }
    public function setStafflines($stafflines) { $this->stafflines = $stafflines; }
}
