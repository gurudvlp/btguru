#!/usr/bin/php
<?/////////////////////////////////////////////////////////////////////////////
//
//	btguru bittorrent scraper
//
///////////////////////////////////////////////////////////////////////////////
//
//	Copyright 2012, March 1, 2012 Brian Murphy
//	www.gurudigitalsolutions.com
//
///////////////////////////////////////////////////////////////////////////////
//
//	This is the main engine for the btguru bittorrent scraper.  This
//	script initializes everything, and basically figures out what sub
//	section to launch.
//
///////////////////////////////////////////////////////////////////////////////

define("APPNAME", "btguru bittorrent scraper");
define("APPVERSION", "v2012.3.1");

$Scrapers = array();

require_once("modules/module_includes.php");

if(count($argv) < 2) { DisplayHelp(); }

$skiparg = false;
for($earg = 2; $earg < count($argv); $earg++)
{
	if(!skiparg)
	{
		if(strtoupper($argv[$earg]) == "-SCRAPERS"
		&& count($argv) >= $earg + 2)
		{
			//	Limit the scrapers that this script will end up
			//	using.
			$skiparg = true;
			
			$scrapeprts = explode(",", $argv[$earg + 1]);
			for($escraper = 0; $escraper < $scrapeprts; $escraper++)
			{
				$Scrapers[] = new $scrapeprts[$escraper]();
			}
		}
		elseif(strtoupper($argv[$earg]) == "-SEARCH"
		&& count($argv) >= $earg + 2)
		{
			$skiparg = true;
			
			
		}
	}
	else { $skiparg = false; }
}


function dienice($reason)
{
	echo $reason."\n";
	exit;
}

function DisplayHelp()
{
	//
	//	Display some help and command line options and stuff.
	//
	
	echo APPNAME." ".APPVERSION."\n";
	echo "\twww.gurudigitalsolutions.com\n\n";
	echo "USAGE: btguru [OPTIONS]\n";
	echo "OPTIONS:\n";
	echo "\t-search <query>\t\tSearch the loaded indexes with <query>\n";
	echo "\t-scrapers <scrapers>\t\tSpecify which scrapers to use.  Separate list with commas.\n";
	
	
	exit;
}
?>
