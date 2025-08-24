<?php

/**//**************************************************************
 * I have built an array of items from the DOCs from bmw to try
 * and convert into ABC.  This should save a bunch of typesetting tim.
 *
 * In additon, I want to be able to add Cainnteraichd to each of the tunes
 * as well as the ABC notes below for students who don't read music lines well.
 *
 * Being able to convert cainnteraichd into ABC would be a bonus.
 *
 * Last possibility would be the converson of ABC into bmw but that isn't 
 * a current goal for me as I don't intend to use BMW.  MuseScore OTOH...
 * * ***************************************************************/

$arr[''][] = '';
//BMW to abc
$bmw[''] = '';
/*
*/
//Assuming L:1/16
$abc['G']['cannt'] = 'em';
$abc['A']['cannt'] = 'en';
$abc['B']['cannt'] = 'o';
$abc['c']['cannt'] = 'o';
$abc['d']['cannt'] = 'a';
$abc['e']['cannt'] = 'e';
$abc['e']['cannt'] = 'e';
$abc['f']['cannt'] = 've';
$abc['g']['cannt'] = 'di';
$abc['a']['cannt'] = 'I';
//G Gracenotes
$abc['{g}G']['cannt'] = 'hem';
$abc['{g}A']['cannt'] = 'hen';
$abc['e']['cannt'] = 'e ';
$abc['{g}B']['cannt'] = 'ho';
$abc['{g}c']['cannt'] = 'ho';
$abc['{g}d']['cannt'] = 'ha';
$abc['{g}e']['cannt'] = 'he';
$abc['{g}f']['cannt'] = 'che';
//D Gracenotes
$abc['{d}G']['cannt'] = 'dam';
$abc['{d}A']['cannt'] = 'dan';
$abc['{d}B']['cannt'] = 'to';
$abc['{d}c']['cannt'] = 'do';
//E Gracenotes
$abc['{e}G']['cannt'] = 'um';
$abc['{e}A']['cannt'] = 'un';
$abc['{e}B']['cannt'] = 'eo';
$abc['{e}c']['cannt'] = 'eo';
$abc['{e}d']['cannt'] = 'ea';
//A Gracenotes
$abc['{a}G']['cannt'] = 'him';
$abc['{a}A']['cannt'] = 'hin';
$abc['{a}B']['cannt'] = 'ho';
$abc['{a}c']['cannt'] = 'ho';
$abc['{a}d']['cannt'] = 'ha';
$abc['{a}e']['cannt'] = 'che';
$abc['{a}f']['cannt'] = 'he';
$abc['{a}g']['cannt'] = 'hi';
//Strikes
$abc['{G}A']['cannt'] = 'den';
$abc['{AG}A']['cannt'] = 'den';
$abc['{G}B']['cannt'] = 'do';
$abc['{BG}B']['cannt'] = 'do';
$abc['{G}c']['cannt'] = 'do';
$abc['{cG}c']['cannt'] = 'do';
$abc['{G}d']['cannt'] = 'emda';
$abc['{dG}d']['cannt'] = 'emda';
$abc['{c}d']['cannt'] = 'oda';
$abc['{dc}d']['cannt'] = 'adoa';
$abc['{A}e']['cannt'] = 'ende';
$abc['{eA}e']['cannt'] = 'ende';
$abc['{e}f']['cannt'] = 'eve';
$abc['{fe}f']['cannt'] = 'eve';
$abc['{f}g']['cannt'] = 'vedi';
$abc['{gf}g']['cannt'] = 'chedi';
$abc['{g}a']['cannt'] = 'li';		//strike
$abc['{ag}a']['cannt'] = 'dili';
$abc['{GAG}A']['cannt'] = 'rarin';	//Birl
$abc['{AGAG}A']['cannt'] = 'enrarin';
$abc['{gAGAG}A']['cannt'] = 'henrarin';
//$abc['{gAGAG}A']['cannt'] = 'hihararin';	//Pibroch
$abc['{aAGAG}A']['cannt'] = 'henrarin';
//Double Echo
$abc['{GBG}A']['cannt'] = 'roro';
$abc['{GcG}c']['cannt'] = 'roro';
$abc['{GdG}d']['cannt'] = 'rara';
$abc['{cdc}d']['cannt'] = 'rara';
$abc['{AeA}e']['cannt'] = 'rede';
$abc['{efe}f']['cannt'] = 'rere';
$abc['{fgf}g']['cannt'] = 'riri';
$abc['{gag}a']['cannt'] = 'riri';
$abc['{BGBG}']['cannt'] = 'hodo';
$abc['{gBGBG}B']['cannt'] = 'horodo';
$abc['{gBGBG}']['cannt'] = 'horodo';
$abc['{aBGBG}B']['cannt'] = 'horodo';
$abc['{aBGBG}']['cannt'] = 'horodo';
$abc['{cGcG}']['cannt'] = 'rodo';
$abc['{gcGcG}c']['cannt'] = 'horodo';
$abc['{gcGcG}']['cannt'] = 'horodo';
$abc['{acGcG}c']['cannt'] = 'horodo';
$abc['{acGcG}']['cannt'] = 'horodo';
$abc['{dGdG}']['cannt'] = 'remdem';
$abc['{gdGdG}d']['cannt'] = 'hemdemde';
$abc['{gdGdG}']['cannt'] = 'hemdemde';
$abc['{adGdG}d']['cannt'] = 'hemdemde';
$abc['{adGdG}']['cannt'] = 'hemdemde';
$abc['{dcdc}']['cannt'] = 'roda';
$abc['{gdcdc}d']['cannt'] = 'haroda';
$abc['{gdcdc}']['cannt'] = 'haroda';
$abc['{adcdc}d']['cannt'] = 'haroda';
$abc['{adcdc}']['cannt'] = 'haroda';
$abc['{eAeA}']['cannt'] = 'renden';
$abc['{geAeA}e']['cannt'] = 'hendende';
$abc['{geAeA}']['cannt'] = 'hendende';
$abc['{aeAeA}e']['cannt'] = 'hendende';
$abc['{aeAeA}']['cannt'] = 'hendende';
$abc['{fefe}']['cannt'] = 'veve';
$abc['{gfefe}f']['cannt'] = 'cheveve';
$abc['{gfefe}']['cannt'] = 'cheveve';
$abc['{afefe}f']['cannt'] = 'cheveve';
$abc['{afefe}']['cannt'] = 'cheveve';
$abc['{gfgf}g']['cannt'] = 'chididi';
$abc['{gfgf}']['cannt'] = 'chidi';
$abc['{agfgf}g']['cannt'] = 'chididi';
$abc['{agfgf}']['cannt'] = 'chididi';
$abc['{agag}']['cannt'] = 'dili';
//Doublings
$abc["{gGd}G"]['cannt'] = 'hemdem';
$abc["{gAd}A"]['cannt'] = 'hindin';
$abc["{gBd}B"]['cannt'] = 'hoto';
$abc["{gcd}c"]['cannt'] = 'hodo';
$abc["{gde}d"]['cannt'] = 'hada';
$abc["{gef}e"]['cannt'] = 'hede';	//chehe
$abc["{gfg}f"]['cannt'] = 'chede';  	//hehe
$abc["{afg}f"]['cannt'] = 'chede'; 
//Thumb Dbl
$abc["{aGd}G"]['cannt'] = 'hemdin';
$abc["{aAd}A"]['cannt'] = 'hindin';
$abc["{aBd}B"]['cannt'] = 'hodo';
$abc["{acd}c"]['cannt'] = 'hodo';
$abc["{ade}d"]['cannt'] = 'hoda';
$abc["{aef}e"]['cannt'] = 'dre';
$abc["{afg}f"]['cannt'] = 'dare';
//Half  Dbl
$abc["{Gd}G"]['cannt'] = 'hemdin';
$abc["{Ad}A"]['cannt'] = 'hindin';
$abc["{Bd}B"]['cannt'] = 'hodo';
$abc["{cd}c"]['cannt'] = 'hodo';
$abc["{de}d"]['cannt'] = 'hoda';
$abc["{ef}e"]['cannt'] = 'dre';
//$abc["{fg}f"]['cannt'] = 'dare';	//throw
$abc["{fg}f"]['cannt'] = 'vede';	//half doubling
//Throws
$abc["{Gdc}d"]['cannt'] = 'tra';	//Light
$abc["{dc}d"]['cannt'] = 'tra';		//Light from G
$abc["{GdGc}d"]['cannt'] = 'tra';	//Heavy
$abc["{dGc}d"]['cannt'] = 'tra';	//Heavy from G
$abc["{eAfA}e"]['cannt'] = 'dre';
$abc["{fe}f"]['cannt'] = 'dare';
$abc["{fege}f"]['cannt'] = 'dare';
$abc["{gf}g"]['cannt'] = 'vili';
$abc["{ag}a"]['cannt'] = 'dili';
//Grip
$abc["{GdG}G"]['cannt'] = 'drem';
$abc["{GdG}A"]['cannt'] = 'bain';
$abc["{GdG}B"]['cannt'] = 'tro';
$abc["{GdG}c"]['cannt'] = 'dro';
$abc["{GdG}d"]['cannt'] = 'dada';
$abc["{GdG}e"]['cannt'] = 'bare';
$abc["{GdG}f"]['cannt'] = 'barhe';
$abc["{GdG}g"]['cannt'] = 'barhi';
$abc["{GdG}a"]['cannt'] = 'barI';
$abc["{gGGdG}G"]['cannt'] = 'hemdrem';
$abc["{gAGdG}A"]['cannt'] = 'hinbain';
$abc["{gBGdG}B"]['cannt'] = 'hotro';
$abc["{gcGdG}c"]['cannt'] = 'hodro';
$abc["{gdGdG}d"]['cannt'] = 'hadada';
$abc["{geGdG}e"]['cannt'] = 'hebare';
$abc["{gfGdG}f"]['cannt'] = 'chebarhe';
$abc["{agGdG}g"]['cannt'] = 'hibarhi';
$abc["{aGdG}a"]['cannt'] = 'IbarI';
$abc["{aGGdG}G"]['cannt'] = 'hemdrem';
$abc["{aAGdG}A"]['cannt'] = 'hinbain';
$abc["{aBGdG}B"]['cannt'] = 'hotro';
$abc["{acGdG}c"]['cannt'] = 'hodro';
$abc["{adGdG}d"]['cannt'] = 'hadada';
$abc["{aeGdG}e"]['cannt'] = 'hebare';
$abc["{afGdG}f"]['cannt'] = 'chebarhe';
$abc["{agGdG}g"]['cannt'] = 'hibarhi';
$abc["{aGdG}a"]['cannt'] = 'IbarI';
//Taorluath
$abc["{GdGe}G"]['cannt'] = 'daridem';
$abc["{GdGe}A"]['cannt'] = 'dariden';
$abc["{GdGe}B"]['cannt'] = 'darido';
$abc["{GdGe}c"]['cannt'] = 'darido';
$abc["{GdGe}d"]['cannt'] = 'darida';
$abc["{GdGAe}c"]['cannt'] = 'darido';
$abc["{GBGe}G"]['cannt'] = 'daridem';
$abc["{GBGe}A"]['cannt'] = 'dariden';
$abc["{GBGe}B"]['cannt'] = 'darido';
$abc["{GBGe}c"]['cannt'] = 'darido';
$abc["{GBGe}d"]['cannt'] = 'darida';
$abc["{GBGAe}c"]['cannt'] = 'darido';
//Shakes
$abc["{gfe}f"]['cannt'] = 'chere';
$abc["{geA}e"]['cannt'] = 'chere';
$abc["{gdc}d"]['cannt'] = 'hara';
$abc["{gdG}d"]['cannt'] = 'hadema';
$abc["{gcG}c"]['cannt'] = 'hadoa';
$abc["{gBG}B"]['cannt'] = 'hodemo';
$abc["{gAG}A"]['cannt'] = 'hendema';
//Thumb Shakes
$abc["{agf}g"]['cannt'] = 'chere';
$abc["{afe}f"]['cannt'] = 'chere';
$abc["{aeA}e"]['cannt'] = 'chere';
$abc["{adc}d"]['cannt'] = 'hara';
$abc["{adG}d"]['cannt'] = 'hadema';
$abc["{acG}c"]['cannt'] = 'hadoa';
$abc["{aBG}B"]['cannt'] = 'hodemo';
$abc["{aAG}A"]['cannt'] = 'hendema';
$abc["{g}e2d{G}d4{Gdc}d6"]['cannt'] = 'hiharara';
$abc["{gfe}f{e}f"]['cannt'] = 'herere';
//Cadence
$abc["{g}e2{d}B4{G}A6"]['cannt'] = 'hodin';
$abc["{g}e3fd{e}dc6"]['cannt'] = 'chelalho';
//Bubbly
$abc["C4{GdGcG}B4"]['cannt'] = 'darodo';
$abc["{GdGcG}B"]['cannt'] = 'darodo';  
$abc["{G2dGcG2}B"]['cannt'] = 'darodo';  
$abc["{dGcG}B"]['cannt'] = 'rodo';  
$abc["{g}e4{dAGAG}A4"]['cannt'] = 'hiharin';
//Hornpipe Shakes aka Pelle aka Doubling Shake
$abc["{gAeAG}A"]['cannt'] = 'henendem';
$abc["{gBeBG}B"]['cannt'] = 'hododemo';
$abc["{gcecG}c"]['cannt'] = 'hododemo';
$abc["{gdedG}d"]['cannt'] = 'hadada';
$abc["{gdedc}d"]['cannt'] = 'hadala';
$abc["{gefeA}e"]['cannt'] = 'hedede';
$abc["{gfgfA}f"]['cannt'] = 'chehede';
$abc["{aAeAG}A"]['cannt'] = 'henendem';
$abc["{aBeBG}B"]['cannt'] = 'hododemo';
$abc["{acecG}c"]['cannt'] = 'hododemo';
$abc["{adedG}d"]['cannt'] = 'hadada';
$abc["{adedc}d"]['cannt'] = 'hadala';
$abc["{aefeA}e"]['cannt'] = 'hedede';
$abc["{afgfA}f"]['cannt'] = 'chehede';
$abc["{AeAG}A"]['cannt'] = 'endendem';
$abc["{BeBG}B"]['cannt'] = 'ododo';
$abc["{cecG}c"]['cannt'] = 'ododo';
$abc["{dedG}d"]['cannt'] = 'adada';
$abc["{dedc}d"]['cannt'] = 'adala';


