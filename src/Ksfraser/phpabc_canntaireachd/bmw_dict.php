<?php

/**//**************************************************************
 * I have built an array of items from the DOCs from bmw to try
 * and convert into ABC.  This should save a bunch of typesetting tim.
 *
 * In addition, I want to be able to add Cainnteraichd to each of the tunes
 * as well as the ABC notes below for students who don't read music lines well.
 *
 * Being able to convert cainnteraichd into ABC would be a bonus.
 *
 * Last possibility would be the conversion of ABC into bmw but that isn't 
 * a current goal for me as I don't intend to use BMW.  MuseScore OTOH...
 * * ***************************************************************/

$arr[''][] = '';
//BMW to abc
$bmw[''] = '';
global $bmw_header;
//$bmw_header = array();
$bmw_header[''] = '';
/*
*/
$bmw['&']['abc'] = '[K: clef=treble]';
$bmw_header['TuneTempo,'] = 'Q:';
$bmw_header['Bagpipe Reader:'] = 'X: ';
$bmw_header['Bagpipe Music Writer Gold:'] = 'X: ';
$bmw_header['GracenoteDurations,'] = '% GracenoteDurations, ';
$bmw_header['MIDINoteMappings,'] = '% MIDINoteMappings, ';
$bmw_header['TuneFormat,'] = '% TuneFormat, ';
$bmw_header['FontSizes'] = '% FontSizes ';
$bmw_header['FrequencyMappings,'] = '% FrequencyMappings,';
$bmw_header['InstrumentMappings,'] = '% InstrumentMappings,';
$bmw_header['sharpf sharpc'] = '[K:Amix]';
$bmw_header[' sharpf sharpc '] = '[K:Amix]';
$bmw_header['sharpc'] = '[K:Dmix]';
$bmw_header['Part 1'] = '';
$bmw_header['Part 2'] = '';
$bmw_header['Part 3'] = '';
$bmw_header['Part 4'] = '';
$bmw_header['"2-4 March"'] = "R: March \n\r" . 'M:2/4' . "\n\r% ";
$bmw["I!''"]['abc'] = '[|:';
$bmw["''!I"]['abc'] = ':|]';
$bmw['I!']['abc'] = '[|';
$bmw['!']['abc'] = '|';
$bmw["'t"]['abc'] = "| $";
$bmw["''I"]['abc'] = ':|]';
$bmw['2_4']['abc'] = '[M:2/4]';
$bmw['3_4']['abc'] = '[M:3/4]';
$bmw['4_4']['abc'] = '[M:4/4]';
$bmw['5_4']['abc'] = '[M:5/4]';
$bmw['6_4']['abc'] = '[M:6/4]';
$bmw['7_4']['abc'] = '[M:7/4]';
$bmw['2_8']['abc'] = '[M:2/8]';
$bmw['3_8']['abc'] = '[M:3/8]';
$bmw['4_8']['abc'] = '[M:4/8]';
$bmw['5_8']['abc'] = '[M:5/8]';
$bmw['6_8']['abc'] = '[M:6/8]';
$bmw['7_8']['abc'] = '[M:7/8]';
$bmw['8_8']['abc'] = '[M:8/8]';
$bmw['9_8']['abc'] = '[M:9/8]';
$bmw['10_8']['abc'] = '[M:10/8]';
$bmw['11_8']['abc'] = '[M:11/8]';
$bmw['12_8']['abc'] = '[M:12/8]';
$bmw['15_8']['abc'] = '[M:15/8]';
$bmw['18_8']['abc'] = '[M:18/8]';
$bmw['21_8']['abc'] = '[M:21/8]';
$bmw['2_2']['abc'] = '[M:2/2]';
$bmw['C_']['abc'] = '[M:2/2]';
$bmw['C']['abc'] = '[M:4/4]';
$bmw['flatc']['abc'] = '';
$bmw['naturalc']['abc'] = '';
$bmw['segno']['abc'] = 'segno';
$bmw['delsegno']['abc'] = 'delsegno';
$bmw['si']['abc'] = '[S';	//Singling 
$bmw['do']['abc'] = '[D';	//Doubling
$bmw['bis']['abc'] = '[bis';	//Play this chunk twice.	Would a repeat sign be better?
$bmw['bis_']['abc'] = ']';	//End of chunk.
$bmw['dacapoalfine']['abc'] = '!D.C al fine!';	//Da capo al fine
$bmw['fine']['abc'] = '!fine!';
$bmw['coda']['abc'] = '!coda!';
$bmw['dacapoalcoda']['abc'] = '!D.C al coda!';	
$bmw['codasection']['abc'] = '!coda!';
//Assuming L:1/16
$bmw['LG_1']['abc'] = 'G16';
$bmw['LG_2']['abc'] = 'G8';
$bmw['LG_4']['abc'] = 'G4';
$bmw['LG_8']['abc'] = 'G2';
$bmw['LGr_8']['abc'] = 'G2';
$bmw['LGl_8']['abc'] = 'G2';
$bmw['LG_16']['abc'] = 'G';
$bmw['LGr_16']['abc'] = 'G';
$bmw['LGl_16']['abc'] = 'G';
$bmw['LG_32']['abc'] = 'G/';
$bmw['LGr_32']['abc'] = 'G/';
$bmw['LGl_32']['abc'] = 'G/';

