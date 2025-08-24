<?php

/**//***********************
* https://github.com/jwdj/EasyABC/blob/master/tune_elements.py has items we may want eventually (but in python)
***************************/

/***********************************
*	ABC File Structure
*		Tune
*		blankline
*		Tune(s)(optional)
*
*	Tune Structure
*		X: index
*		... header lines
*		K:
*		...body lines
*
*	Body Lines
*		[Instruction]
*		Bar indicators [|:
*		Notes and Embellishments, decorators, etc
*		Bar indicator | [1 |1 ...
*		EOL indicator
*
*
*************************************/

require_once( 'class.abc_tunebase.php' );
require_once( 'class.abc_note.php' );
require_once( 'class.abc_embellishment.php' );
require_once( 'defines.inc.php' );

class abc_token extends origin
{
	protected $token;	//string
}
class abc_comment extends abc_token
{
	//Line starts with % but not %%
}
class abc_wrapped extends abc_token
{
	//string wrapped in a matching pair e.g. { }
	protected $startchar;
	protected $endchar;
	function set( $field, $value = null, $enforce = true )
	{
		switch( $field )
		{
			case "token":
				return $this->set_token( $value );
				break;
			default:
				return parent::set( $field, $value, $enforce );
				break;
		}
	}
	protected function set_token( $value, $enforce )
	{
		if( is_array( $value ) )
		{
			$len = count( $value );
			if( strcmp( $value[0], $startchar ) !== 0 )
			{
				throw new Exception( "We are in the wrong class.  Start char doesn't match!" );
			}
			if( strcmp( $value[$len - 1], $endchar ) !== 0 )
			{
				throw new Exception( "Last char of passed in value doesn't match expected! " );
			}
			return parent::set( "token", implode( $value ), $enforce );
		}
		else
		{
			$len = strlen( $value );
			if( strncmp( $value[0], $startchar, 1 ) !== 0 )
			{
				throw new Exception( "We are in the wrong class.  Start char doesn't match!" );
			}
			if( strncmp( $value[$len], $endchar, 1 ) !== 0 )
			{
				throw new Exception( "Last char of passed in value doesn't match expected! " );
			}
			return parent::set( "token", $value, $enforce );
		}
	}
}
class abc_chord extends abc_wrapped
{
	//string wrapped in " "
	function __construct()
	{
		parent::__construct();
		$this->set( "startchar", '"' );
		$this->set( "endchar", '"' );
	}
}
class abc_decorator extends abc_wrapped
{
	//string wrapped in ! !
	function __construct()
	{
		parent::__construct();
		$this->set( "startchar", '!' );
		$this->set( "endchar", '!' );
	}
}