/*
$abc["hpele"]['cannt'] = '{efeA}';
$abc["hpelf"]['cannt'] = '{fgfe}';

$abc["st3la"]['cannt'] = '{AGGAG}';
$abc["st3b"]['cannt'] = '{GBGBG}';
$abc["st3c"]['cannt'] = '{GcGcG}';
$abc["st3d"]['cannt'] = '{GdGdG}';
$abc["lst3d"]['cannt'] = '{GcGcG}';
$abc["st3e"]['cannt'] = '{AeAeA}';
$abc["st3f"]['cannt'] = '{efefe}';
$abc["st3hg"]['cannt'] = '{fgfgf}';
$abc["st3ha"]['cannt'] = '{gagag}';

$abc["gst3la"]['cannt'] = '{gAGAGAG}';
$abc["gst3b"]['cannt'] = '{gBGBGBG}';
$abc["gst3c"]['cannt'] = '{gcGcGcG}';
$abc["gst3d"]['cannt'] = '{gdGdGdG}';
$abc["lgst3d"]['cannt'] = '{gdcdcdc}';
$abc["gst3e"]['cannt'] = '{geAeAeA}';
$abc["gst3f"]['cannt'] = '{gfefefe}';

$abc["tst3la"]['cannt'] = '{aAGAGAG}';
$abc["tst3b"]['cannt'] = '{aBGBGBG}';
$abc["tst3c"]['cannt'] = '{acGcGcG}';
$abc["tst3d"]['cannt'] = '{adGdGdG}';
$abc["ltst3d"]['cannt'] = '{adcdcdc}';
$abc["tst3e"]['cannt'] = '{aeAeAeA}';
$abc["tst3f"]['cannt'] = '{afefefe}';
$abc["tst3hg"]['cannt'] = '{agfgfgf}';

$abc["hst3la"]['cannt'] = '{AGAGAG}';
$abc["hst3b"]['cannt'] = '{BGBGBG}';
$abc["hst3c"]['cannt'] = '{cGcGcG}';
$abc["hst3d"]['cannt'] = '{dGdGdG}';
$abc["lhst3d"]['cannt'] = '{dcdcdc}';
$abc["hst3e"]['cannt'] = '{eAeAeA}';
$abc["hst3f"]['cannt'] = '{fefefe}';
$abc["hst3hg"]['cannt'] = '{gfgfgf}';
$abc["hst3ha"]['cannt'] = '{agagag}';
*/
/*
 *These would be half doublings in light music
$abc[" dlg"]['cannt'] = '{dG}';
$abc[" dla"]['cannt'] = '{dA}';
$abc[" db"]['cannt'] = '{dB}';
$abc[" dc"]['cannt'] = '{dc}';

$abc[" elg"]['cannt'] = '{eG}';
$abc[" ela"]['cannt'] = '{eA}';
$abc[" eb"]['cannt'] = '{eB}';
$abc[" ec"]['cannt'] = '{ec}';
$abc[" ed"]['cannt'] = '{ed}';

$abc[" flg"]['cannt'] = '{fG}';
$abc[" fla"]['cannt'] = '{fA}';
$abc[" fb"]['cannt'] = '{fB}';
$abc[" fc"]['cannt'] = '{fc}';
$abc[" fd"]['cannt'] = '{fd}';
$abc[" fe"]['cannt'] = '{fe}';


$abc[" glg "]['cannt'] = '{gG}';
$abc[" gla "]['cannt'] = '{gA}';
$abc[" gb "]['cannt'] = '{gB}';
$abc[" gc "]['cannt'] = '{gc}';
$abc[" gd "]['cannt'] = '{gd}';
$abc[" ge "]['cannt'] = '{ge}';
$abc[" gf "]['cannt'] = '{gf}';

$abc[" tlg"]['cannt'] = '{aG}';
$abc[" tla"]['cannt'] = '{aA}';
$abc[" tb"]['cannt'] = '{aB}';
$abc[" tc"]['cannt'] = '{ac}';
$abc[" td"]['cannt'] = '{ad}';
$abc[" te"]['cannt'] = '{ae}';
$abc[" tf"]['cannt'] = '{af}';
$abc[" thg"]['cannt'] = '{ag}';
*/