$bmw['LA_1']['abc'] = 'A16';
$bmw['LA_2']['abc'] = 'A8';
$bmw['LA_4']['abc'] = 'A4';
$bmw['LA_8']['abc'] = 'A2';
$bmw['LAr_8']['abc'] = 'A2';
$bmw['LAl_8']['abc'] = 'A2';
$bmw['LA_16']['abc'] = 'A';
$bmw['LAr_16']['abc'] = 'A';
$bmw['LAl_16']['abc'] = 'A';
$bmw['LA_32']['abc'] = 'A/';
$bmw['LAr_32']['abc'] = 'A/';
$bmw['LAl_32']['abc'] = 'A/';

$bmw['B_1']['abc'] = 'B16';
$bmw['B_2']['abc'] = 'B8';
$bmw['B_4']['abc'] = 'B4';
$bmw['B_8']['abc'] = 'B2';
$bmw['Br_8']['abc'] = 'B2';
$bmw['Bl_8']['abc'] = 'B2';
$bmw['B_16']['abc'] = 'B';
$bmw['Br_16']['abc'] = 'B';
$bmw['Bl_16']['abc'] = 'B';
$bmw['B_32']['abc'] = 'B/';
$bmw['Br_32']['abc'] = 'B/';
$bmw['Bl_32']['abc'] = 'B/';

$bmw['C_1']['abc'] = 'c16';
$bmw['C_2']['abc'] = 'c8';
$bmw['C_4']['abc'] = 'c4';
$bmw['C_8']['abc'] = 'c2';
$bmw['Cr_8']['abc'] = 'c2';
$bmw['Cl_8']['abc'] = 'c2';
$bmw['C_16']['abc'] = 'c';
$bmw['Cr_16']['abc'] = 'c';
$bmw['Cl_16']['abc'] = 'c';
$bmw['C_32']['abc'] = 'c/';
$bmw['Cr_32']['abc'] = 'c/';
$bmw['Cl_32']['abc'] = 'c/';

$bmw['D_1']['abc'] = 'd16';
$bmw['D_2']['abc'] = 'd8';
$bmw['D_4']['abc'] = 'd4';
$bmw['D_8']['abc'] = 'd2';
$bmw['Dr_8']['abc'] = 'd2';
$bmw['Dl_8']['abc'] = 'd2';
$bmw['D_16']['abc'] = 'd';
$bmw['Dr_16']['abc'] = 'd';
$bmw['Dl_16']['abc'] = 'd';
$bmw['D_32']['abc'] = 'd/';
$bmw['Dr_32']['abc'] = 'd/';
$bmw['Dl_32']['abc'] = 'd/';

$bmw['E_1']['abc'] = 'e16';
$bmw['E_2']['abc'] = 'e8';
$bmw['E_4']['abc'] = 'e4';
$bmw['E_8']['abc'] = 'e2';
$bmw['Er_8']['abc'] = 'e2';
$bmw['El_8']['abc'] = 'e2';
$bmw['E_16']['abc'] = 'e';
$bmw['Er_16']['abc'] = 'e';
$bmw['El_16']['abc'] = 'e';
$bmw['E_32']['abc'] = 'e/';
$bmw['Er_32']['abc'] = 'e/';
$bmw['El_32']['abc'] = 'e/';

$bmw['F_1']['abc'] = 'f16';
$bmw['F_2']['abc'] = 'f8';
$bmw['F_4']['abc'] = 'f4';
$bmw['F_8']['abc'] = 'f2';
$bmw['Fr_8']['abc'] = 'f2';
$bmw['Fl_8']['abc'] = 'f2';
$bmw['F_16']['abc'] = 'f';
$bmw['Fr_16']['abc'] = 'f';
$bmw['Fl_16']['abc'] = 'f';
$bmw['F_32']['abc'] = 'f/';
$bmw['Fr_32']['abc'] = 'f/';
$bmw['Fl_32']['abc'] = 'f/';