/*****Inherits
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
	protected $voice_arr;	//V
	protected $discography;	//D
	protected $file_url;	//F
	protected $group;	//G  e.g. G:Flute
	protected $instruction_arr;	//I
	protected $macro_arr;	//m
	protected $notes;	//N  i.e. referrences to other similar tunes.
	protected $origin;	//O  e.g. O:Canada; Nova Scotia; Halifax.
	protected $parts;	//P
	protected $rythm;	//R  e.g. hornpipe, double jig, single jig, 48-bar polka
	protected $source;	//S
	protected $userdef_arr;	//U
	protected $transcription;	//Z
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
	protected $current_voice;	//For when the source file doesn't use [V: at the start of each line
	protected $current_barnum;
	protected $current_linenum;
	function __construct()
	function set( $field, $value )
	function get( $field )
	function get_cannt( $note )
	function xfer_headers( abc_tunesetting $dest )
	function get_header_key( $val )
	function output()
	function build_headers()
	function add_index( $index = 1 )
	function add_key( $key )
	function add_transcription( $line )
	function add_notelength( $line )
	function add_meter( $line )
	function add_tempo( $line )
	function add_title( $line )
	function add_composer( $line )
	function add_history( $line )
	function add_books( $line )
	function add_voice( string $voice )
	function add_voice_name( string $voice )
	function add_voice_arr( $line )
	function add_discography( $line )
	function add_file_url( $line )
	function add_group( $line )
	function add_instruction( $line )
	function add_macro( $line )
	function add_userdef_arr( $line )
	function add_notes( $line )
	function add_origin( $line )
	function add_parts( $line )
	function add_rythm( $line )
	function add_source( $line )
	function add_words_bottom( $line )
	function add_words( $line, $verse = 0, $linenum = 1, $barnum = 1 )
	function validate_rythm()
	function build_body()
	function add_body( $bar, $voice = 1, $linenum = 1, $barnum = 1 )
	function add_voice_obj( abc_voice $voice )
	function search_voices_by_name( $name )
	function search_voices_by_ind( $ind )
	function get_voice_number( $val )
	function get_voice_number_by_name( $name )
	function add_voice_line( string $name, string $bar, int $linenum, int $barnum )
	function add_melody( $bar,  $linenum = 1, $barnum = 1, $addcannt = true, $addabc = false )
	function add_lyrics( $bar,  $linenum = 1, $barnum = 1 )
	function set_current_voice( abc_voice $current_voice )
	function add_harmony( $bar,  $linenum = 1, $barnum = 1 )
	function add_c_harmony( $bar,  $linenum = 1, $barnum = 1 )
	function add_snare( $bar,  $linenum = 1, $barnum = 1 )
	function add_bass( $bar,  $linenum = 1, $barnum = 1 )
	function add_tenor( $bar,  $linenum = 1, $barnum = 1 )
	function add_canntaireachd( $bar,  $linenum = 1, $barnum = 1 )
	function add_ABC( $bar,  $linenum = 1, $barnum = 1 )
	function add_words( $bar,  $linenum = 1, $barnum = 1 )
	function print_tune()
**/ /*Inheritance*/
/**//**********************************************************************************************
*Take apart an ABC file and stick the components into variables within the class for re-assembly later.
*
*Tunesetting is designed (goal) to generate a compliant abc file that has components being used in
*the typesetting of the band settings of Bagpipe (i.e. Pipe Band) music.  This is also including the
*textual representation of the ABC code without embellishments, as well as the Canntaireachd equivalent
*so that those that are challenged reading the notes on the staff might be able to learn the tune in
*other ways.
*
*ABCparser sets out to take an existing tune apart, and eventully do the "translation" steps to
*put that textual representation as well as the canntaireachd into the setting without the typesettor
*having to manually type all of those too.
*
**************************************************************************************************/
class abcparser extends abc_tunebase
{
	protected $tune;
	protected $lines;
	protected $headers;
	protected $body_start;	//!<int which line is the start of the body
	protected $tokens;
	protected $tokens_arr;	//array of classes for the tokens
	function __construct()
	{
		parent::__construct();
		$this->tokens = array();
	}
	/**//**************************************
	* Find the line where the body starts
	*
	* The body starts after the first K: in the tune
	*
	* @param none uses internal
	* @return bool did we find a body start
	*******************************************/
	protected function find_body_start()
	{
		$this->var_dump( get_class() . "::" . __METHOD__ );
		if( ! isset( $this->lines ) )
		{
			throw new Exception( "Variable LINES not set", KSF_FIELD_NOT_SET );
		}
		$count = 0;
		foreach ($this->lines as $line) 
		{
			$count++;
			$l = trim($line);
			if( strncmp( "K:", $l, 2 ) == 0 )
			{
				//This is the last line of the HEADER.
				$this->body_start = $count;
				$this->Log( "Found Body at line:  $this->body_start", 'PEAR_LOG_DEBUG' );
				return TRUE;
			}
		}
		return FALSE;
	}
	/**//***********************************
	* Extract the Headers from a TUNE.
	*
	* At this point, we have been passed in a TUNE, 
	* not a set of tunes
	*
	* @param none assumption tune has been set
	* @return bool sets internal variables.
	****************************************/
	protected function extractHeaders()
	{
		$this->var_dump( get_class() . "::" . __METHOD__ );
		// Iterate through, parsing each header as we get to it
		if( ! isset( $this->lines ) )
		{
			throw new Exception( "Variable LINES not set", KSF_FIELD_NOT_SET );
		}
		$count = 0;
		foreach ($this->lines as $line) 
		{
			$count++;
			if( $count >= $this->body_start )
			{
				//Body start is the line with the first K: in it.
				//By definition this is the last line of the header.
				return TRUE;
			}
			$l = trim($line);
			// Ignore blank lines
			if (empty($l))
			{
				//EasyABC for one uses a blank line as a tune separator so we don't want to include them.
				//If we hit here, we've hit the end of the tune, and therefore an error
				$this->Log( "We've hit a blank line in the middle of a HEADER.  Line: $count", PEAR_LOG_ERR );
				//echo "********Blank Line********\n\r";
				continue;
			}
			//Headers have a : as the 2nd character	
			if( $l[1] == '%')
			{
				//comment % or instruction %%
				//echo "Comment or Instruction:: " . $l . "\n\r";
				$this->add_body( $l );
				continue;
			}
			if ($l[1] !== ':')
			{
				//echo "**BODY**::" . $l . "\n\r";
				$this->Log( "We've hit a BODY line in the middle of a HEADER.  Line: $count", PEAR_LOG_ERR );
				$this->Log( $l, PEAR_LOG_ERR );
				continue;
			}
			else if( $l[0] == '|' )
			{
				//repeat sign, not header
				$this->Log( "We've hit a BODY line in the middle of a HEADER.  Line: $count", PEAR_LOG_ERR );
				$this->Log( $l, PEAR_LOG_ERR );
				continue;
			}
			// Split the line into header and value
			$parts = explode(':', $l, 2);
			//headers_anglo_arr is for converting the letter into the internal variable name
			//echo "Match Header::" . $parts[0] . " :: " . $parts[1] . "\r\n";
			$internal = $this->headers_anglo_arr[$parts[0]];	//internal variable name
			//echo "Section::" . $internal . "\r\n";
			$this->set( $internal, trim( $parts[1] ) );
			$this->lines[$count-1] = "";
		}
		
		return true;
	}
	/**//***********************************
	* Extrac the body once we've extracted the headers
	*
	* Assumption is that extractHeaders has already been run (->lines set)
	* @param none assumption tune has been set
	* @return bool sets internal variables.
	****************************************/
	protected function extractBodyExplode()
	{
		$this->var_dump( get_class() . "::" . __METHOD__ );
		$melodylinenum = 1;
		$Wordslinenum = 1;
		// Split into lines
		if( ! isset( $this->lines ) )
		{
			throw new Exception( "Variable LINES not set", KSF_FIELD_NOT_SET );
		}
		$count = 0;
		// Iterate through, parsing each line
		foreach ($this->lines as $line) 
		{
		$this->var_dump( get_class() . "::" . __METHOD__ . "::" . __LINE__ );
		$this->var_dump( "BEFORE Trim - Line: ", PEAR_LOG_DEBUG );
		$this->var_dump( $line, PEAR_LOG_DEBUG );
			if( $count < $this->body_start )
			{
				//Increment to the next line, as this is a header
				$this->Log( "Skip over header lines.  Line: $count", PEAR_LOG_INFO );
				$count++;
				continue;
			}
			$l = trim($line);
		$this->var_dump( get_class() . "::" . __METHOD__ . "::" . __LINE__ );
		$this->var_dump( "Trimmed line: ", PEAR_LOG_DEBUG );
		$this->var_dump( $l, PEAR_LOG_DEBUG );
			// Ignore blank lines
			if (empty($l))
			{
				//EasyABC for one uses a blank line as a tune separator so we don't want to include them.
				$this->Log( "We've hit a blank line in the middle of a BODY.  Line: $count", PEAR_LOG_ERR );
				continue;
			}
			if( ! strncmp( $l[0], 'w', 1 ) )
			{
				echo "*****Lyrics" . "\n\r";
				//Line is Lyrics
				$lyrics = explode(':', $l, 2);
					//For some reason lines with a double bar line AB || abc| def | edc... doesn't pickup the leading AB
				$this->var_dump( __LINE__ . "::::" .$lyrics );
				$this->var_dump( $lyrics );
				$bars = explode('|', $lyrics[1]);
				$barnum = 1;
				foreach( $bars as $bar )
				{
					$this->add_lyrics( $bar, $melodylinenum, $barnum );
					$barnum++;
				}
			}
			else
			if( ! strncmp( $l[0], 'W', 1 ) )
			{
				echo "******WORDS" . "\n\r";
				//Words at bottom
				$words = explode(':', $l, 2);
				$this->add_words_bottom( $words[1], $Wordslinenum, 1 );
				$Wordslinenum++;
			}
			// Split the line into header and value
			$this->var_dump( get_class() . "::" . __METHOD__ . "::" . __LINE__ );
			$this->Log( "Splitting Bars apart", PEAR_LOG_INFO );
			$this->var_dump( $l, PEAR_LOG_DEBUG );
			$bars = explode('|', $l);
			$this->process_bars( $bars );
		}
		
		return true;
	}
	function extractBody()
	{
		$this->extractBodyToken();
	}
	/**//***********************************
	* Extrac the body once we've extracted the headers
	*
	* Assumption is that extractHeaders has already been run (->lines set)
	* @param none assumption tune has been set
	* @return bool sets internal variables.
	****************************************/
	protected function extractBodyToken()
	{
		$this->var_dump( get_class() . "::" . __METHOD__ );
		// Split into lines
		if( ! isset( $this->lines ) )
		{
			throw new Exception( "Variable LINES not set", KSF_FIELD_NOT_SET );
		}
		$count = 0;
		// Iterate through, parsing each line
		foreach ($this->lines as $line) 
		{
			//Tokenize the line
			$tokenarray = $this->tokenizer( $line, false );
			$this->var_dump( get_class() . "::" . __METHOD__ . "::" . __LINE__, PEAR_LOG_INFO );
			$this->var_dump( "!@!@!@!@!@!@!@!@!@!@!@!@!@!@!@!@!@!@!@!@!@!@!@!", PEAR_LOG_INFO );
			$this->var_dump( $tokenarray, PEAR_LOG_INFO );
			$this->var_dump( "!@!@!@!@!@!@!@!@!@!@!@!@!@!@!@!@!@!@!@!@!@!@!@!", PEAR_LOG_INFO );
			$bar = "";
			foreach( $tokenarray as $token )
			{
				if( ( strncmp( "|", $token, 1 ) == 0 ) OR ( strncmp( ":", $token, 1 ) == 0 ) )
				{
					//Bar Line.
					$bar .= $token;
					$this->add_bar( $bar );
					$bar = "";
				}
				else
				{
					$bar .= $token;
				}
			}

		}
		
		return true;
	}
	function add_bar( $bar )
	{
		$this->var_dump( get_class() . "::" . __METHOD__ . ":: $bar", PEAR_LOG_DEBUG );
		if( strlen( $bar ) < 1 )
		{
			$this->var_dump( "Bar of zero length.  Skipping", PEAR_LOG_DEBUG );
		}
		if( isset( $this->current_voice ) )
		{
			$v = $this->current_voice;
			$this->Log( "Current Voice $v", PEAR_LOG_DEBUG );
		}
		else
		{
			$v = "melody";
			$this->Log( "Current Voice not set so calling $v", PEAR_LOG_DEBUG );
		}
		$fn = "add_" . $v;
		$this->$fn( $bar );
	}
	/**//****************************************
	* Process data from the bars
	*
	* We need to process each bar since there are instructions that 
	* can be embedded in a bar (K, V, etc) that could change things.
	* For tunes I typeset this is not so much true (I would do that on another line)
	*
	* @param array bars array of data for bars.
	*********************************************/
	protected function process_bars( /*array*/ $bars )
	{
		$this->var_dump( get_class() . "::" . __METHOD__ . "::" . __LINE__ );
		$this->var_dump( $bars, PEAR_LOG_DEBUG );
		$cannt_arr = array();
		$currentbar = 0;
		$zerobar = false;
		foreach( $bars as $bar )
		{
			$currentbar++;
			if( strlen( $bar ) == 0 )
			{
				//A double bar line will put us here
				$this->var_dump( get_class() . "::" . __METHOD__ . "::" . __LINE__, PEAR_LOG_DEBUG );
				$this->var_dump( "We hit a bar of ZERO length." , PEAR_LOG_DEBUG );
				$zerobar = true;
				continue;
			}
			else
			{
				$bar = trim( $bar );
				if( $zerobar )
				{
					$zerobar = false;
					$bar = "|" . $bar;
				}
				$this->add_bar( $bar );
	/** Refactoring
	 * A Parser should not be adding extra processing.
	 * Moving to aspd_tune
				$this->var_dump( get_class() . "::" . __METHOD__ . "::" . __LINE__, PEAR_LOG_DEBUG );
				$tokens = $this->tokenizer( $bar, true );	//array without timing
				$this->var_dump( get_class() . "::" . __METHOD__ . "::" . __LINE__, PEAR_LOG_DEBUG );
				$this->var_dump( $tokens, PEAR_LOG_DEBUG );
				$notes = implode( ' ', $tokens );
				$this->var_dump( get_class() . "::" . __METHOD__ . "::" . __LINE__ );
				$this->var_dump( $notes, PEAR_LOG_DEBUG );
				$nograce = $this->remove_gracenotes( $notes );
				$this->var_dump( $nograce, PEAR_LOG_DEBUG );
				$this->add_ABC( $nograce );
				if( is_array( $notes ) )
				{
					foreach( $notes as $note )
					{
						$cannt = $this->get_cannt( $note );
						if( strnmp( $cannt, $note, strlen($note) ) == 0 )
						{
							//we don't have a cannt equivalent
							$cannt_arr[] = "OOPS";
							$this->var_dump( "Didn't find a CANNT string for: $notes", PEAR_LOG_ERR );
						}
						else
						{
							$cannt_arr[] = $cannt;
							$this->var_dump( "CANNT string for $notes is $cannt", PEAR_LOG_DEBUG );
						}
					}
					$c = implode( " ", $cannt_arr );
					$this->add_canntaireachd( $c );
				}
				//$tune->add_melody( "{g}GA {GAG}AB",  1, 1 );
	 ***/
			}
		}
	}
	/**//************************************
	* Take in string of data, return tokens
	*
	* Assuming we want to Strip Timing as the
	* usual case for tokenizing would be to convert
	* into another form, be it ABC, Cannt, 
	* or some other representation
	*
	* @param string data
	* @return array tokens
	*****************************************/
	protected function tokenizer( $data, $striptiming = true )
	{
		$this->var_dump( get_class() . "::" . __METHOD__ );
		$this->var_dump( $data );
		if( strlen( $data ) > 0 )
		{
			$clean = trim( $data );	//strip whitespace front and back
		}
		else
		{
			$clean = "";
			$this->Log( "Passed in empty string.  Intentional?", PEAR_LOG_INFO );
		}
/*********
*	https://abc.sourceforge.net/standard/abc2midi.txt
* Line can be a bunch of:
* Notes
* Embellishments
* Guitar Chords ""
* Decorations !!
* Chords [ ]
* Bar Lines
**********/
		$len = strlen( $clean );
		$isComment = false;	//Comment or %%MIDI.  either way, don't process the rest of the bar (line)
		$conscount = 0;	//How many notes in a row note separated by a space 
		$embellishment = "";
		$full_emb = "";
		$token = "";
		$emb_started = false;
		$emb_ended = false;
		$emb_done = false;
		$tiednote = false;
		$isBarLine = false;		//There are variations of barlines, some with multi characters, so need to check
		for( $i=0; $i<$len; $i++ )
		{
			if( $isComment )
				return $this->tokens;
	
			switch( $clean[$i] )
			{
				case ' ':
					//Separator between beats
					if( ! $tiednote )
					{
						//I would separate tied notes across beats with the space for readability...
						$conscount = 0;
						if( strlen( $token ) > 0 )
						{
							$this->set( "tokens", $token );
							if( $isBarLine )
							{
								$this->set( "tokens", "|" );
								$isBarLine = false;
							}
						}
						else
							$this->var_dump( "How did we end up with a zero length token?", PEAR_LOG_DEBUG );
						$token = "";
					}
					else
					{
					}
					break;
				case '[':
					if( $isBarLine )
					{
						//As the last character was a barline, we need to add it as a token
						$this->set( "tokens", "|" );
						$isBarLine = false;
					}
					//start of instruction OR chord (grouped notes)
					break;
				case ']':
					//end of instruction or chord
					if( $isBarLine )
					{
						//As the last character was a barline, we need to add it as a token
						//NOTE THIS IS PROBABLY AN ERROR
						$this->set( "tokens", "|" );
						$isBarLine = false;
					}
					break;
				case '{':
					if( $isBarLine )
					{
						//As the last character was a barline, we need to add it as a token
						$this->set( "tokens", "|" );
						$isBarLine = false;
					}
					//Start of Gracenotes
      						$this->var_dump( __FUNCTION__  . ":" . __LINE__ . "::" . "start emb" );
                                        $emb_started = true;
					break;
				case '}':
					if( $isBarLine )
					{
						//As the last character was a barline, we need to add it as a token
						//ERROR?  Should embellishments go across bars?
						$this->set( "tokens", "|" );
						$isBarLine = false;
					}
					//End of Gracenotes
					 $emb_ended = true;
					 $emb_done = false;	//The gracenotes may be done, but we still need a melody note and duration
					 $emb_started = false;
                                         	$this->var_dump( __FUNCTION__  . ":" . __LINE__ . "::" . "end emb" );
                                         $full_emb = "{" . "$embellishment" . "}";
                                         	$this->var_dump( __FUNCTION__  . ":" . __LINE__ . "::" . $full_emb );
                                         $embellishment = "";
					break;
				case 'a':
				case 'b':
				case 'c':
				case 'd':
				case 'e':
				case 'f':
				case 'g':
				case 'A':
				case 'B':
				case 'C':
				case 'D':
				case 'E':
				case 'F':
				case 'G':
					//NOTE
					if( $isBarLine )
					{
						//As the last character was a barline, we need to add it as a token
						$this->set( "tokens", "|" );
						$isBarLine = false;
					}
					if( $tiednote )
					{
						//In piping music we shouldn't see tied gracenotes.
						//We aren't adding tied note symbols into our token so
						//we should be able to compare the last character in the token
						//to this one
						if( strncmp( $clean[$i], $clean[$i-2], 1 ) == 0 )
						{
							//same note
						}
						else
						{	
							$token = $clean[$i];
							$tiednote = false; 	//slurred note?
						} 
					}
					else
 					if( $emb_started ) 
					{ 
						//These are gracenotes
						$embellishment .= $clean[$i];
						$this->var_dump( __FUNCTION__  . ":" . __LINE__ . "::" . $clean[$i] ); 
						$this->var_dump( __FUNCTION__  . ":" . __LINE__ . "::" . $embellishment ); 
					} 
					else 
					{ 
						//These are NOT gracenotes.  These are melody.
						if( $conscount == 0 )
						{
							$token = $full_emb . $clean[$i]; //Can't set token yet, as there might be timing modifiers as the next char.
							$conscount++;
						}
						else
						{
							//This is the 2nd melody note between spaces
							if( strlen( $token ) > 0 )
								$this->set( "tokens", $token );
							else
								$this->var_dump( "How did we end up with a zero length token? MELODY", PEAR_LOG_DEBUG );
							$token = $clean[$i];
							$conscount = 0;
						}
					}

					break;
				case '<':
				case '>':
				case '/':
					if( $isBarLine )
					{
						//As the last character was a barline, we need to add it as a token
						//NOW THIS WOULD BE AN ERROR as timing modifiers should be immediately behind a note
						$this->set( "tokens", '"ERROR TIMING"|' );
						$isBarLine = false;
					}
					//Timing modifier
					if( ! $striptiming )
						$token .= $clean[$i];
					else
						$token .= " ";
					break;
				case 'H':
					//Fermata
					if( $isBarLine )
					{
						//As the last character was a barline, we need to add it as a token
						$this->set( "tokens", "|" );
						$isBarLine = false;
					}
					break;
				case '0':
				case '1':
				case '2':
				case '3':
				case '4':
				case '5':
				case '6':
				case '7':
				case '8':
				case '9':
					if( $isBarLine )
					{
						//As the last character was a barline, we need to add it as a token
						//NOW THIS WOULD BE AN ERROR as timing modifiers should be immediately behind a note
						$this->set( "tokens", '"ERROR TIMING"|' );
						$isBarLine = false;
					}
					//For the moment going to assume this is a duration modifier of a note.
					if( ! $striptiming )
						$token .= $clean[$i];
					break;
				case '-':
					//tied note
					if( ! $striptiming )
						$token .= $clean[$i];
					else
					{
						$tiednote = true;
					}
					break;
				case '(':
					if( $isBarLine )
					{
						//As the last character was a barline, we need to add it as a token
						$isBarLine = false;
					}
					break;
				case ')':
					if( $isBarLine )
					{
						//As the last character was a barline, we need to add it as a token
						$this->set( "tokens", '"ERROR?"|' );
						$isBarLine = false;
					}
					break;
				case ':':
					if( $isBarLine )
					{
						//As the last character was a barline, we need to add it as a token
						$this->set( "tokens", "|:" );
						$isBarLine = false;
					}
					break;
				case '|':
					//barline
					if( $isBarLine )
					{
						//already had a barline.  So double bar line
						$this->set( "tokens", "||" );
						$isBarLine = false;
					}
					else
					{
						$isBarLine = true;
					}
					break;
				case '^':
					if( $isBarLine )
					{
						$this->set( "tokens", '"ERROR TIMING"|' );
						$isBarLine = false;
					}
					break;
				case '_':
					if( $isBarLine )
					{
						//already had a barline.  So double bar line
						$this->set( "tokens", '"ERROR TIED NOTE should be behind note"|' );
						$isBarLine = false;
					}
					break;
				case '!':
					if( $isBarLine )
					{
						$this->set( "tokens", '|' );
						$isBarLine = false;
					}
					//Ornaments
					break;
				case '.':
					if( $isBarLine )
					{
						$this->set( "tokens", '|' );
						$isBarLine = false;
					}
					break;
				case 'x':
					if( $isBarLine )
					{
						$this->set( "tokens", '|' );
						$isBarLine = false;
					}
					break;
				case 'X':
					if( $isBarLine )
					{
						$this->set( "tokens", '|' );
						$isBarLine = false;
					}
					break;
				case 'z':
					if( $isBarLine )
					{
						$this->set( "tokens", '|' );
						$isBarLine = false;
					}
					break;
				case 'Z':
					if( $isBarLine )
					{
						$this->set( "tokens", '|' );
						$isBarLine = false;
					}
					break;
				case '%':
					//comment or Instruction
					if( ! $isComment )
					{
						$isComment = true;
						//We need to grab the rest of the line as a whole
							$token = substr( $clean, $i );
							$i = $i + strlen( $token );	//Move counter (pointer) to end of line
							if( strlen( $token ) > 0 )
								$this->set( "tokens", $token );
							else
								$this->var_dump( "How did we end up with a zero length token? ESPECIALLY FOR A COMMENT!!", PEAR_LOG_DEBUG );
					}
					else
					{
						$isInstruction = true;
						//We need to grab the rest of the line as a whole
					}
					break;
				case '$':
					//EOL indicator usually
					if( $isBarLine )
					{
						$this->set( "tokens", '|' );
						$isBarLine = false;
					}
					break;
			}
		}
		return $this->tokens;
			
	}
	/**//************************************************
	*
	*
	****************************************************/
	public function process()
	{
		$this->var_dump( get_class() . "::" . __METHOD__ );
		//$this->var_dump( __FILE__ . "::" . __LINE__ . "\n\r" );
		// Split into lines
		if( ! isset( $this->tune ) )
		{
		//	$this->var_dump( __FILE__ . "::" . __LINE__ . "\n\r" );
			throw new Exception( "Variable TUNE not set", KSF_FIELD_NOT_SET );
		}
		try {
		//$this->var_dump( __FILE__ . "::" . __LINE__ . "\n\r" );
			$this->lines = explode("\n", $this->tune);
			$this->find_body_start();
			$this->extractHeaders();
			$this->extractBody();
		}
		catch( Exception $e )
		{
			throw $e;
		}
	}
	/**//************************************************
	* Is the passed in bar an anacrusis?
	*
	* SEE https://github.com/jwdj/EasyABC/blob/master/aligner.py
	*
	* @param string bar data
	* @param int default_length normal length of a bar in beats
	* @param string meter the time signature
	* @return bool
	****************************************************/
	public function is_likely_anacrusis( $bar, $default_length, $meter )
	{
		$this->var_dump( get_class() . "::" . __METHOD__ );
		return FALSE;
	}
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
	/**//******************************************************************************
	*
	*
	* From https://github.com/jwdj/EasyABC/blob/master/abc_context.py
	*
	***********************************************************************************/
	function get_tune_start_line( )
	{
	}
	/**//******************************************************************************
	*
	*
	* From https://github.com/jwdj/EasyABC/blob/master/abc_context.py
	*
	***********************************************************************************/
	/**//******************************************************************************
	*
	*
	* From https://github.com/jwdj/EasyABC/blob/master/abc_context.py
	*
	***********************************************************************************/
	/**//******************************************************************************
	*
	*
	* From https://github.com/jwdj/EasyABC/blob/master/abc_context.py
	*
	***********************************************************************************/
}
