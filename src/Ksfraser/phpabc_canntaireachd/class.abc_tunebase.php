<?php

/**//**************************************************************
 * I have built an array of items from the DOCs from bmw to try
 * and convert into ABC.  This should save a bunch of typesetting tim.
 *
 * * ***************************************************************/

/***USAGE***
 * Quick Start
 *
 */

global $abc_dict;
require_once( 'abc_dict.php' );	//Used by get_cannt
require_once( 'class.origin.php' );	//Used by get_cannt
require_once( 'class.abc_voice.php' ); // Used by get_cannt
use Ksfraser\PhpabcCanntaireachd\Voices\InstrumentVoiceFactory;
use Ksfraser\PhpabcCanntaireachd\Voices\AbcVoice;


/**//**
 * This class is the components of an ABC tune.  It is like a syntax.
 *
 * This class is meant to be the common parts between an 
 *	INPUT class (i.e. parser)
 * 	OUTPUT class (i.e. aspd_tune)
 * 
 * */
class abc_tunebase extends origin
{
	protected $discography; //D
	protected $headers_anglo_arr;
	protected $index; //X
	protected $key; //K
	protected $notelength;	//L Default of 1/16 for 2/4 or 6/8.  1/8 for 3/4 4/4 9/8 12/8...
	protected $meter;	//M Default is 4/4 if omitted
	protected $tempo;	//Q default 100bpm   e.g. Q: "Allegro" 1/4=120
	protected $title_arr;	//T
	protected $composer;	//C
	protected $history_arr;	//H
	protected $books_arr;	//B
	// REMOVED: protected $voice_arr; //V
	// REMOVED: protected $body_voice_arr;   //The list of voices for the [V:1] line headers
	// REMOVED: protected $voice_name_arr;   //The list of voices Names for searching
	// REMOVED: protected $body_arr;
	protected $instruction_arr;	//I
	protected $macro_arr;	//m
	protected $notes;	//N  i.e. referrences to other similar tunes.
	protected $origin;	//O  e.g. O:Canada; Nova Scotia; Halifax.
	protected $parts;	//P
	protected $rythm;	//R  e.g. hornpipe, double jig, single jig, 48-bar polka
	protected $source;	//S
	protected $userdef_arr;	//U
	protected $transcription;	//Z
	//BODY
	protected $words_arr;	//w	LYRICS
	protected $body_voice_arr;	//The list of voices for the [V:1] line headers
	protected $voice_name_arr;	//The list of voices Names for searching
	protected $voicecount;
	protected $words_bottom_arr;//W
	protected $complete_tune;//What we are going to output.
	protected $body_arr;
	protected $body;
	protected $line_count;	//How many lines did we add?
	protected $header_symbols_arr;
	protected $voices_obj_arr;	//Array of Voice objects
	// X must be first field.  T is second field.  K is last field.  All others optional.
	// Accidentals:
	// 	^ sharp
	// 	_ flat
	// 	= natural
	// 	^^ and __ allowed
	function __construct()
	{
        $this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		parent::__construct();
		$this->headers_anglo_arr = array();
		$this->headers_anglo_arr['X'] = "index";
		$this->headers_anglo_arr['K'] = "key";
		$this->headers_anglo_arr['L'] = "notelength";
		$this->headers_anglo_arr['M'] = "meter";
		$this->headers_anglo_arr['Q'] = "tempo";
		$this->headers_anglo_arr['T'] = "title_arr";
		$this->headers_anglo_arr['C'] = "composer";
		$this->headers_anglo_arr['H'] = "history_arr";
		$this->headers_anglo_arr['B'] = "books_arr";
		$this->headers_anglo_arr['V'] = "voice_arr";
		$this->headers_anglo_arr['D'] = "discography";
		$this->headers_anglo_arr['F'] = "file_url";
		$this->headers_anglo_arr['G'] = "group";
		$this->headers_anglo_arr['I'] = "instruction_arr";
		$this->headers_anglo_arr['m'] = "macro_arr";
		$this->headers_anglo_arr['N'] = "notes";
		$this->headers_anglo_arr['O'] = "origin";
		$this->headers_anglo_arr['P'] = "parts";
		$this->headers_anglo_arr['R'] = "rythm";
		$this->headers_anglo_arr['S'] = "source";
		$this->headers_anglo_arr['U'] = "user_def_arr";
		$this->headers_anglo_arr['Z'] = "transcription";
		$this->headers_anglo_arr['w'] = "lyrics";
		$this->headers_anglo_arr['W'] = "words_bottom_arr";
		$this->line_count = 0;
		/**/
	$this->words_bottom_arr = array();
	$this->words_arr = array();
	$this->instructions_arr = array();
	$this->title_arr = array();
		/**/
		$this->body = "";
		$this->complete_tune = "";
		$this->header_symbols_arr = array( "transcription" => "Z:",
			"notelength" => "L:", 
			"meter" => "M:", 
			"tempo" => "Q:", 
			"composer" => "C:", 
			"history" => "H:", 
			"book" => "B:", 
			"voice" => "V:", 
			"discography" => "D:", 
			"file_url" => "F:", 
			"group" => "G:", 
			"instruction" => "I:", 
			"macro" => "m", 
			"notes" => "N:", 
			"origin" => "O:", 
			"parts" => "P:", 
			"rythm" => "R:", 
			"source" => "S:", 
			"userdef" => "U:", 
			"title" => "T:",
			"index" => "X:",
		);
		$this->voicecount = 0;
		// Use InstrumentVoiceFactory for all default voices
		$vm = \Ksfraser\PhpabcCanntaireachd\Voices\InstrumentVoiceFactory::createVoiceFromParams('M', 'Melody', 'Melody', 'down', 'up', 0, 0, 'add_melody');
		$this->add_voice_obj( $vm );
		$vl = \Ksfraser\PhpabcCanntaireachd\Voices\InstrumentVoiceFactory::createVoiceFromParams('w', 'Lyrics', 'Lyrics', 'down', 'up', 0, 0, 'add_lyrics');
		$this->add_voice_obj( $vl );
	}
	/**//**************************************************************************
	* Take the "anglo" value for the header and return the Key value e.g. Title => T:
	*
	* @param string the Anglo value e.g. Title
	* @returns string the key e.g. T:
	******************************************************************************/
	function get_header_key( $val )
	{
					$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		if( isset( $this->header_symbols_arr ) )
		{
			return array_search( $val, $this->header_symbols_arr );
		}
		else
		{
			//How did we get this far where the arr isn't set?
			throw new Exception( "Header_symbol_arr not set.  How so, as the constructor sets it?", KSF_FIELD_NOT_SET );
		}
	}
	function add_index( $index = 1 )
	{
					$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		if( ! isset( $this->index ) )
			$this->index = $index;
	}
	/**//***********************
	 * Add the first KEY to the header
	 *
	 * The Spec allows for inline Key changes.  So we will
	 * only add the Key once, on the assumption it is for 
	 * the header.
	 *
	 * @param string
	 * */
	function add_key( $key )
	{
					//$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		if( strlen( $this->key ) < 2 )
			$this->key = $key;
		//else
		return;
	}
	/**//**
	* Add HEADER line.  TODO: REFACTOR.  Set will do this...
	*/
	function add_transcription( $line )
	{
					//$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		$this->transcription .= " / " . $line;
	}
	/**//**
	* Add HEADER line.  TODO: REFACTOR.  Set will do this...
	*/
	function add_notelength( $line )
	{
					//$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		$this->notelength = $line;
	}
	/**//**
	* Add HEADER line.  TODO: REFACTOR.  Set will do this...
	*/
	function add_meter( $line )
	{
					//$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		$this->meter =  $line;
	}
	/**//**
	* Add HEADER line.  TODO: REFACTOR.  Set will do this...
	*/
	function add_tempo( $line )
	{
					//$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		$this->tempo =  $line;
	}
	/**//**
	* Add HEADER line.  TODO: REFACTOR.  Set will do this...
	*/
	function add_title( $line )
	{
					//$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		$this->title_arr[] = $line;
	}
	/**//**
	* Add HEADER line.  TODO: REFACTOR.  Set will do this...
	*/
	function add_composer( $line )
	{
					//$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		$this->composer = $line;
	}
	/**//**
	* Add HEADER line.  TODO: REFACTOR.  Set will do this...
	*/
	function add_history( $line )
	{
					//$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		$this->history_arr[] = $line;
	}
	/**//**
	* Add HEADER line.  TODO: REFACTOR.  Set will do this...
	*/
	function add_books( $line )
	{
					//$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		$this->books_arr[] = $line;
	}
	/**//**
	* Add HEADER line.  TODO: REFACTOR.  Set will do this...
	*/
	function add_voice( string $voice )
	{
					//$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		$this->body_voice_arr[] = $voice;
		$this->voicecount++;
	}
	/**//**
	* Add HEADER line.  TODO: REFACTOR.  Set will do this...
	*/
	function add_voice_name( string $voice )
	{
					//$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		$this->voice_name_arr[] = $voice;
	}
	// REMOVED: function add_voice_arr( $line )
	/**//**
	* Add HEADER line.  TODO: REFACTOR.  Set will do this...
	*/
	function add_discography( $line )
	{
					//$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		$this->discography .= " / " . $line;
	}
	/**//**
	* Add HEADER line.  TODO: REFACTOR.  Set will do this...
	*/
	function add_file_url( $line )
	{
					//$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		$this->file_url = $line;
	}
	/**//**
	* Add HEADER line.  TODO: REFACTOR.  Set will do this...
	*/
	function add_group( $line )
	{
					//$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		$this->group = $line;
	}
	/**//**
	* Add HEADER line.  TODO: REFACTOR.  Set will do this...
	*/
	function add_instruction( $line )
	{
					//$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		$this->instruction_arr[] = $line;
	}
	/**//**
	* Add HEADER line.  TODO: REFACTOR.  Set will do this...
	*/
	function add_macro( $line )
	{
					//$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		$this->macro_arr[] = $line;
	}
	/**//**
	* Add HEADER line.  TODO: REFACTOR.  Set will do this...
	*/
	function add_userdef_arr( $line )
	{
					//$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		$this->userdef_arr[] = $line;
	}
	/**//**
	* Add HEADER line.  TODO: REFACTOR.  Set will do this...
	*/
	function add_notes( $line )
	{
					//$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		$this->notes = $line;
	}
	/**//**
	* Add HEADER line.  TODO: REFACTOR.  Set will do this...
	*/
	function add_origin( $line )
	{
					//$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		$this->origin = $line;
	}
	/**//**
	* Add HEADER line.  TODO: REFACTOR.  Set will do this...
	*/
	function add_parts( $line )
	{
					//$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		$this->parts = $line;
	}
	/**//**
	* Add HEADER line.  TODO: REFACTOR.  Set will do this...
	*/
	function add_rythm( $line )
	{
					//$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		$this->rythm = $line;
	}
	/**//**
	* Add HEADER line.  TODO: REFACTOR.  Set will do this...
	*/
	function add_source( $line )
	{
					//$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		$this->source = $line;
	}
	/**//**
	* Add HEADER line.  TODO: REFACTOR.  Set will do this...
	*/
	function add_words_bottom( $line )
	{
		//$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		$this->words_bottom_arr[] = $line;
	}
	/**//**
	 * LYRICS not Cainteraichd
	 *
	 * Bar 0 is pickup
	 * */
/*
	function add_words( $line, $verse = 0, $linenum = 1, $barnum = 1 )
	{
					$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		if( ! isset( $this->words_arr[$verse][$linenum][$barnum] ) )
			$this->words_arr[$verse][$linenum][$barnum] = $line;
		else
		{
			if( $barnum == 4 )
			{
				$linenum++;
				$barnum=1;
			}
			else
			{
				$barnum++;
				$this->add_words( $line, $verse = 0, $linenum = 1, $barnum = 1 );
			}
		}
	}
*/
	/**//**
	 * Tune Types in Piping have associated time signatures
	 *
	 * These are Pipe Band specific, not generic!
	 *
	 * */
	function validate_rythm()
	{
					$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		switch( $this->meter )
		{
		case '2/2':
			if( $this->rythm !== "Reel" )
				$this->rythm = "Reel";
			break;
		case '2/4':
			if( $this->rythm !== "March" )
				if( $this->rythm !== "Hornpipe" )
					$this->rythm = "March";
		case '4/4':
			if( $this->rythm !== "March" )
				if( $this->rythm !== "Strathspey" )
					$this->rythm = "March";
			break;
		case '5/4':
		case '3/4':
			if( $this->rythm !== "March" )
				$this->rythm = "March";
			break;
		case '6/8':
			if( $this->rythm !== "March" )
				if( $this->rythm !== "Jig" )
					$this->rythm = "March";
			break;
		case '9/8':
			if( $this->rythm !== "March" )
				if( $this->rythm !== "Jig" )
					if( $this->rythm !== "Slip Jig" )
						$this->rythm = "March";
			break;
		case '12/8':
			if( $this->rythm !== "March" )
				if( $this->rythm !== "Jig" )
					$this->rythm = "March";
			break;
/*
		case default:
			$this->rythm = "March";
			break;
*/
		}
	}
       /**//**
         * Music Lines
         *
         * Bar 0 is pickup
         *
         * */
        function add_body( $bar, $voice = 1, $linenum = 1, $barnum = 1 )
        {
                                        $this->var_dump( __FUNCTION__  . ":" . __LINE__ );
                //echo "Setting Line by Voice $voice $linenum:$barnum:$bar \r\n";
                if( $linenum > $this->line_count )
                {
                        $this->line_count = $linenum;
                }
                if( ! isset( $this->body_arr[$voice][$linenum][$barnum] ) )
                        $this->body_arr[$voice][$linenum][$barnum] = $bar;
                else
                {
                                $barnum++;
                        $this->add_body( $bar, $voice, $linenum, $barnum );
                }
                //$this->var_dump( $this->body_arr );
        }
        /**
    * Add a voice object (AbcVoice) to the tune
    */
	function add_voice_obj( AbcVoice $voice )
	{
		$this->voices_obj_arr[] = $voice;
	}

