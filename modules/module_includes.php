<?/////////////////////////////////////////////////////////////////////////////
//
//	btguru bittorrent scraper - module includes
//
///////////////////////////////////////////////////////////////////////////////
//
//	Copyright 2012, March 1, 2012 Brian Murphy
//	www.gurudigitalsolutions.com
//
///////////////////////////////////////////////////////////////////////////////
//
//	This file has includes for each module
//

//	
	//	Include all of the scraper classes, and the abstract class file
	//
	
	$DebugLevel = 0;
	
	if(!file_exists("modules/scrapers.php")) { dienice("Could not load scrapers module."); }
	if(!file_exists("modules/scrapers")) { dienice("Scrapers module folder not found."); }
	
	require_once("modules/scrapers.php");
	require_once("modules/scrapers/etree-scrape.php");
	
function DebugMsg($message, $verblevel = 1)
{
	//	If verbosity is set, send some debug message out.
	//	VerbLevels:
	//		0	No debug messages
	//		1	Major steps taking places
	//		2	Minor steps in a major steps
	//		3	Info detailing things in the minor steps
	
	global $DebugLevel;
	if($DebugLevel == 0) { return; }
	if($verblevel <= $DebugLevel)
	{
		echo "DEBUG".$verblevel.":: ".$message."\n";
	}
}

?>