$abc["A{ag}a"]['cannt'] = 'dili';  
$abc["A{G2dc}d"]['cannt'] = 'tra';  
$abc["G{dc}d"]['cannt'] = 'htra';  
$abc["d{G2dc}d"]['cannt'] = 'tra';  

$abc["A{ag}a"]['cannt'] = 'dili';  //with "tr" over the note instead of gracenotes
$abc["'tr'a"]['cannt'] = 'dili';  //with "tr" over the note instead of gracenotes

$abc["F{geAfA}e"]['cannt'] = 'edre';  
$abc["{geAfA}e"]['cannt'] = 'edre';  
$abc["{aeAfA}e"]['cannt'] = 'edre';  
$abc["e{gfege}f"]['cannt'] = 'dare';  
$abc["{gfege}"]['cannt'] = 'dare';  
$abc["{afege}"]['cannt'] = 'dare';  

$abc["e{ageae}g"]['cannt'] = 'chechere';  
$abc["{ageae}"]['cannt'] = 'chechere';  

$abc["e{AfA}e"]['cannt'] = 'dre';  
$abc["{AfA}e"]['cannt'] = 'dre';  
$abc["f{ege}f"]['cannt'] = 'hedale';  
$abc["{ege}f"]['cannt'] = 'hedale';  
$abc["g{eae}g"]['cannt'] = 'chedere';  
$abc["d{GeG}d"]['cannt'] = 'deda';  


