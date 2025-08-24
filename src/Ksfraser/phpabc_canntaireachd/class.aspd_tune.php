<?php
/**//**************************************************
 * Music Lines
 *
 * Bar 0 is pickup
 * 4 regular bars to a line.
 * Output will only do 16 lines (8 2line parts)
 *
 * V1 Melody
 * 2	cainntairechd
 * 3	ABC
 * 4	Harmony
 * 5	Counter Harmony
 * 6	Snare
 * 7	Bass
 * 8	Tenor
 *************************************** */
require_once( 'class.tunesetting.php' );
class aspd_tune extends abc_tunesetting
{
	protected $current_voice;	//For when the source file doesn't use [V: at the start of each line
	protected $current_barnum;
	protected $current_linenum;
	protected $tokens;
	function __construct()
	{
		parent::__construct();
		$this->transcription = "Kevin Fraser, <bagpipes@ksfraser.com> ";
			//$voice_indicator, $name = "", $sname = "", $stem = null, $gstem = "up", $octave = 0, $transpose = 0
/**
 *Added to BASE
		$vm = new abc_voice( 'M', "Melody", "Melody", 'down', 'up', 0, 0, "add_melody" );
		//Lyrics need to go first so that when searching by indicator, for legacy listings, it gets found.
		$vl = new abc_voice( 'w', "Lyrics", "Lyrics", 'down', 'up', 0, 0, "add_lyrics" );
*/
		$va = new abc_voice( 'w', "ABC", "ABC", 'down', 'up', 0, 0, "add_ABC" );
		$vn = new abc_voice( 'w', "Canntaireachd", "Cannt", 'down', 'up', 0, 0, "add_canntaireachd" );
		$vw = new abc_voice( 'W', "Words", "Words", 'down', 'up', 0, 0, "add_words" );
		$vh = new abc_voice( 'H', "Harmony", "Harmony", 'down', 'up', 0, 0, "add_harmony" );
		$vc = new abc_voice( 'C', "C-Harmony", "C-Harmony", 'down', 'up', 0, 0, "add_c_harmony" );
		$vs = new abc_voice( 'S', "Snare", "Snare", 'down', 'up', 0, 0, "add_snare" );
		$vb = new abc_voice( 'B', "Bass", "Bass", 'down', 'up', 0, 0, "add_bass" );
		$vt = new abc_voice( 'T', "Tenor", "Tenor", 'down', 'up', 0, 0, "add_tenor" );
		$vA = new abc_voice( 'BA', "Brass A", "Brass A", 'down', 'up', 0, -1, "add_brassA" );
		$vA = new abc_voice( 'BB', "Brass B", "Brass B", 'down', 'up', 0, -1, "add_brassB" );
		$vA = new abc_voice( 'BC', "Brass C", "Brass C", 'down', 'up', 0, -1, "add_brassC" );
		$vA = new abc_voice( 'BD', "Brass D", "Brass D", 'down', 'up', 0, -1, "add_brassD" );
		//$this->add_voice_obj( $vm );
		$this->add_voice_obj( $vh );
		$this->add_voice_obj( $vc );
		$this->add_voice_obj( $vs );
		$this->add_voice_obj( $vb );
		$this->add_voice_obj( $vt );
		//$this->add_voice_obj( $vl );
		$this->add_voice_obj( $va );
		$this->add_voice_obj( $vn );
		$this->add_voice_obj( $vw );
		$this->add_userdef_arr( 'R = ///');
		$this->add_userdef_arr( 'r = //');
		$this->add_userdef_arr( 'V = !accent!');
		//$this->var_dump( $this->voice_name_arr );
		//$this->var_dump( $this->body_voice_arr );
	}
	function set( $field, $value = null, $enforce_only_native_vars = true )
	{
					//$this->var_dump( __FILE__ . "::" . __FUNCTION__  . ":" . __LINE__ );
		if( $field == "current_voice" )
		{
			$this->set_current_voice( $value );
		}
		else
		{
			parent::set( $field, $value, $enforce_only_native_vars );
		}
	}
	function set_current_voice( abc_voice $current_voice )
	{
					$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		$this->current_voice = $current_voice;
	}
	/**//**
	 * Music Lines
	 *
	 * Bar 0 is pickup
	 * 4 regular bars to a line.
	 * Output will only do 16 lines (8 2line parts)
	 * 
	 * We are overriding base class because
 	 * for pipe tunes I want a 4 bar to a line format
	 * whereas other music could be more.
	 *
	 * */
	function add_body( $bar, $voice = 1, $linenum = 1, $barnum = 1 )
	{
		$this->var_dump( __FILE__ . "::" . __FUNCTION__  . "::" . __LINE__ . ":: $bar", PEAR_LOG_DEBUG );
		//echo "Setting Line by Voice $voice $linenum:$barnum:$bar \r\n";
		if( $linenum > $this->line_count )
		{
			$this->line_count = $linenum;
			//echo "Setting Linecount to $linenum by Voice $voice:$linenum:$barnum \r\n";
			//$this->var_dump( $bar );
		}
		if( ! isset( $this->body_arr[$voice][$linenum][$barnum] ) )
		{
			$this->var_dump( __FUNCTION__  . ":" . __LINE__ . ": Adding Bar:: " . $bar );
			$this->body_arr[$voice][$linenum][$barnum] = $bar;
		}
		else
		{
			if( $barnum == 4 )
			{
				//Bar 4 for this line is already set
				//echo "Bar 4 already set for $voice:$linenum:$barnum \n\r";
				$linenum++;
				$barnum=1;
			}
			else
			{
				$barnum++;
			}
			$this->var_dump( __FUNCTION__  . ":" . __LINE__ . ": Recursive Call new Bar/Line number :: " . $linenum . "::" . $barnum );
			$this->add_body( $bar, $voice, $linenum, $barnum );
		}
		//$this->var_dump( $this->body_arr );
	}
	/**//************************************
	* Take in string of data, return tokens
	*
	* Assuming we DO NOT want to Strip Timing.
	* We would be using the tokens for validity checking etc.
	*
	* @param string data
	* @return array tokens
	*****************************************/
	protected function tokenizer( $data, $striptiming = false )
	{
		$this->var_dump( get_class() . "::" . __METHOD__ );
		$this->var_dump( $data );
		$clean = trim( $data );	//strip whitespace front and back
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
		$this->tokens = array();	
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
						$this->var_dump( __FUNCTION__  . ":" . __LINE__ . "::Setting Tokens with Token::" . $token ); 
						$this->set( "tokens", $token );
						$token = "";
					}
					else
					{
					}
					break;
				case '[':
					//start of instruction OR chord (grouped notes)
					break;
				case ']':
					//end of instruction or chord
					break;
				case '{':
					//Start of Gracenotes
      						$this->var_dump( __FUNCTION__  . ":" . __LINE__ . "::" . "start emb" );
                                        $emb_started = true;
					break;
				case '}':
					//End of Gracenotes
					 $emb_ended = true;
					 $emb_done = false;	//The gracenotes may be done, but we still need a melody note and duration
					 $emb_started = false;
                                         	$this->var_dump( __FUNCTION__  . ":" . __LINE__ . "::" . "end emb" );
                                         $full_emb = "{" . "$embellishment" . "}";
                                         	$this->var_dump( __FUNCTION__  . ":" . __LINE__ . "::FULL EMB:::" . $full_emb );
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
					if( $tiednote )
					{
						//In piping music we shouldn't see tied gracenotes.
						//We aren't adding tied note symbols into our token so
						//we should be able to compare the last character in the token
						//to this one
						if( strncmp( $clean[$i], $clean[$i-2], 1 ) == 0 )
						{
							//same note
							if( ! striptiming )
							{
								$token = $clean[$i];
								$this->var_dump( __FUNCTION__  . ":" . __LINE__ . "::Token::" . $token ); 
							}
							else
							{
								//since its the same note, this is a timing modifier
							}
						}
						else
						{	
							$token = $clean[$i];
							$this->var_dump( __FUNCTION__  . ":" . __LINE__ . "::Token::" . $token ); 
							$tiednote = false; 	//slurred note?
						} 
					}
					else
 					if( $emb_started ) 
					{ 
						//These are gracenotes
						$embellishment .= $clean[$i];
						$this->var_dump( __FUNCTION__  . ":" . __LINE__ . "::Note to add::" . $clean[$i] ); 
						$this->var_dump( __FUNCTION__  . ":" . __LINE__ . "::Embelllishment so far::" . $embellishment ); 
					} 
					else 
					{ 
						//These are NOT gracenotes.  These are melody.
						if( $conscount == 0 )
						{
							$token = $full_emb . $clean[$i]; //Can't set token yet, as there might be timing modifiers as the next char.
							$full_emb = "";
							$this->var_dump( __FUNCTION__  . ":" . __LINE__ . "::Token::" . $token ); 
							$conscount++;
						}
						else
						{
							//This is the 2nd melody note between spaces
							$this->set( "tokens", $token );
							$token = $clean[$i];
							$this->var_dump( __FUNCTION__  . ":" . __LINE__ . "::Token::" . $token ); 
							$conscount = 0;
						}
					}