$bmw['HG_1']['abc'] = 'g16';
$bmw['HG_2']['abc'] = 'g8';
$bmw['HG_4']['abc'] = 'g4';
$bmw['HG_8']['abc'] = 'g2';
$bmw['HGr_8']['abc'] = 'g2';
$bmw['HGl_8']['abc'] = 'g2';
$bmw['HG_16']['abc'] = 'g';
$bmw['HGr_16']['abc'] = 'g';
$bmw['HGl_16']['abc'] = 'g';
$bmw['HG_32']['abc'] = 'g/';
$bmw['HGr_32']['abc'] = 'g/';
$bmw['HGl_32']['abc'] = 'g/';

$bmw['HA_1']['abc'] = 'a16';
$bmw['HA_2']['abc'] = 'a8';
$bmw['HA_4']['abc'] = 'a4';
$bmw['HA_8']['abc'] = 'a2';
$bmw['HAr_8']['abc'] = 'a2';
$bmw['HAl_8']['abc'] = 'a2';
$bmw['HA_16']['abc'] = 'a';
$bmw['HAr_16']['abc'] = 'a';
$bmw['HAl_16']['abc'] = 'a';
$bmw['HA_32']['abc'] = 'a/';
$bmw['HAr_32']['abc'] = 'a/';
$bmw['HAl_32']['abc'] = 'a/';
$bmw['REST_1']['abc'] = 'z16';
$bmw['REST_2']['abc'] = 'z8';
$bmw['REST_4']['abc'] = 'z4';
$bmw['REST_8']['abc'] = 'z2';
$bmw['REST_16']['abc'] = 'z1';
$bmw['REST_32']['abc'] = 'z/';

$bmw["''lg"]['abc'] = 'G>>';
$bmw["''la"]['abc'] = 'A>>';
$bmw["''b"]['abc'] = 'B>>';
$bmw["''c"]['abc'] = 'c>>';
$bmw["''d"]['abc'] = 'd>>';
$bmw["''e"]['abc'] = 'e>>';
$bmw["''f"]['abc'] = 'f>>';
$bmw["''hg"]['abc'] = 'g>>';
$bmw["''ha"]['abc'] = 'a>>';

$bmw["'lg"]['abc'] = 'G>';
$bmw["'la"]['abc'] = 'A>';
$bmw["'b"]['abc'] = 'B>';
$bmw["'c"]['abc'] = 'c>';
$bmw["'d"]['abc'] = 'd>';
$bmw["'e"]['abc'] = 'e>';
$bmw["'f"]['abc'] = 'f>';
$bmw["'hg"]['abc'] = 'g>';
$bmw["'ha"]['abc'] = 'a>';

$bmw["fermatlg"]['abc'] = '';
$bmw["fermatla"]['abc'] = '';
$bmw["fermatb"]['abc'] = '';
$bmw["fermatc"]['abc'] = '';
$bmw["fermatd"]['abc'] = '';
$bmw["fermate"]['abc'] = '';
$bmw["fermatf"]['abc'] = '';
$bmw["fermathg"]['abc'] = '';
$bmw["fermatha"]['abc'] = '';

$bmw[" ag "]['abc'] = '{A}';
$bmw[" bg "]['abc'] = '{B}';
$bmw[" cg "]['abc'] = '{c}';
$bmw[" dg "]['abc'] = '{d}';
$bmw[" eg "]['abc'] = '{e}';
$bmw[" fg "]['abc'] = '{f}';
$bmw[" gg "]['abc'] = '{g}';
$bmw["gg"]['abc'] = '{g}';
$bmw[" tg "]['abc'] = '{a}';

$bmw["dblg"]['abc'] = '{gGd}';
$bmw["dbla"]['abc'] = '{gAd}';
$bmw["dbb"]['abc'] = '{gBd}';
$bmw["dbc"]['abc'] = '{gcd}';
$bmw["dbd"]['abc'] = '{gde}';
$bmw["dbe"]['abc'] = '{gef}';
$bmw["dbf"]['abc'] = '{gfg}';
$bmw["dbhg"]['abc'] = '{gf}';
$bmw["dbha"]['abc'] = '{ag}';

$bmw["tdblg"]['abc'] = '{aGd}';
$bmw["tdbla"]['abc'] = '{aAd}';
$bmw["tdbb"]['abc'] = '{aBd}';
$bmw["tdbc"]['abc'] = '{acd}';
$bmw["tdbd"]['abc'] = '{ade}';
$bmw["tdbe"]['abc'] = '{aef}';
$bmw["tdbf"]['abc'] = '{afg}';

$bmw["hdblg"]['abc'] = '{Gd}';
$bmw["hdbla"]['abc'] = '{Ad}';
$bmw["hdbb"]['abc'] = '{Bd}';
$bmw["hdbc"]['abc'] = '{cd}';
$bmw["hdbd"]['abc'] = '{de}';
$bmw["hdbe"]['abc'] = '{ef}';
$bmw["hdbf"]['abc'] = '{fg}';

