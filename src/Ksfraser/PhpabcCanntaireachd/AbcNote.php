<?php
namespace Ksfraser\PhpabcCanntaireachd;

echo 'PHPABC_VERBOSE: ' . (defined('PHPABC_VERBOSE') ? (PHPABC_VERBOSE ? 'true' : 'false') : 'not defined') . "\n";

/**
 * Class AbcNote
 *
 * Represents a single note in ABC notation, including pitch, octave, accidental, length, decorator, lyrics, canntaireachd, and solfege.
 * Provides parsing, validation, and rendering logic for ABC notes.
 *
 * SOLID: Single Responsibility (note model), DRY (trait for parsing), SRP (validation methods).
 *
 * @package Ksfraser\PhpabcCanntaireachd
 *
 * @property string $pitch Note pitch (a-gA-G)
 * @property string $octave Octave modifier (', ,,)
 * @property string $sharpflat Accidental (=, ^, _)
 * @property string $length Note length (1, /, //, etc.)
 * @property string $decorator Note decorator (.MHTR!trill! etc.)
 * @property string $lyrics Lyrics for this note
 * @property string $canntaireachd Canntaireachd for this note
 * @property string $solfege Solfege for this note
 * @property string $bmwToken BMW token for this note
 * @property callable $callback Optional callback for processing
 *
 * @method __construct(string $noteStr, callable|null $callback)
 * @method set(string $field, mixed $value, bool $enforce_only_native_vars)
 * @method setLyrics(string $lyrics)
 * @method getLyrics(): string
 * @method setCanntaireachd(string $cannt)
 * @method getCanntaireachd(): string
 * @method setSolfege(string $solfege)
 * @method getSolfege(): string
 * @method setBmwToken(string $bmw)
 * @method getBmwToken(): string
 * @method renderLyrics(): string
 * @method renderCanntaireachd(): string
 * @method renderSolfege(): string
 * @method get_body_out(): string
 *
 * @uml
 * @startuml
 * class AbcNote {
 *   - pitch: string
 *   - octave: string
 *   - sharpflat: string
 *   - length: string
 *   - decorator: string
 *   - lyrics: string
 *   - canntaireachd: string
 *   - solfege: string
 *   - bmwToken: string
 *   - callback: callable
 *   + __construct(noteStr: string, callback: callable)
 *   + set(field: string, value: mixed, enforce_only_native_vars: bool)
 *   + setLyrics(lyrics: string)
 *   + getLyrics(): string
 *   + setCanntaireachd(cannt: string)
 *   + getCanntaireachd(): string
 *   + setSolfege(solfege: string)
 *   + getSolfege(): string
 *   + setBmwToken(bmw: string)
 *   + getBmwToken(): string
 *   + renderLyrics(): string
 *   + renderCanntaireachd(): string
 *   + renderSolfege(): string
 *   + get_body_out(): string
 * }
 * AbcNote --|> Origin
 * AbcNote <|-- NoteParserTrait
 * @enduml
 */

use Ksfraser\PhpabcCanntaireachd\Exceptions\AbcNoteLengthException;
use Ksfraser\PhpabcCanntaireachd\NoteParserTrait;
use Ksfraser\origin\Origin;