$abc["{aAGdG}A"]['cannt'] = 'henbain';  
$abc["{gAGdG}A"]['cannt'] = 'henbain';  
$abc["B{AGdG}A"]['cannt'] = 'enbain';  
$abc["{AGdG}A"]['cannt'] = 'bain';  
$abc["{aBGdG}B"]['cannt'] = 'hotro';  
$abc["{gBGdG}B"]['cannt'] = 'hotro';  
$abc["C{BGdG}B"]['cannt'] = 'otro';  
$abc["{BGdG}B"]['cannt'] = 'tro';  
$abc["d{cGdG}c"]['cannt'] = 'odro';  
$abc["{cGdG}c"]['cannt'] = 'dro';  
$abc["{acGdG}c"]['cannt'] = 'hodro';  
$abc["{gcGdG}c"]['cannt'] = 'hodro';  
$abc["e{dGdG}d"]['cannt'] = 'adeda';  
$abc["{dGdG}d"]['cannt'] = 'deda';  
$abc["{gdGdG}d"]['cannt'] = 'hadeda';  
$abc["{adGdG}d"]['cannt'] = 'hadeda';  








$abc["hiharin"]['cannt'] = 'e{dAGAG}A';  
$abc["hiharin"]['cannt'] = 'e{dAGAG}A';  
$abc["phiharin"]['cannt'] = 'e{dAGAG}A';  //tilde
$abc["phiharin"]['cannt'] = 'e{dAGAG}A';  //tilde
$abc["rodin"]['cannt'] = 'c{GBG}A';  
$abc["rodin"]['cannt'] = 'c{GBG}A';  
$abc["chelalho"]['cannt'] = 'e{f2de}dec2';  
$abc["chelalho"]['cannt'] = 'e{f2de}dec2';  
$abc["din"]['cannt'] = 'B{G2}A';  
$abc["din"]['cannt'] = 'B{G2}A';  
/*
//Pobrach Lemluaths
$abc["lem"]['cannt'] = '{GdG}';
$abc["lem"]['cannt'] = '{GdG}';
$abc["lemb"]['cannt'] = '{GBG}';
$abc["lemb"]['cannt'] = '{GBG}';
$abc["hlemla"]['cannt'] = '{g}G{dA}e';
$abc["hlemla"]['cannt'] = '{g}G{dA}e';
$abc["hlemlg"]['cannt'] = '{g}G{dG}e';
$abc["hlemlg"]['cannt'] = '{g}G{dG}e';
$abc["LA_4 lem"]['cannt'] = '{g}A2>{GdG}e';
$abc["LA_4 lem"]['cannt'] = '{g}A2>{GdG}e';
$abc["LA_4 pl"]['cannt'] = '{g}A2>{GdG}e'; //"L" below the note instead of grace.
$abc["LA_4 pl"]['cannt'] = '{g}A2>{GdG}e'; //"L" below the note instead of grace.
$abc["pl"]['cannt'] = '"vL"';
$abc["pl"]['cannt'] = '"vL"';
$abc["plb"]['cannt'] = '{GBG}d'; //L below
$abc["plb"]['cannt'] = '{GBG}d'; //L below
$abc["phlla"]['cannt'] = '{g}G{dA}e';
$abc["phlla"]['cannt'] = '{g}G{dA}e';

//Pob
$abc[" tar"]['cannt'] = '{g}A2>{GdGe}A'; //
$abc[" tar"]['cannt'] = '{g}A2>{GdGe}A'; //
$abc[" tarb"]['cannt'] = '{g}d2>{GBGe}A'; //
$abc[" tarb"]['cannt'] = '{g}d2>{GBGe}A'; //
$abc["htarla"]['cannt'] = '{g}G2>{dAe}A'; //
$abc["htarla"]['cannt'] = '{g}G2>{dAe}A'; //
$abc["htarlg"]['cannt'] = '{g}G2>{dGe}A'; //
$abc["htarlg"]['cannt'] = '{g}G2>{dGe}A'; //
$abc["LA_4 pl"]['cannt'] = '{g}A2>{GdGe}A'; //"T" below the note instead of grace.
$abc["LA_4 pl"]['cannt'] = '{g}A2>{GdGe}A'; //"T" below the note instead of grace.
$abc["tarbrea"]['cannt'] = '{g}G2>{dGe}G'; //
$abc["tarbrea"]['cannt'] = '{g}G2>{dGe}G'; //
$abc["tarbbrea"]['cannt'] = '{g}B2>{GdGe}G'; //
$abc["tarbbrea"]['cannt'] = '{g}B2>{GdGe}G'; //
$abc["htarlabrea"]['cannt'] = '{g}d2>{GBGe}G'; //
$abc["htarlabrea"]['cannt'] = '{g}d2>{GBGe}G'; //
$abc["tmb"]['cannt'] = '{g}B<{GdGe}B'; // a mach
$abc["tmb"]['cannt'] = '{g}B<{GdGe}B'; // a mach
$abc["tmb"]['cannt'] = '{g}c<{GdGe}c'; // a mach
$abc["tmb"]['cannt'] = '{g}c<{GdGe}c'; // a mach
$abc["tmd"]['cannt'] = '{g}B<{G2dc}d{e}d'; // a mach
$abc["tmd"]['cannt'] = '{g}B<{G2dc}d{e}d'; // a mach
//Triplings
$abc["triplg"]['cannt'] = '(3:2{g}G{d}G{e}G)'; // fosgailte
$abc["triplg"]['cannt'] = '(3:2{g}G{d}G{e}G)'; // fosgailte
$abc["tripla"]['cannt'] = '(3:2{g}A{d}A{e}A)'; // fosgailte
$abc["tripla"]['cannt'] = '(3:2{g}A{d}A{e}A)'; // fosgailte
$abc["tripb"]['cannt'] = '(3:2{g}B{d}B{e}B)'; // fosgailte
$abc["tripb"]['cannt'] = '(3:2{g}B{d}B{e}B)'; // fosgailte
$abc["tripc"]['cannt'] = '(3:2{g}c{d}c{e}c)'; // fosgailte
$abc["tripc"]['cannt'] = '(3:2{g}c{d}c{e}c)'; // fosgailte

$abc["ttriplg"]['cannt'] = '(3:2{a}G{d}G{e}G)'; // fosgailte
$abc["ttriplg"]['cannt'] = '(3:2{a}G{d}G{e}G)'; // fosgailte
$abc["ttripla"]['cannt'] = '(3:2{a}A{d}A{e}A)'; // fosgailte
$abc["ttripla"]['cannt'] = '(3:2{a}A{d}A{e}A)'; // fosgailte
$abc["ttripb"]['cannt'] = '(3:2{a}B{d}B{e}B)'; // fosgailte
$abc["ttripb"]['cannt'] = '(3:2{a}B{d}B{e}B)'; // fosgailte
$abc["ttripc"]['cannt'] = '(3:2{a}c{d}c{e}c)'; // fosgailte
$abc["ttripc"]['cannt'] = '(3:2{a}c{d}c{e}c)'; // fosgailte

$abc["htriplg"]['cannt'] = '(3:2G{d}G{e}G)'; // fosgailte
$abc["htriplg"]['cannt'] = '(3:2G{d}G{e}G)'; // fosgailte
$abc["htripla"]['cannt'] = '(3:2A{d}A{e}A)'; // fosgailte
$abc["htripla"]['cannt'] = '(3:2A{d}A{e}A)'; // fosgailte
$abc["htripb"]['cannt'] = '(3:2B{d}B{e}B)'; // fosgailte
$abc["htripb"]['cannt'] = '(3:2B{d}B{e}B)'; // fosgailte
$abc["htripc"]['cannt'] = '(3:2c{d}c{e}c)'; // fosgailte
$abc["htripc"]['cannt'] = '(3:2c{d}c{e}c)'; // fosgailte

$abc["crunl"]['cannt'] = '>{GdGeAfA}e'; //
$abc["crunl"]['cannt'] = '>{GdGeAfA}e'; //
$abc["crunlb"]['cannt'] = '>{GBGeAfA}e'; //
$abc["crunlb"]['cannt'] = '>{GBGeAfA}e'; //
$abc["hcrunla"]['cannt'] = '{g}G>{dAeAfA}e'; //
$abc["hcrunla"]['cannt'] = '{g}G>{dAeAfA}e'; //
$abc["hcrunlgla"]['cannt'] = '{g}G>{dGeAfA}e'; //
$abc["hcrunlgla"]['cannt'] = '{g}G>{dGeAfA}e'; //

$abc["crunlbrea"]['cannt'] = '{g}G>{dGeGfG}e'; //
$abc["crunlbrea"]['cannt'] = '{g}G>{dGeGfG}e'; //
$abc["crunlbbrea"]['cannt'] = '{g}B>{GBGeAfA}e'; //
$abc["crunlbbrea"]['cannt'] = '{g}B>{GBGeAfA}e'; //
$abc["hcrunllabrea"]['cannt'] = '{g}d>{GBGeGfG}e'; //
$abc["hcrunllabrea"]['cannt'] = '{g}d>{GBGeGfG}e'; //
//Crunluath a Machs
$abc["cmb"]['cannt'] = '{gBGdG}B<{eBfB}e'; //
$abc["cmb"]['cannt'] = '{gBGdG}B<{eBfB}e'; //
$abc["cmc"]['cannt'] = '{gcGdG}c<{ecfc}e'; //
$abc["cmc"]['cannt'] = '{gcGdG}c<{ecfc}e'; //
$abc["cmd"]['cannt'] = '{g}B{Gdc}d{edfd}e'; //
$abc["cmd"]['cannt'] = '{g}B{Gdc}d{edfd}e'; //
//Crunluath Fosgailth a Machs
$abc["edreb"]['cannt'] = '{g}G2{d}B{edfd}e'; //
$abc["edreb"]['cannt'] = '{g}G2{d}B{edfd}e'; //
$abc["edrec"]['cannt'] = '{g}A2{d}c{ecfc}e'; //
$abc["edrec"]['cannt'] = '{g}A2{d}c{ecfc}e'; //
$abc["edred"]['cannt'] = '{g}A2d{edfd}e'; //
$abc["edred"]['cannt'] = '{g}A2d{edfd}e'; //
//$abc[\t]['cannt'] = ' '; //beat separator
*/