$bmw["strlg"]['abc'] = '{G}';
$bmw["strla"]['abc'] = '{A}';
$bmw["strb"]['abc'] = '{B}';
$bmw["strc"]['abc'] = '{c}';
$bmw["strd"]['abc'] = '{d}';
$bmw["stre"]['abc'] = '{e}';
$bmw["strf"]['abc'] = '{f}';
$bmw["strg"]['abc'] = '{g}';


$bmw["gstla"]['abc'] = '{gAG}';
$bmw["gstb"]['abc'] = '{gBG}';
$bmw["gstc"]['abc'] = '{gcG}';
$bmw["gstd"]['abc'] = '{gdG}';
$bmw["lgstd"]['abc'] = '{gdc}';
$bmw["gste"]['abc'] = '{geA}';
$bmw["gstf"]['abc'] = '{gfe}';
$bmw["gstg"]['abc'] = '{g}';

$bmw["tstla"]['abc'] = '{aAG}';
$bmw["tstb"]['abc'] = '{aBG}';
$bmw["tstc"]['abc'] = '{acG}';
$bmw["tstd"]['abc'] = '{adG}';
$bmw["ltstd"]['abc'] = '{adc}';
$bmw["tste"]['abc'] = '{aeA}';
$bmw["tstf"]['abc'] = '{afe}';
$bmw["tsthg"]['abc'] = '{agf}';

$bmw["hstla"]['abc'] = '{AG}';
$bmw["hstb"]['abc'] = '{BG}';
$bmw["hstc"]['abc'] = '{cG}';
$bmw["hstd"]['abc'] = '{dG}';
$bmw["lhstd"]['abc'] = '{dc}';
$bmw["hste"]['abc'] = '{eA}';
$bmw["hstf"]['abc'] = '{fe}';
$bmw["hsthg"]['abc'] = '{gf}';

$bmw["grp"]['abc'] = '{GdG}';
$bmw["hgrp"]['abc'] = '{dG}';
$bmw["grpb"]['abc'] = '{GBG}';
$bmw[" tar"]['abc'] = '{GdGe}';
$bmw["\ttar"]['abc'] = '{GdGe}';
$bmw["tarb"]['abc'] = '{GBGe}';
$bmw["htar"]['abc'] = '{dGe}';
$bmw["bubly"]['abc'] = '{GdGcG}';
$bmw["hbubly"]['abc'] = '{dGcG}';

$bmw["ggrpla"]['abc'] = '{gAGdG}';
$bmw["ggrpb"]['abc'] = '{gBGdG}';
$bmw["ggrpc"]['abc'] = '{gcGdG}';
$bmw["ggrpd"]['abc'] = '{gdGdG}';
$bmw["ggrpdb"]['abc'] = '{gdGBG}';
$bmw["ggrpe"]['abc'] = '{geGdG}';
$bmw["ggrpf"]['abc'] = '{gfGdG}';

$bmw["tgrpla"]['abc'] = '{aAGdG}';
$bmw["tgrpb"]['abc'] = '{aBGdG}';
$bmw["tgrpc"]['abc'] = '{acGdG}';
$bmw["tgrpd"]['abc'] = '{adGdG}';
$bmw["tgrpdb"]['abc'] = '{adGBG}';
$bmw["tgrpe"]['abc'] = '{aeGdG}';
$bmw["tgrpf"]['abc'] = '{afGdG}';

$bmw["hgrpla"]['abc'] = '{AGdG}';
$bmw["hgrpb"]['abc'] = '{BGdG}';
$bmw["hgrpc"]['abc'] = '{cGdG}';
$bmw["hgrpd"]['abc'] = '{dGdG}';
$bmw["hgrpdb"]['abc'] = '{dGBG}';
$bmw["hgrpe"]['abc'] = '{eGdG}';
$bmw["hgrpf"]['abc'] = '{fGdG}';

$bmw["brl"]['abc'] = '{GAG}';
$bmw["abr"]['abc'] = '{AGAG}';
$bmw["gbr"]['abc'] = '{gAGAG}';
$bmw["tbr"]['abc'] = '{aAGAG}';

$bmw["thrd"]['abc'] = '{Gdc}';
$bmw["hvthrd"]['abc'] = '{GdGc}';
$bmw["hthrd"]['abc'] = '{dc}';
$bmw["hhvthrd"]['abc'] = '{dGc}';