class AbcNote extends Origin
{
	public function getPitch() {
		return $this->pitch;
	}
	public function getDecorator() {
		return $this->decorator;
	}
	public static $shortcutLookup = null;
	use NoteParserTrait;
	// Properties
	/**
	 * @var mixed Error state for this note (for legacy compatibility)
	 */
	protected $error;
	protected $decorator = '';
	protected $instanceShortcutLookup = [];
	protected $chordSymbol = null;
	protected $graceNotes = [];
	protected $annotations = [];
	protected $accidentals = [];
	protected $decorators = [];
	protected $pitch = '';
	protected $octave = '';
	protected $sharpflat = '';
	protected $length = '';
	protected $lyrics = '';
	protected $canntaireachd = '';
	protected $solfege = '';
	protected $bmwToken = '';
	public function __construct($noteStr, $callback = null)
	{
		$this->callback = $callback;
		$this->parseAbcNote($noteStr);
	}
	protected function parseAbcNote($noteStr)
		{
			file_put_contents('debug.log', "ENTERED parseAbcNote with: $noteStr\n", FILE_APPEND);
		// Use new parser classes for each ABC element
		$chordParser = new \Ksfraser\PhpabcCanntaireachd\Parser\ChordSymbolsParser();
		$graceParser = new \Ksfraser\PhpabcCanntaireachd\Parser\GraceNotesParser();
		$annotationsParser = new \Ksfraser\PhpabcCanntaireachd\Parser\AnnotationsParser();
		$accidentalsParser = new \Ksfraser\PhpabcCanntaireachd\Parser\AccidentalsParser();
		$noteParser = new \Ksfraser\PhpabcCanntaireachd\Parser\NoteParser();
		$octaveParser = new \Ksfraser\PhpabcCanntaireachd\Parser\OctaveParser();
		$lengthParser = new \Ksfraser\PhpabcCanntaireachd\Parser\NoteLengthParser();
	$typesettingSpaceParser = new \Ksfraser\PhpabcCanntaireachd\Parser\TypesettingSpaceParser();
	$redefinableSymbolParser = new \Ksfraser\PhpabcCanntaireachd\Parser\RedefinableSymbolParser();

		// Load gotchas (ambiguous shortcuts)
		$gotchas = \Ksfraser\PhpabcCanntaireachd\NoteElementLoader::getGotchas();

		// EasyABC-inspired: sequential token parsing
		$decoratorMap = \Ksfraser\PhpabcCanntaireachd\Decorator\DecoratorLoader::getDecoratorMap();
		$originalStr = $noteStr;
	file_put_contents('debug.log', "Initial noteStr: $noteStr\n", FILE_APPEND);
		// 1. Remove decorator shortcuts at the start using regex
		$decoratorPattern = \Ksfraser\PhpabcCanntaireachd\Decorator\DecoratorLoader::getRegex();
		file_put_contents('debug.log', "Decorator regex: $decoratorPattern\n", FILE_APPEND);
		if (preg_match($decoratorPattern, $noteStr, $dm)) {
			file_put_contents('debug.log', "Decorator match: " . var_export($dm, true) . "\n", FILE_APPEND);
			if (isset($dm[1]) && $dm[1] !== '') {
				$this->decorator = $dm[1];
				$noteStr = substr($noteStr, strlen($dm[1]));
			}
		}
		file_put_contents('debug.log', "After decorator strip: $noteStr\n", FILE_APPEND);
		// 2. Remove accidentals (=, ^, _)
		// EasyABC-inspired: prioritized regex patterns for ABC elements
		$patterns = [
			'chord' => \Ksfraser\PhpabcCanntaireachd\Parser\ChordSymbolsParser::getRegex(),
			'grace' => \Ksfraser\PhpabcCanntaireachd\Parser\GraceNotesParser::getRegex(),
			'decorator' => \Ksfraser\PhpabcCanntaireachd\Decorator\DecoratorLoader::getRegex(),
			'accidental' => \Ksfraser\PhpabcCanntaireachd\Parser\AccidentalsParser::getRegex(),
			'pitch_octave_length' => '/([=_^]*)([a-gA-G])([\',]*)(\d*\/?\d*)/', // This composite regex can be moved to a loader if needed
		];
		$originalStr = $noteStr;
		file_put_contents('debug.log', "Initial noteStr: $noteStr\n", FILE_APPEND);
		// 1. Chord symbol
		if (preg_match($patterns['chord'], $noteStr, $m)) {
			$this->chordSymbol = $m[1];
			$noteStr = preg_replace($patterns['chord'], '', $noteStr);
		}
		// 2. Grace notes
		if (preg_match($patterns['grace'], $noteStr, $m)) {
			$this->graceNotes = [$m[1]];
			$noteStr = preg_replace($patterns['grace'], '', $noteStr);
		}
		// 3. Decorator (ABC !...! at start)
		if (preg_match($patterns['decorator'], $noteStr, $m)) {
			$this->decorator = $m[1];
			$noteStr = substr($noteStr, strlen($m[1]));
		}
		// 4. Accidentals
		if (preg_match($patterns['accidental'], $noteStr, $m)) {
			$this->accidentals = [$m[1]];
			$noteStr = substr($noteStr, strlen($m[1]));
		}
		// 5. Pitch, octave, length (single pass)
		if (preg_match($patterns['pitch_octave_length'], $noteStr, $m)) {
			$this->set('sharpflat', $m[1]);
			// Preserve case for pitch
			$this->set('pitch', $m[2]);
			$this->set('octave', $m[3]);
			$this->set('length', $m[4]);
		} else {
			// No valid note found, log error
			file_put_contents('debug.log', "No valid pitch/octave/length found in: $noteStr\n", FILE_APPEND);
		}
		// 6. Ambiguity resolution: longest match wins, pattern order resolves ambiguity
		// No manual gotchas logic needed; regex order and specificity handle ambiguity
		// 7. Decorator objects (legacy, for compatibility)
		$this->decorators = [];
	}