					break;
				case '<':
				case '>':
				case '/':
					//Timing modifier
					if( ! $striptiming )
					{
						$token .= $clean[$i];
						$this->var_dump( __FUNCTION__  . ":" . __LINE__ . "::RToken::" . $token ); 
					}
					else
					{
						//$token .= " ";
						$this->var_dump( __FUNCTION__  . ":" . __LINE__ . "::RToken::" . $token ); 
					}
					break;
				case 'H':
					//Fermata
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
					//For the moment going to assume this is a duration modifier of a note.
					if( ! $striptiming )
					{
						$token .= $clean[$i];
						$this->var_dump( __FUNCTION__  . ":" . __LINE__ . "::RToken::" . $token ); 
					}
					break;
				case '-':
					//tied note
					if( ! $striptiming )
					{
						$token .= $clean[$i];
						$this->var_dump( __FUNCTION__  . ":" . __LINE__ . "::RToken::" . $token ); 
					}
					else
					{
						$tiednote = true;
					}
					break;
				case '(':
					break;
				case ')':
					break;
				case ':':
					break;
				case '|':
					//barline
					break;
				case '^':
					break;
				case '_':
					break;
				case '!':
					//Ornaments
					break;
				case '.':
					break;
				case 'x':
					break;
				case 'X':
					break;
				case 'z':
					break;
				case 'Z':
					break;
				case '%':
					//comment
					$isComment = true;
					break;
				case '$':
					//EOL indicator usually
					break;
			}
		}
		$this->set( "tokens", $token );
		return $this->tokens;
			
	}
	/**//***************************************************************************************
	* Take a bar's input and put into the tune's intermediate storage
	*
	*
	******************************************************************************************/
	function add_melody(  $bar,  $linenum = 1, $barnum = 1, $addcannt = true, $addabc = false )
	{
		parent::add_melody(  $bar,  $linenum, $barnum, $addcannt = true, $addabc = false );
		if( strlen( $bar ) > 0 )
		{
		  	if( $addcannt )
	                {
				/*
	                        //need to parse the bar
	                        $notes = explode( " ", $bar );
	                        $str = "";
	                        $cannt = "";
	                        $this->var_dump( "NOTES: " );
	                        $this->var_dump(  $notes );
				$tokens = $this->tokenizer( $notes, false );
				*/
				$tokens = $this->tokenizer( $bar, true );
				//ADD CANNT
				$cannt_arr = array();
				foreach( $tokens as $token )
				{
					if( strlen( $token ) < 1 )
					{
						//empty so don't look up
						continue;
					}
					$cannt = $this->get_cannt( $token );
					if( strcmp( $cannt, $token ) == 0 )
					{
						//we don't have a cannt equivalent
						$cannt_arr[] = "OOPS";
						$this->var_dump( "Didn't find a CANNT string for::$token::", PEAR_LOG_ERR );
					}
					else
					{
						$cannt_arr[] = $cannt;
						$this->var_dump( "CANNT string for $token is $cannt", PEAR_LOG_DEBUG );
					}
					$cannt = "";
				}
				$c = implode( " ", $cannt_arr );
				$this->add_canntaireachd( $c );
			}
                }
	}
	function add_harmony( $bar,  $linenum = 1, $barnum = 1 )
	{
					$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		$this->add_voice_line( "Harmony", $bar,  $linenum, $barnum );
	}
	function add_c_harmony( $bar,  $linenum = 1, $barnum = 1 )
	{
					$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		$this->add_voice_line( "C-Harmony", $bar,  $linenum, $barnum );
	}
	function add_snare( $bar,  $linenum = 1, $barnum = 1 )
	{
					$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		$this->add_voice_line( "Snare", $bar,  $linenum, $barnum );
	}
	function add_bass( $bar,  $linenum = 1, $barnum = 1 )
	{
					$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		$this->add_voice_line( "Bass", $bar,  $linenum, $barnum );
	}
	function add_tenor( $bar,  $linenum = 1, $barnum = 1 )
	{
					$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		$this->add_voice_line( "Tenor", $bar,  $linenum, $barnum );
	}
	function add_canntaireachd( $bar,  $linenum = 1, $barnum = 1 )
	{
					$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		$this->add_voice_line( "Canntaireachd", $bar,  $linenum, $barnum );
	}
	function add_ABC( $bar,  $linenum = 1, $barnum = 1 )
	{
					$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		$this->add_voice_line( "ABC", $bar,  $linenum, $barnum );
	}
	function add_words( $bar,  $linenum = 1, $barnum = 1 )
	{
					$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		$this->add_voice_line( "Words", $bar,  $linenum, $barnum );
	}
	/**//**
	 * This is the complicated part - multiple valid body styles
	 *
	 * I'm going to standardize on the following:
	 * [V:M] a [|: ABcd | abcd | abcd |abcd | $
	 * w:    a [|: ABcd | abcd | abcd |abcd | $                          % ABC
	 * w:    a [|: en em o o | en em o o | en em o o | en em o o | $     % Cainntearachd
	 * [V:H] a [|: ABcd | abcd | abcd |abcd | $
	 * [V:C] a [|: ABcd | abcd | abcd |abcd | $
	 * [V:S] a [|: ABcd | abcd | abcd |abcd | $
	 * [V:B] a [|: ABcd | abcd | abcd |abcd | $
	 * [V:T] a [|: ABcd | abcd | abcd |abcd | $
	 * w:    a [|: lyr-ics lyr-ics | lyr-ics lyr-ics | lyr-ics lyr-ics | lyr-ics lyr-ics | $
	 * W:	Non Lyric Text on bottom
	 * W:	----
	 *
	 * TODO: Figure out how to determine PART endings.
	 * */
	function build_body()
	{
					$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
		$this->body = "";
		//$this->var_dump( $this->voicecount );
		//$this->var_dump( $this->body_voice_arr );
		$WordsLine = $this->get_voice_number_by_name( "Words" );
		$this->var_dump( "Words Line is $WordsLine \n\r" );
		for( $linenum=1; $linenum <= $this->line_count; $linenum++ )
		{
			for( $voice = 0; $voice <= $this->voicecount; $voice++ )
			{
				if( isset( $this->body_arr[$voice] ) )
				{
					//If the voice ISN"T set no point doing this...
					$ind = $this->body_voice_arr[$voice];
					if( strcasecmp( $ind, "w" ) == 0 )
					{
						$linestart = $ind . ": ";
					}
					else
					{
						$linestart = "[V:" . $ind . "] ";
					}
					$line = "";
					//Bar 0 is a pickup, only applies to odd lines within a part.
					//However, what about second endings for an entire line - throws
					//off the line number count.
					for( $barnum=0; $barnum < 5; $barnum++ )
					{
						if( isset( $this->body_arr[$voice][$linenum][$barnum] ) )
						{
							if( $voice == $WordsLine)
							{
								$this->var_dump( "This is a Wordsline\n\r" );
								$this->add_words_bottom( $this->body_arr[$voice][$linenum][$barnum] );
							}
							else
							{
								$line .= $this->body_arr[$voice][$linenum][$barnum];
							}
						}
						else
						{
							if( strcasecmp( $ind, "W" ) )
							{
								$line .= " Z ";
								//If the word (lyric) bar is not set, place blank bar
							}
							else
/*
							if( $ind !== "w" )
							{
								$line .= "  ";
							}
							else
*/
							{
								//Footer WORDS don't have bars and therefore no RESTS
							}
			
						}
						if( $ind !== "W" )
						{
							$line .= " | ";
						}
						else
						{
							//Footer WORDS don't have bar lines
						}
					}
					if( strcasecmp( $ind, "w" ) == 0 )
					{
						$this->body .= $linestart . $line . " \n\r";
					}
					else
					{
						$this->body .= $linestart . $line . " $ \n\r";
					}
					unset( $line );
				}
			}
		}
			foreach( $this->words_bottom_arr as $line )
			{
				$this->var_dump( __FUNCTION__  . ":" . __LINE__ );
				$this->var_dump( "Adding WordsLine to body\n\r" );
				$this->body .= "W: " . $line . "\n\r";
			}
	}
	function print_tune()
	{
		echo $this->complete_tune;
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
        public function process_bars( /*array*/ $bars )
        {
                $this->var_dump( get_class() . "::" . __METHOD__ . "::" . __LINE__ );
                $this->var_dump( $bars, PEAR_LOG_DEBUG );
                $cannt_arr = array();
                foreach( $bars as $bar )
                {
                        $this->add_bar( $bar );
                        $this->var_dump( get_class() . "::" . __METHOD__ . "::" . __LINE__, PEAR_LOG_DEBUG );
                        $tokens = $this->tokenizer( $bar, true );       //array without timing
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
                }
        }
	function add_bar( $bar )
	{
		$this->var_dump( get_class() . "::" . __METHOD__ . ":: $bar", PEAR_LOG_DEBUG );
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
		return $this->$fn( $bar );
	}

}