$bmw["pella"]['abc'] = '{gAeAG}';
$bmw["pelb"]['abc'] = '{gBeBG}';
$bmw["pelc"]['abc'] = '{gcecG}';
$bmw["peld"]['abc'] = '{gdedG}';
$bmw["lpeld"]['abc'] = '{gdedc}';
$bmw["pele"]['abc'] = '{gefeA}';
$bmw["pelf"]['abc'] = '{gfgfe}';

$bmw["tpella"]['abc'] = '{aAeAG}';
$bmw["tpelb"]['abc'] = '{aBeBG}';
$bmw["tpelc"]['abc'] = '{acecG}';
$bmw["tpeld"]['abc'] = '{adedG}';
$bmw["ltpeld"]['abc'] = '{adedc}';
$bmw["tpele"]['abc'] = '{aefeA}';
$bmw["tpelf"]['abc'] = '{afgfe}';

$bmw["hpella"]['abc'] = '{AeAG}';
$bmw["hpelb"]['abc'] = '{BeBG}';
$bmw["hpelc"]['abc'] = '{cecG}';
$bmw["hpeld"]['abc'] = '{dedG}';
$bmw["ltpeld"]['abc'] = '{dedc}';
$bmw["hpele"]['abc'] = '{efeA}';
$bmw["hpelf"]['abc'] = '{fgfe}';
 

$bmw["st2la"]['abc'] = '{GAG}';
$bmw["st2b"]['abc'] = '{GBG}';
$bmw["st2c"]['abc'] = '{GcG}';
$bmw["st2d"]['abc'] = '{GdG}';
$bmw["lst2d"]['abc'] = '{GcG}';
$bmw["st2e"]['abc'] = '{AeA}';
$bmw["st2f"]['abc'] = '{efe}';
$bmw["st2hg"]['abc'] = '{fgf}';
$bmw["st2ha"]['abc'] = '{gag}';

$bmw["gst2la"]['abc'] = '{gAGAG}';
$bmw["gst2b"]['abc'] = '{gBGBG}';
$bmw["gst2c"]['abc'] = '{gcGcG}';
$bmw["gst2d"]['abc'] = '{gdGdG}';
$bmw["lgst2d"]['abc'] = '{gdcdc}';
$bmw["gst2e"]['abc'] = '{geAeA}';
$bmw["gst2f"]['abc'] = '{gfefe}';

$bmw["tst2la"]['abc'] = '{aAGAG}';
$bmw["tst2b"]['abc'] = '{aBGBG}';
$bmw["tst2c"]['abc'] = '{acGcG}';
$bmw["tst2d"]['abc'] = '{adGdG}';
$bmw["ltst2d"]['abc'] = '{adcdc}';
$bmw["tst2e"]['abc'] = '{aeAeA}';
$bmw["tst2f"]['abc'] = '{afefe}';
$bmw["tst2hg"]['abc'] = '{agfgf}';

$bmw["hst2la"]['abc'] = '{AGAG}';
$bmw["hst2b"]['abc'] = '{BGBG}';
$bmw["hst2c"]['abc'] = '{cGcG}';
$bmw["hst2d"]['abc'] = '{dGdG}';
$bmw["lhst2d"]['abc'] = '{dcdc}';
$bmw["hst2e"]['abc'] = '{eAeA}';
$bmw["hst2f"]['abc'] = '{fefe}';
$bmw["hst2hg"]['abc'] = '{gfgf}';
$bmw["hst2ha"]['abc'] = '{agag}';

$bmw["st3la"]['abc'] = '{AGGAG}';
$bmw["st3b"]['abc'] = '{GBGBG}';
$bmw["st3c"]['abc'] = '{GcGcG}';
$bmw["st3d"]['abc'] = '{GdGdG}';
$bmw["lst3d"]['abc'] = '{GcGcG}';
$bmw["st3e"]['abc'] = '{AeAeA}';
$bmw["st3f"]['abc'] = '{efefe}';
$bmw["st3hg"]['abc'] = '{fgfgf}';
$bmw["st3ha"]['abc'] = '{gagag}';

$bmw["gst3la"]['abc'] = '{gAGAGAG}';
$bmw["gst3b"]['abc'] = '{gBGBGBG}';
$bmw["gst3c"]['abc'] = '{gcGcGcG}';
$bmw["gst3d"]['abc'] = '{gdGdGdG}';
$bmw["lgst3d"]['abc'] = '{gdcdcdc}';
$bmw["gst3e"]['abc'] = '{geAeAeA}';
$bmw["gst3f"]['abc'] = '{gfefefe}';