	/**//**
	* TODO: REfactor to use array key search like above
	*/
	function search_voices_by_name( $name )
	{
					$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		foreach( $this->voices_obj_arr as $voice )
		{
			$vname = $voice->getName();
			if( strcasecmp( $vname, $name ) == 0 )
			{
				return $voice;
			}
		}
		echo "Searching for $name failed\n\r";
		$this->var_dump( $this->voices_obj_arr );
		return FALSE;
	}
	/**//**
	* TODO: REfactor to use array key search like above
	*/
	function search_voices_by_ind( $ind )
	{
					$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		foreach( $this->voices_obj_arr as $voice )
		{
			if( strcasecmp( $voice->getVoiceIndicator(), $ind ) == 0 )
			{
				return $voice;
			}
		}
		return FALSE;
	}
	/**//**
	* TODO: REfactor to use array key search like above
	*/
	// REMOVED: function get_voice_number( $val )
	/**//**
	* TODO: REfactor to use array key search like above
	*/
	// REMOVED: function get_voice_number_by_name( $name )
	// REMOVED: function add_voice_line( string $name, string $bar, int $linenum, int $barnum )
	// REMOVED: function add_melody( $bar,  $linenum = 1, $barnum = 1 )
	// REMOVED: function add_lyrics( $bar,  $linenum = 1, $barnum = 1 )
	/**//************************************************
	* How long (beats) is the bar?
	*
	*
	* SEE https://github.com/jwdj/EasyABC/blob/master/aligner.py
	*
	* @param string bar data
	* @param int default_length normal length of a bar in beats
	* @param string meter the time signature
	****************************************************/
	public function get_bar_length( $data, $default_length, $meter )
	{
		$this->var_dump( get_class() . "::" . __METHOD__ );
		//remove extra fragments that don't affect the bar length
		$data = $this->remove_non_note_fragments( $data );
		$data = $this->replace_chords_by_first( $data );
		$data = $this->remove_gracenotes( $data );
		$total_length = 0;
		$last_broken_rythm = "";
		$tuplet_notes_left = 0;
		$tuplet_time = 2;
		$datalength = strlen( $data );
		for( $i=0; $i<$datalength; $i++ )
		{
		}
		
	
	}
	/**//************************************************
	*
	*
	* SEE https://github.com/jwdj/EasyABC/blob/master/aligner.py
	*
	*
	****************************************************/
	public function remove_non_note_fragments( $data )
	{
		$this->var_dump( get_class() . "::" . __METHOD__ );
		$pattern = array();
		$replace = array();
		$pattern =  "/\[.*\]/";
		$replace = "";
		$pattern =  "/\!.*\!/";
		$replace = "";
		$pattern =  '/\".*\"/';
		$replace = "";
		return preg_replace( $pattern, $replace, $data );
	}
	/**//************************************************
	*
	*
	* SEE https://github.com/jwdj/EasyABC/blob/master/aligner.py
	*
	*
	****************************************************/
	public function replace_chords_by_first( $data )
	{
		$this->var_dump( get_class() . "::" . __METHOD__ );
		return $data;
	}
	/**//************************************************
	*
	*
	* SEE https://github.com/jwdj/EasyABC/blob/master/aligner.py
	*
	*
	****************************************************/
	public function remove_gracenotes( $data )
	{
		$this->var_dump( get_class() . "::" . __METHOD__ );
		//preg_replace( string|array $pattern, string|array $replacement, string|array $subject, int $limit = -1, int &$count = null): string|array|null
		$pattern = "/\{[A-Ga-g]*\}/";
		$replace = "";
		return preg_replace( $pattern, $replace, $data );
		//return $data;
	}

}


