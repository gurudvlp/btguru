#!/usr/bin/php
<?
////////////////////////////////////////////////////////////////////////////////////
//
//	Add Item To Watch Script
//
//	This script will add an item to be watched.
//
////////////////////////////////////////////////////////////////////////////////////
//
//	June 27, 2011 Brian Murphy
//	www.gurudigitalsolutions.com
//
////////////////////////////////////////////////////////////////////////////////////
//
//	Parse the command line arguments so we know what the fuck to do!
//

require_once("wanted.php");
require_once("modules/module_includes.php");


$iamwho = `whoami`;
$Where = "/home/" . trim($iamwho) . "/Code/torrentscrape";
chdir($Where);

if(file_exists("wantedlib.txt")) { $Wants = unserialize(file_get_contents("wantedlib.txt")); }
else { $Wants = new WantedMedia(); }

if(count($argv) == 1) { DisplayHelp(); }
$itemname = $argv[1];
$itemmincron = -1;
$itemmaxcron = -26;
$itemdigits = -2;
$itemquery = "";

if(count($argv) > 2)
{
	for($earg = 2; $earg < count($argv); $earg++)
	{
		//	Parse each command line option

		if($argv[$earg] == "-q") { $itemquery = $argv[$earg + 1]; }
		elseif($argv[$earg] == "--mincron")
		{
			$itemmincron = $argv[$earg + 1];
			if(!is_numeric($itemmincron)) { $itemmincron = 1; }
		}
		elseif($argv[$earg] == "--maxcron")
		{
			$itemmaxcron = $argv[$earg + 1];
			if(!is_numeric($itemmaxcron)) { $itemmaxcron = 26; }
		}
		elseif($argv[$earg] == "-d")
		{
			$itemdigits = $argv[$earg + 1];
			if(!is_numeric($itemdigits)) { $itemdigits = 2; }
		}
	}
}

//
//	Now we must figure out the information from what wasn't entered
//	at the command line.
//

if($itemquery == "") { $itemquery = GetUserInput("Search Query: ", false, "[CRONNUM]"); }
if($itemmincron < 0) { $itemmincron = GetUserInput("Lowest number in the sequence: ", true); }
if($itemmaxcron < 0) { $itemmaxcron = GetUserInput("Highest number in the sequence: ", true); }
if($itemdigits < 0) { $itemdigits = GetUserInput("Number of digits for search: ", true); }

//
//	Hell yeah, we got our variables set for this set of stuff to retrieve!!
//	A new item needs to be created.
//

$itemid = $Wants->AddItem($itemname, $itemquery, $itemmincron, $itemmaxcron, $itemdigits);
$Wants->Save();

echo $itemname . " has been added to your queue.\n";

function DisplayHelp()
{
	//
	//	Either they want help or typed the command in wrong.
	//
	
	echo "Guru's Automated Torrent Finder\n";
	echo "Use this script to add media you would like to\n";
	echo "automatically retrieve.\n\n";
	
	echo "USAGE: ./add.php <CollectionTitle> [options]\n\n";
	echo "Options:\n";
	echo "\t-q <Search Query>\tThe query to use for the search.\n";
	echo "\t\tThis should contain [CRONNUM] if there are multiple\n";
	echo "\t\tparts to the process.\n";
	echo "\t--mincron <Number>\tMinimum number in the cronological sequence.\n";
	echo "\t--maxcron <Number>\tMaximum number in the cronological sequence.\n";
	echo "\t-d <Number>\tNumber of digits to use for cronological search.\n\n";
	echo "Example:\n";
	echo "\t./add.php \"TCG Sterling\" -q \"Colours out of Space [CRONNUM]\" --mincron 25 --maxcron 99 -d 2\n";
	
	exit;
}

function GetUserInput($statement, $numericonly = true, $reqtext = "")
{
	//
	//	$numericonly determines if only numbers are allowed in the users input
	//
	
	echo $statement;
	
	while($din = trim(fgets(STDIN)))
	{
		if($din == "") { echo "We are looking for some input dude.\n"; }
		elseif($numericonly && is_numeric($din)) { if($din < 0) { return $din*(-1); } else return $din; }
		elseif(!$numericonly
		&& (str_replace(strtoupper($reqtext), "", strtoupper($din)) != strtoupper($din)
			|| $reqtext == "")) { return $din; }
	}
}

?>