$bmw["tst3la"]['abc'] = '{aAGAGAG}';
$bmw["tst3b"]['abc'] = '{aBGBGBG}';
$bmw["tst3c"]['abc'] = '{acGcGcG}';
$bmw["tst3d"]['abc'] = '{adGdGdG}';
$bmw["ltst3d"]['abc'] = '{adcdcdc}';
$bmw["tst3e"]['abc'] = '{aeAeAeA}';
$bmw["tst3f"]['abc'] = '{afefefe}';
$bmw["tst3hg"]['abc'] = '{agfgfgf}';

$bmw["hst3la"]['abc'] = '{AGAGAG}';
$bmw["hst3b"]['abc'] = '{BGBGBG}';
$bmw["hst3c"]['abc'] = '{cGcGcG}';
$bmw["hst3d"]['abc'] = '{dGdGdG}';
$bmw["lhst3d"]['abc'] = '{dcdcdc}';
$bmw["hst3e"]['abc'] = '{eAeAeA}';
$bmw["hst3f"]['abc'] = '{fefefe}';
$bmw["hst3hg"]['abc'] = '{gfgfgf}';
$bmw["hst3ha"]['abc'] = '{agagag}';

$bmw[" dlg"]['abc'] = '{dG}';
$bmw[" dla"]['abc'] = '{dA}';
$bmw[" db"]['abc'] = '{dB}';
$bmw[" dc"]['abc'] = '{dc}';

$bmw[" elg"]['abc'] = '{eG}';
$bmw[" ela"]['abc'] = '{eA}';
$bmw[" eb"]['abc'] = '{eB}';
$bmw[" ec"]['abc'] = '{ec}';
$bmw[" ed"]['abc'] = '{ed}';

$bmw[" flg"]['abc'] = '{fG}';
$bmw[" fla"]['abc'] = '{fA}';
$bmw[" fb"]['abc'] = '{fB}';
$bmw[" fc"]['abc'] = '{fc}';
$bmw[" fd"]['abc'] = '{fd}';
$bmw[" fe"]['abc'] = '{fe}';


$bmw[" glg "]['abc'] = '{gG}';
$bmw[" gla "]['abc'] = '{gA}';
$bmw[" gb "]['abc'] = '{gB}';
$bmw[" gc "]['abc'] = '{gc}';
$bmw[" gd "]['abc'] = '{gd}';
$bmw[" ge "]['abc'] = '{ge}';
$bmw[" gf "]['abc'] = '{gf}';

$bmw[" tlg"]['abc'] = '{aG}';
$bmw[" tla"]['abc'] = '{aA}';
$bmw[" tb"]['abc'] = '{aB}';
$bmw[" tc"]['abc'] = '{ac}';
$bmw[" td"]['abc'] = '{ad}';
$bmw[" te"]['abc'] = '{ae}';
$bmw[" tf"]['abc'] = '{af}';
$bmw[" thg"]['abc'] = '{ag}';

$bmw["^tla"]['abc'] = '-';
$bmw["^tla"]['abc'] = '-';
$bmw["^tb"]['abc'] = '-';
$bmw["^tc"]['abc'] = '-';
$bmw["^td"]['abc'] = '-';
$bmw["^te"]['abc'] = '-';
$bmw["^tf"]['abc'] = '-';
$bmw["^thg"]['abc'] = '-';
$bmw["^tha"]['abc'] = '-';

$bmw["^2s"]['abc'] = '(2:3'; //duplet start
$bmw["^2e"]['abc'] = ')'; //duplet start
$bmw["^3s"]['abc'] = '(3:2'; //
$bmw["^3e"]['abc'] = ')'; //
$bmw["^43s"]['abc'] = '(4:3'; //
$bmw["^43e"]['abc'] = ')'; //
$bmw["^46s"]['abc'] = '(4:6'; //
$bmw["^46e"]['abc'] = ')'; //
$bmw["^53s"]['abc'] = '(5:3'; //
$bmw["^53e"]['abc'] = ')'; //
$bmw["^54s"]['abc'] = '(5:4'; //
$bmw["^54e"]['abc'] = ')'; //
$bmw["^64s"]['abc'] = '(6:4'; //
$bmw["^64e"]['abc'] = ')'; //
$bmw["^74s"]['abc'] = '(7:4'; //
$bmw["^74e"]['abc'] = ')'; //
$bmw["^76s"]['abc'] = '(7:6'; //
$bmw["^76e"]['abc'] = ')'; //

$bmw["'1"]['abc'] = '[1'; //
$bmw["'2"]['abc'] = '[2'; //
$bmw["_'"]['abc'] = ']'; //End of ending
$bmw["'22"]['abc'] = '[2 "2nd part"'; //

//Cadences
$bmw["cadged"]['abc'] = '"^cadence"{ged}';  //?? {ge4d}
$bmw["cadge"]['abc'] = '"^cadence"{ge}';  
$bmw["caded"]['abc'] = '"^cadence"{ed}';  
$bmw["cade"]['abc'] = '"^cadence"{e}';  
$bmw["cadae"]['abc'] = '"^cadence"{ae}';  