	public function setBmwToken($bmw) {
		$this->bmwToken = $bmw;
	}
	public function getBmwToken() {
		return $this->bmwToken;
	}
	public function setLyrics($lyrics) {
		$this->lyrics = $lyrics;
	}
	public function getLyrics() {
		return $this->lyrics;
	}
	public function setCanntaireachd($cannt) {
		$this->canntaireachd = $cannt;
	}
	public function getCanntaireachd() {
		return $this->canntaireachd;
	}
	public function setSolfege($solfege) {
		$this->solfege = $solfege;
	}
	public function getSolfege() {
		return $this->solfege;
	}

	public function renderLyrics() {
		return $this->lyrics ?? '';
	}
	public function renderCanntaireachd() {
		return $this->canntaireachd ?? '';
	}
	public function renderSolfege() {
		return $this->solfege ?? '';
	}

	public function set($field, $value = null, $enforce_only_native_vars = true)
	{
		switch ($field) {
			case "pitch":
				$ok = $this->validate_pitch($value);
				if ($ok) $this->pitch = $value;
				break;
			case "octave":
				$ok = $this->validate_octave($value);
				if ($ok) $this->octave = $value;
				break;
			case "sharpflat":
				$ok = $this->validate_sharpflat($value);
				if ($ok) $this->sharpflat = $value;
				break;
			case "length":
				$ok = $this->validate_length($value);
				if ($ok) $this->length = $value;
				break;
			case "decorator":
				$ok = $this->validate_decorator($value);
				if ($ok) $this->decorator = $value;
				break;
			default:
				return parent::set($field, $value, $enforce_only_native_vars);
		}
		return true;
	}

	/**
	 * Validate that the pitch is valid
	 * @param string $value
	 * @return bool
	 */
	function validate_pitch($value)
	{
		switch ($value) {
			case "A": case "B": case "C": case "D": case "E": case "F": case "G":
			case "a": case "b": case "c": case "d": case "e": case "f": case "g":
				return true;
			default:
				return false;
		}
	}

	/**
	 * Validate that the octave is valid
	 * @param string $value
	 * @return bool
	 */
	function validate_octave($value)
	{
		switch ($value) {
			case ",": case "'": case ",,": case "''": case "":
				return true;
			default:
				return false;
		}
	}

	/**
	 * Validate that the sharpflat is valid
	 * @param string $value
	 * @return bool
	 */
	function validate_sharpflat($value)
	{
		switch ($value) {
			case "=": case "^": case "_": case "":
				return true;
			default:
				return false;
		}
	}

	/**
	 * Validate that the length is valid
	 * @param string $value
	 * @return bool
	 */
	function validate_length($value)
	{
		// Check for three or more slashes
		if (preg_match('/^\/{3,}$/', $value)) {
			throw new \Ksfraser\PhpabcCanntaireachd\Exceptions\AbcNoteLengthException($value);
		}
		switch ($value) {
			case "1": case "2": case "3": case "4": case "5": case "6": case "7": case "8":
			case "9": case "10": case "11": case "12": case "13": case "14": case "15": case "16":
			case "/": case ">": case "<": case "//":
				return true;
			default:
				return false;
		}
	}

	/**
	 * Validate that the decorator is valid
	 * @param string $value
	 * @return bool
	 */
	function validate_decorator($value)
	{
		switch ($value) {
			case ".": case "<": case "H": case "T": case "R": case "!trill!": case "!fermata!": case "":
				return true;
			default:
				return false;
		}
	}

	/**
	 * Format the class variables into the Voice line in the header
	 * @return string
	 */
	function get_header_out()
	{
		throw new \Exception("Notes can't be in the header!");
	}

	/**
	 * Render the note as ABC, including all spec fields in correct order, using Decorator classes.
	 * @return string
	 */
	public function get_body_out()
	{
		$out = "";
		// Grace notes first
		if (!empty($this->graceNotes)) {
			$out .= "{" . implode(' ', $this->graceNotes) . "}";
		}
		// Chord symbol next
		if ($this->chordSymbol) {
			$out .= '"' . $this->chordSymbol . '"';
		}
		// Accidentals before note
		foreach ($this->accidentals as $a) {
			$out .= $a;
		}
		// Decorators/annotations (ABC spec: decorations before note)
		foreach ($this->decorators as $decoratorObj) {
			$out .= $decoratorObj->render();
		}
		// Decorator field (legacy, for compatibility)
		if (isset($this->decorator) && $this->decorator !== "") {
			$out .= $this->decorator;
		}
		// Pitch
		$out .= $this->pitch;
		// Octave
		if (isset($this->octave) && $this->octave !== "") {
			$out .= $this->octave;
		}
		// Note length
		if (isset($this->length) && $this->length !== "") {
			$out .= $this->length;
		}
		return $out;
	}

}



