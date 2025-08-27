<?php
namespace Ksfraser\PhpabcCanntaireachd;

use ksfraser\origin\Origin;

trait NoteParserTrait {
	/**
	 * Parse an ABC note string into components.
	 * @param string $noteStr
	 * @return array [pitch, octave, sharpflat, length, decorator]
	 */
	public static function parseNote($noteStr) {
		// Example regex for ABC note parsing
		if (preg_match("/^([_=^]?)([a-gA-GzZ])([,']*)(\\d*\/?\\d*)(.*)$/", $noteStr, $m)) {
			return [
				'pitch' => $m[2],
				'octave' => $m[3],
				'sharpflat' => $m[1],
				'length' => $m[4],
				'decorator' => $m[5]
			];
		}
		return [
			'pitch' => '',
			'octave' => '',
			'sharpflat' => '',
			'length' => '',
			'decorator' => ''
		];
	}
}

class AbcNote extends Origin
{
	use NoteParserTrait;

	protected $pitch;    //a-gA-G
	protected $octave;    //, or '
	protected $sharpflat;    // =^_    null/natural/sharp/flat
	protected $length;    //!<string    (int)(/)(int)
	protected $decorator;    //!<string .MHTR!trill!    stacatto Legato Fermato Trill Roll
	protected $name;
	protected $cannt;
	protected $callback;    //Function to process this voice

	public function __construct($noteStr, $callback = null)
	{
		parent::__construct();
		$parsed = self::parseNote($noteStr);
		$this->set("pitch", $parsed['pitch']);
		$this->set("octave", $parsed['octave']);
		$this->set("sharpflat", $parsed['sharpflat']);
		$this->set("length", $parsed['length']);
		$this->set("decorator", $parsed['decorator']);
		$this->callback = $callback;
	}
}
			case "pitch":
				$ok = $this->validate_pitch( $value );
				if( $ok )
					$this->pitch = $value;
				break;
			case "octave":
				$ok = $this->validate_octave( $value );
				if( $ok )
					$this->octave = $value;
				break;
			case "sharpflat":
				$ok = $this->validate_sharpflat( $value );
				if( $ok )
					$this->sharpflat = $value;
				break;
			case "length":
				$ok = $this->validate_length( $value );
				if( $ok )
					$this->length = $value;
				break;
			case "decorator":
				$ok = $this->validate_decorator( $value );
				if( $ok )
					$this->decorator = $value;
				break;
			default:
				//Either we don't allow anything else to be set
				return false;
				// or we let inherited fields be set.
		}
	}
	
	/**//**************************************************
	* Validate that the pitch is valid
	*
	* @param string
	* @returns bool
	***************************************************************/
	function validate_pitch( $value )
	{
		switch( $value )
		{
			case "A": case "B": case "C": case "D": case "E": case "F": case "G":
			case "a": case "b": case "c": case "d": case "e": case "f": case "g":
				return true;
			default:
				return false;
		}
	}
	
	/**//**************************************************
	* Validate that the octave is valid
	*
	* @param string
	* @returns bool
	***************************************************************/
	function validate_octave( $value )
	{
		switch( $value )
		{
			case ",": case "'": case ",,": case "''": case "":
				return true;
			default:
				return false;
		}
	}
	
	/**//**************************************************
	* Validate that the sharpflat is valid
	*
	* @param string
	* @returns bool
	***************************************************************/
	function validate_sharpflat( $value )
	{
		switch( $value )
		{
			case "=": case "^": case "_": case "":
				return true;
			default:
				return false;
		}
	}
	
	/**//**************************************************
	* Validate that the length is valid
	*
	* @param string
	* @returns bool
	***************************************************************/
	function validate_length( $value )
	{
		switch( $value )
		{
			case "1": case "2": case "3": case "4": case "5": case "6": case "7": case "8":
			case "9": case "10": case "11": case "12": case "13": case "14": case "15": case "16":
			case "/": case ">": case "<": case "//":
				return true;
			default:
				return false;
		}
	}
	
	/**//**************************************************
	* Validate that the decorator is valid
	*
	* @param string
	* @returns bool
	***************************************************************/
	function validate_decorator( $value )
	{
		switch( $value )
		{
			case ".": case "<": case "H": case "T": case "R": case "!trill!": case "!fermata!": case "":
				return true;
			default:
				return false;
		}
	}
	
	/**//******************************************
	* Format the class variables into the Voice line in the header
	*
	* returns something like "V:M name="Melody" sname="Melody" stem=down gstem=done octave=0 transpose=0 clef="Treble""
	* @param none
	* @returns string
	***********************************************/
	function get_header_out()
	{
		throw new \Exception( "Notes can't be in the header!" );
	}
	
	/**//******************************************
	* Format the class variables into the Voice line in the body  
	*
	* returns something like "[V:M name="Melody" sname="Melody" stem=down gstem=done octave=0 transpose=0 clef="Treble"]"
	* @param none
	* @returns string
	***********************************************/
	function get_body_out()
	{
		$out = "";
		if( isset( $this->decorator ) && $this->decorator !== "" )
		{
			$out .= $this->decorator;
		}  
		if( isset( $this->sharpflat ) && $this->sharpflat !== "" )
		{
			$out .= $this->sharpflat;
		}  
		$out .= $this->pitch;
		if( isset( $this->octave ) && $this->octave !== "" )
		{
			$out .= $this->octave;
		}  
		if( isset( $this->length ) && $this->length !== "" )
		{
			$out .= $this->length;
		}  
		return $out;
	}
}