$bmw["fcadged"]['abc'] = '"^cadence"{ged}';  //?? {ge4d} with fermenta over e
$bmw["fcadge"]['abc'] = '"^cadence"{ge}';  
$bmw["fcaded"]['abc'] = '"^cadence"{ed}';  
$bmw["fcade"]['abc'] = '"^cadence"{e}';  
$bmw["fcadae"]['abc'] = '"^cadence"{ae}';  

$bmw["embari"]['abc'] = 'G{eAfA}e';  
$bmw["endari"]['abc'] = 'A{fege}f';  
$bmw["chedari"]['abc'] = 'f{geae}g';  

$bmw["pembari"]['abc'] = 'G{eAfA}e';  //e with tilde over it
$bmw["pendari"]['abc'] = 'A{fege}f';  //f with tilde over it
$bmw["pchedari"]['abc'] = 'f{geae}g';  //g with tilde over it

$bmw["dili"]['abc'] = 'A{ag}a';  
$bmw["tra"]['abc'] = 'A{G2dc}d';  
$bmw["htra"]['abc'] = 'G{dc}d';  
$bmw["tra8"]['abc'] = 'd{G2dc}d';  

$bmw["pdili"]['abc'] = 'A{ag}a';  //with "tr" over the note instead of gracenotes
$bmw["ptra"]['abc'] = 'A{G2dc}d';  
$bmw["phtra"]['abc'] = 'G{dc}d';  
$bmw["ptra8"]['abc'] = 'd{G2dc}d';  

$bmw["gedre"]['abc'] = 'F{geAfA}e';  
$bmw["gdare"]['abc'] = 'e{gfege}f';  

$bmw["tedre"]['abc'] = 'g{aeAfA}e';  
$bmw["tdare"]['abc'] = 'g{afege}f';  
$bmw["tchechere"]['abc'] = 'e{ageae}g';  

$bmw["dre"]['abc'] = 'e{AfA}e';  
$bmw["hedale"]['abc'] = 'f{ege}f';  
$bmw["hchedere"]['abc'] = 'g{eae}g';  

$bmw["grp"]['abc'] = '{GdG}';  
$bmw["deda"]['abc'] = 'd{GeG}d';  
$bmw["pgrp"]['abc'] = '{GdG}';  //"tr" above note instead of graces

$bmw["enbain"]['abc'] = 'B{AGdG}A';  
$bmw["otro"]['abc'] = 'C{BGdG}B';  
$bmw["odro"]['abc'] = 'd{cGdG}c';  
$bmw["adeda"]['abc'] = 'e{dGdG}d';  

$bmw["penbain"]['abc'] = 'B{AGdG}A';  //with the trill tilde above instead of graces
$bmw["potro"]['abc'] = 'C{BGdG}B';  
$bmw["podro"]['abc'] = 'd{cGdG}c';  
$bmw["padeda"]['abc'] = 'e{dGdG}d';  

$bmw["genbain"]['abc'] = '{gAGdG}A';  
$bmw["gotro"]['abc'] = '{gBGdG}B';  
$bmw["godro"]['abc'] = '{gcGdG}c';  
$bmw["gadeda"]['abc'] = '{gdGdG}d';  

$bmw["tenbain"]['abc'] = '{aAGdG}A';  
$bmw["totro"]['abc'] = '{aBGdG}B';  
$bmw["todro"]['abc'] = '{acGdG}c';  
$bmw["tadeda"]['abc'] = '{adGdG}d';  

//echo beats (strikes)
$bmw["echolg"]['abc'] = '{G}';  
$bmw["echola"]['abc'] = '{A}';  
$bmw["echob"]['abc'] = '{B}';  
$bmw["echoc"]['abc'] = '{c}';  
$bmw["echod"]['abc'] = '{d}';  
$bmw["echoe"]['abc'] = '{e}';  
$bmw["echof"]['abc'] = '{f}';  
$bmw["echohg"]['abc'] = '{g}';  
$bmw["echoha"]['abc'] = '{a}';  

$bmw["darodo"]['abc'] = '{GdGcG}B';  
$bmw["darodo16"]['abc'] = '{G2dGcG2}B';  
$bmw["hdarodo"]['abc'] = '{dGcG}B';  

$bmw["pdarodo"]['abc'] = '{GdGcG}B';  //infinity sign over top instead of grace
$bmw["pdarodo16"]['abc'] = '{G2dGcG2}B';  
$bmw["phdarodo"]['abc'] = '{dGcG}B';  


