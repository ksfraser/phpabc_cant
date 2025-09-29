<?php
namespace Ksfraser\PhpabcCanntaireachd;
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
	protected function parseAbcNote($noteStr)
	{
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
	error_log("Initial noteStr: $noteStr");
	if (defined('PHPABC_VERBOSE') && PHPABC_VERBOSE) echo "Initial noteStr: $noteStr\n";
		// 1. Remove decorator shortcuts at the start
		foreach (array_keys($decoratorMap) as $shortcut) {
			if (strpos($noteStr, $shortcut) === 0) {
				$noteStr = substr($noteStr, strlen($shortcut));
				$this->decorator = $shortcut;
				break;
			}
		}
	error_log("After decorator strip: $noteStr");
	if (defined('PHPABC_VERBOSE') && PHPABC_VERBOSE) echo "After decorator strip: $noteStr\n";
		// 2. Remove accidentals (=, ^, _)
		$noteStr = preg_replace('/^[=_^]+/', '', $noteStr);
	error_log("After accidental strip: $noteStr");
	if (defined('PHPABC_VERBOSE') && PHPABC_VERBOSE) echo "After accidental strip: $noteStr\n";
		// 3. Remove gracenotes (e.g., {g})
		$noteStr = preg_replace('/^\{[^}]*\}/', '', $noteStr);
	error_log("After gracenote strip: $noteStr");
	if (defined('PHPABC_VERBOSE') && PHPABC_VERBOSE) echo "After gracenote strip: $noteStr\n";
		// 4. Remove annotations (!...!) at the start
		if (preg_match('/^!(.*?)!/', $noteStr, $m)) {
			$noteStr = substr($noteStr, strlen($m[0]));
		}
	error_log("After annotation strip: $noteStr");
	if (defined('PHPABC_VERBOSE') && PHPABC_VERBOSE) echo "After annotation strip: $noteStr\n";
		// 1. Chord symbol
		$this->chordSymbol = $chordParser->parse($noteStr);
		if ($this->chordSymbol !== null) {
			$noteStr = preg_replace('/"[^"]+"/', '', $noteStr);
		}
		// 2. Grace notes
		$this->graceNotes = $graceParser->parse($noteStr);
		if (!empty($this->graceNotes)) {
			$noteStr = preg_replace('/\{[^}]*\}/', '', $noteStr);
		}
		// 3. Annotations/decorators
		$this->annotations = $annotationsParser->parse($noteStr);
		if (!empty($this->annotations)) {
			foreach ($this->annotations as $ann) {
				$noteStr = str_replace($ann, '', $noteStr);
			}
		}
		// 4. Accidentals
		$this->accidentals = $accidentalsParser->parse($noteStr);
		if (!empty($this->accidentals)) {
			foreach ($this->accidentals as $acc) {
				$noteStr = str_replace($acc, '', $noteStr);
			}
		}

	// 5. Typesetting space
	$this->typesettingSpace = $typesettingSpaceParser->parse($noteStr);
	// 6. Redefinable symbol
	$this->redefinableSymbol = $redefinableSymbolParser->parse($noteStr);

		// Ambiguity resolution: check for gotchas in noteStr
		foreach ($gotchas as $shortcut => $types) {
			$pos = strpos($noteStr, $shortcut);
			if ($pos !== false) {
				$pitchPos = preg_match('/[a-gA-GzZ]/', $noteStr, $m, PREG_OFFSET_CAPTURE) ? $m[0][1] : -1;
				$resolvedType = null;
				if ($pitchPos !== -1) {
					if ($pos < $pitchPos) {
						// Shortcut before pitch: likely decorator
						$resolvedType = in_array('decorator', $types) ? 'decorator' : $types[0];
					} else {
						// Shortcut after pitch: likely note element
						$nonDecoratorTypes = array_filter($types, function($t) { return $t !== 'decorator'; });
						$resolvedType = !empty($nonDecoratorTypes) ? reset($nonDecoratorTypes) : $types[0];
					}
				} else {
					// No pitch found, cannot resolve
					$resolvedType = $types[0];
				}
				// Instantiate or log ambiguity
				if ($resolvedType) {
					// Optionally instantiate or mark element type here
					// Example: $this->ambiguousElements[$shortcut] = $resolvedType;
				} else {
					error_log("Ambiguity unresolved for shortcut '$shortcut' in noteStr '$noteStr' (types: " . implode(',', $types) . ")");
				}
			}
		}

		// 13. Pitch (split point)
		$pitch = $noteParser->parse($noteStr);
		$this->set("pitch", $pitch);
		// 14. Octave (after pitch)
		$octave = $octaveParser->parse($noteStr);
		$this->set("octave", $octave);
		// 15. Note length (after pitch/octave)
		$length = $lengthParser->parse($noteStr);
		$this->set("length", $length);
		// 16. Decorator (legacy, for compatibility)
		$decorator = '';
		if (!empty($this->annotations)) {
			$decorator = implode('', $this->annotations);
		}
		$this->set("decorator", $decorator);
		// Decorator objects (instantiate if shortcut matches)
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
