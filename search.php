#!/usr/bin/php
<?
////////////////////////////////////////////////////////////////////////////////////
//
//	btguru torrent search madule
//
//	This script will search torrent indexes for the given search phrase
//
////////////////////////////////////////////////////////////////////////////////////
//
//	Copyright 2011 Brian Murphy
//	www.gurudigitalsolutions.com
//
////////////////////////////////////////////////////////////////////////////////////
//
//	Changes:
//
//	Mar 2, 2012:
//		-Including the ability to return search results as a php
//		serialized array.
//		-Made the search query argument both available by a flag,
//		and just by not matching other arguments.
//		-Added debug levels and debug message functionality.
//		-Added the ability to format output
//
///////////////////////////////////////////////////////////////////////////////

$iamwho = `whoami`;
$Where = "/home/" . trim($iamwho) . "/Code/torrentscrape";
chdir($Where);

//print_r($_SERVER);
$SerializeSearchResult = false;
$SearchQuery = "";
$MaxSearchResults = 50;
$OutputFormat = "";

require_once("modules/module_includes.php");


if(count($argv) == 1)
{
	DisplayHelp();
}

ParseArguments($argv);



$scraper = new etree();
$scraper->MaxSearchResults = $MaxSearchResults;
$scraper->SearchTerms = $SearchQuery;
$scraper->Run();

if($SerializeSearchResult) { echo serialize($scraper->ScrapeResults); }
else
{
	//	Not serializing search results.
	DebugMsg("Not serializing search results.", "1");
	
	if($OutputFormat == "") { print_r($scraper->ScrapeResults); }
	else
	{
		for($eres = 0; $eres < count($scraper->ScrapeResults); $eres++)
		{
			$tline = $OutputFormat;
			$tline = str_replace("[RESULTID]", $eres, $tline);
			$tline = str_replace("[NAME]", $scraper->ScrapeResults[$eres]["name"], $tline);
			$tline = str_replace("[LINK]", $scraper->ScrapeResults[$eres]["link"], $tline);
			$tline = str_replace("[SIZE]", $scraper->ScrapeResults[$eres]["size"], $tline);
			$tline = str_replace("[DATE]", $scraper->ScrapeResults[$eres]["date"], $tline);
			$tline = str_replace("[SEED]", $scraper->ScrapeResults[$eres]["seed"], $tline);
			$tline = str_replace("[LEECH]", $scraper->ScrapeResults[$eres]["leech"], $tline);
			$tline = str_replace("\\n", "\n", $tline);
			echo $tline;
		}
	}
}


function DisplayHelp($message = "")
{
	echo "btguru torrent search\n\n";
	echo "Usage search.php [options] <Search Terms>\n";
	if($message != "")
	{
		echo "**".$message."\n\n";
	}
	echo "OPTIONS:\n";
	echo "\t-sl\t--serialize\t\tReturn search results as a serialized array.\n";
	echo "\t-q \"<query>\"\t--query \"<query>\"\t\tThe phrase to search for.\n";
	echo "\t-v\t--verbose\t\tIncrease the script verbosity level by one. -vv or -vvv for more.\n";
	echo "\t-max <number>\t--maxresults <number>\t\tLimit the number of search results returned.\n";
	echo "\t-f <format>\t--format <format>\t\tSpecify the format for output.\n";
	echo "\n";
	echo "<format> - Output formats:\n";
	echo "\t<format> should be a string of how you would like the output displayed.\n";
	echo "\tAvailable format options:\n";
	echo "\t[RESULTID], [NAME], [LINK], [SIZE], [DATE], [SEED], [LEECH]\n";
	echo "Example: ./search.php \"primus\" -f \"[RESULTID] [NAME] [SIZE]\\n[LINK]\\n\\n\n";
	echo "Will return a result like:\n\n";
	echo "0 Primus - 2004-06-19 - Toronto, ON - Hummingbird Centre 691.17\n";
	echo "http://bt.etree.org/download.php/552651/primus2004-06-19.torrent\n\n";
	exit;
}

function ParseArguments($args)
{
	global $SerializeSearchResult;
	global $SearchQuery;
	global $DebugLevel;
	global $MaxSearchResults;
	global $OutputFormat;
	
	DebugMsg("Parsing command line arguments.", 1);
	$skiparg = false;
	for($earg = 1; $earg < count($args); $earg++)
	{
		if(!$skiparg)
		{
			DebugMsg("Parsing command line argument.", 3);
			
			if((strtoupper($args[$earg]) == "--SERIALIZE"
			|| strtoupper($args[$earg]) == "--SERIALISE"
			|| strtoupper($args[$earg]) == "-SL")
			&& count($args) >= $earg + 1)
			{
				//	The user would like the search output to
				//	be a serialized array.
				DebugMsg("Parsing --serialize", 3);
				$SerializeSearchResult = true;
			} else
			if(substr($args[$earg], 0, 1) != "-")
			{
				//	This doesn't match any option, so it
				//	is going to be treated as the search
				//	query.
				DebugMsg("Parsing non-hyphenated argument: ".$args[$earg], 3);
				$SearchQuery = $args[$earg];
			} else
			if((strtoupper($args[$earg]) == "--QUERY"
			|| strtoupper($args[$earg]) == "-Q")
			&& count($args) >= $earg + 2)
			{
				DebugMsg("Parsing -query", 3);
				$skiparg = true;
				$SearchQuery = $args[$earg + 1];
			} else
			if(strtoupper($args[$earg]) == "--VERBOSE"
			|| strtoupper($args[$earg]) == "-V")
			{
				//	Increase the verbosity level.
				$DebugLevel++;
			} else
			if(strtoupper($args[$earg]) == "-VV")
			{
				$DebugLevel += 2;
			} else
			if(strtoupper($args[$earg]) == "-VVV")
			{
				$DebugLevel += 3;
			} else
			if((strtoupper($args[$earg]) == "--MAXRESULTS"
			|| strtoupper($args[$earg]) == "-MAX")
			&& count($args) >= $earg + 2)
			{
				//	Limit the maximum number of search results
				//	returned.
				DebugMsg("Setting maximum results allowed", 3);
				$skiparg = true;
				if(is_numeric($args[$earg + 1]))
				{
					$MaxSearchResults = $args[$earg + 1];
					DebugMsg("-MAX <flag>, flag was set to ".$MaxSearchResults, "3");
				}
				else { DebugMsg("-MAX <flag>, flag was not numeric. (".$args[$earg + 1].")", "3"); }
			} else
			if((strtoupper($args[$earg]) == "--FORMAT"
			|| strtoupper($args[$earg]) == "-F")
			&& count($args) >= $earg + 2)
			{
				//	Set the output format
				DebugMsg("Setting the output format template.", 3);
				$skiparg = true;
				$OutputFormat = $args[$earg + 1];
			}
		}
		else
		{
			DebugMsg("Skipping this command line argument", 3);
			$skiparg = false;
		}
	}
}



?>