$bmw["hiharin"]['abc'] = 'e{dAGAG}A';  
$bmw["phiharin"]['abc'] = 'e{dAGAG}A';  //tilde
$bmw["rodin"]['abc'] = 'c{GBG}A';  
$bmw["chelalho"]['abc'] = 'e{f2de}dec2';  
$bmw["din"]['abc'] = 'B{G2}A';  
//Piobrach Lemluaths
$bmw["lem"]['abc'] = '{GdG}';
$bmw["lemb"]['abc'] = '{GBG}';
$bmw["hlemla"]['abc'] = '{g}G{dA}e';
$bmw["hlemlg"]['abc'] = '{g}G{dG}e';
$bmw["LA_4 lem"]['abc'] = '{g}A2>{GdG}e';
$bmw["LA_4 pl"]['abc'] = '{g}A2>{GdG}e'; //"L" below the note instead of grace.
$bmw["pl"]['abc'] = '"vL"';
$bmw["plb"]['abc'] = '{GBG}d'; //L below
$bmw["phlla"]['abc'] = '{g}G{dA}e';

//Piob
$bmw[" tar"]['abc'] = '{g}A2>{GdGe}A'; //
$bmw[" tarb"]['abc'] = '{g}d2>{GBGe}A'; //
$bmw["htarla"]['abc'] = '{g}G2>{dAe}A'; //
$bmw["htarlg"]['abc'] = '{g}G2>{dGe}A'; //
$bmw["LA_4 pl"]['abc'] = '{g}A2>{GdGe}A'; //"T" below the note instead of grace.
$bmw["tarbrea"]['abc'] = '{g}G2>{dGe}G'; //
$bmw["tarbbrea"]['abc'] = '{g}B2>{GdGe}G'; //
$bmw["htarlabrea"]['abc'] = '{g}d2>{GBGe}G'; //
$bmw["tmb"]['abc'] = '{g}B<{GdGe}B'; // a mach
$bmw["tmb"]['abc'] = '{g}c<{GdGe}c'; // a mach
$bmw["tmd"]['abc'] = '{g}B<{G2dc}d{e}d'; // a mach
//Triplings
$bmw["triplg"]['abc'] = '(3:2{g}G{d}G{e}G)'; // fosgailte
$bmw["tripla"]['abc'] = '(3:2{g}A{d}A{e}A)'; // fosgailte
$bmw["tripb"]['abc'] = '(3:2{g}B{d}B{e}B)'; // fosgailte
$bmw["tripc"]['abc'] = '(3:2{g}c{d}c{e}c)'; // fosgailte

$bmw["ttriplg"]['abc'] = '(3:2{a}G{d}G{e}G)'; // fosgailte
$bmw["ttripla"]['abc'] = '(3:2{a}A{d}A{e}A)'; // fosgailte
$bmw["ttripb"]['abc'] = '(3:2{a}B{d}B{e}B)'; // fosgailte
$bmw["ttripc"]['abc'] = '(3:2{a}c{d}c{e}c)'; // fosgailte

$bmw["htriplg"]['abc'] = '(3:2G{d}G{e}G)'; // fosgailte
$bmw["htripla"]['abc'] = '(3:2A{d}A{e}A)'; // fosgailte
$bmw["htripb"]['abc'] = '(3:2B{d}B{e}B)'; // fosgailte
$bmw["htripc"]['abc'] = '(3:2c{d}c{e}c)'; // fosgailte

$bmw["crunl"]['abc'] = '>{GdGeAfA}e'; //
$bmw["crunlb"]['abc'] = '>{GBGeAfA}e'; //
$bmw["hcrunla"]['abc'] = '{g}G>{dAeAfA}e'; //
$bmw["hcrunlgla"]['abc'] = '{g}G>{dGeAfA}e'; //

$bmw["crunlbrea"]['abc'] = '{g}G>{dGeGfG}e'; //
$bmw["crunlbbrea"]['abc'] = '{g}B>{GBGeAfA}e'; //
$bmw["hcrunllabrea"]['abc'] = '{g}d>{GBGeGfG}e'; //
//Crunluath a Machs
$bmw["cmb"]['abc'] = '{gBGdG}B<{eBfB}e'; //
$bmw["cmc"]['abc'] = '{gcGdG}c<{ecfc}e'; //
$bmw["cmd"]['abc'] = '{g}B{Gdc}d{edfd}e'; //
//Crunluath Fosgailth a Machs
$bmw["edreb"]['abc'] = '{g}G2{d}B{edfd}e'; //
$bmw["edrec"]['abc'] = '{g}A2{d}c{ecfc}e'; //
$bmw["edred"]['abc'] = '{g}A2d{edfd}e'; //
//$bmw[\t]['abc'] = ' '; //beat separator

