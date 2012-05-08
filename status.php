#!/usr/bin/php
<?
////////////////////////////////////////////////////////////////////////////////////
//
//	Item Status Script
//
//	This script will check on items and return the status of them.
//
////////////////////////////////////////////////////////////////////////////////////
//
//	February 28, 2012 Brian Murphy
//		Modified script to be more compatible with various scrapers
//		rather than being tied to a single one.
//
//	June 27, 2011 Brian Murphy
//	www.gurudigitalsolutions.com
//
////////////////////////////////////////////////////////////////////////////////////
//
//	Check the command line for options,
//	Run the search for each item that needs it.
//

$iamwho = trim(`whoami`);
$Where = "/home/" . $iamwho . "/Code/torrentscrape";
chdir($Where);

require_once("modules/module_includes.php");
require_once("wanted.php");



if(!file_exists("wantedlib.txt"))
{
	echo "You don't have anything queued up to look for.\n";
	echo "Try running add.php first :)\n";
	exit;
}

$Wants = unserialize(file_get_contents("wantedlib.txt"));

//
//	Now to check the command line for options.
//

$doall = false;
$dolist = false;
$doid = -1;
$dosearch = false;
$domincron = -1;
$domaxcron = -1;
$dolimit = -1;
$dosavetorrents = false;

if(count($argv) > 1)
{
	//	Parse arguments
	for($earg = 1; $earg < count($argv); $earg++)
	{
		if($argv[$earg] == "-n" && count($argv) > $earg + 1) { $doid = $argv[$earg + 1]; }
		if($argv[$earg] == "-l") { $dolist = true; }
		if($argv[$earg] == "-s") { $dosearch = true; }
		if($argv[$earg] == "--limit" && count($argv) > $earg + 1) { $dolimit = $argv[$earg + 1]; }
		if($argv[$earg] == "--maxcron" && count($argv) > $earg + 1) { $domaxcron = $argv[$earg + 1]; }
		if($argv[$earg] == "--mincron" && count($argv) > $earg + 1) { $domincron = $argv[$earg + 1]; }
		if($argv[$earg] == "--st") { $dosavetorrents = true; }
	}
	
	if(strtoupper($doid) == "ALL")
	{
		echo "Attempting to perform status update on all wanted items (.".count($Wants->WantedLibrary).")\n";
		for($ewant = 0; $ewant < count($Wants->WantedLibrary); $ewant++)
		{
			$doid = $ewant;
			DisplayItem($ewant);
		}
		$doid = -1;
	}
	elseif(is_numeric($doid) && $doid > -1) { DisplayItem($doid); }
	elseif($dolist) { DisplayList(); }
}
else
{
	//DisplayList();
	ShowHelp();
}


function ShowHelp()
{
	echo "Guru's Wanted Queue Status\n";
	echo "Use this to check and update the status of your\n";
	echo "Wanted Library.\n\n";
	echo "USAGE: ./status.php [OPTIONS]\n";
	echo "Options:\n";
	echo "\t-l\tList the Wanted List\n";
	echo "\t-n <ID>\tShow details about item number <ID>\n";
	echo "\t\tIf <ID> == ALL, do all.\n";
	echo "\t-s\tPerform a search for the items.\n";
	echo "\t--limit\tLimit the number of entries processed.\n";
	echo "\t--mincron\tLow end to start at chronologically.\n";
	echo "\t--maxcron\tHigh end to stop at chronologically.\n";
	echo "\t--st\tSave torrent files when they are found.\n";
	
	exit;
}

function DisplayList()
{
	//
	//	This functin will display quick details about each item in queue.
	//
	
	global $Wants;
	foreach($Wants->WantedLibrary as $wkey=>$wval)
	{
		echo "ID: ".$wkey."  ".$wval->Name."\n";
		echo "Min/Max Cron: ".$wval->CronMin."/".$wval->CronMax." (".$wval->CronDigits." digits.)\n";
		echo "Search Query: ".$wval->Query."\n";
		echo "---------------------------\n";
	}
}

function DisplayItem($itemno)
{
	//
	//	This function will display details about a particular item in the queue.
	//
	
	global $Wants, $dosearch, $domaxcron, $domincron, $dolimit, $dosavetorrents;
	
	if($itemno < 0
	|| $itemno >= count($Wants->WantedLibrary)
	|| $Wants->WantedLibrary[$itemno] == null)
	{
		echo "You have entered an invalid item ID.\n";
		return;
	}
	
	$wkey = $itemno;
	$wval = $Wants->WantedLibrary[$itemno];
	
	echo "ID: ".$wkey."  ".$wval->Name."\n";
	echo "Min/Max Cron: ".$wval->CronMin."/".$wval->CronMax." (".$wval->CronDigits." digits.)\n";
	echo "Search Query: ".$wval->Query."\n";
	echo "---------------------------\n";
	
	
	//foreach($wval->CronList as $ecid=>$ecron)
	if($domincron > -1) { $startat = $domincron; } else { $startat = 0; }
	if($domaxcron > -1 && $domaxcron < count($wval->CronList)) { $endat = $domaxcron; } else { $endat = count($wval->CronList); }
	$startat = $startat + $wval->CronMin;
	$endat = $endat + $wval->CronMin;
	
	$ttlloops = 0;
	
	for($ecid = $startat; $ecid < $endat; $ecid++)
	{
		$ecron = $wval->CronList[$ecid];
		//	Loop through each available cronology spot and put up some
		//	details.
		
	
		echo "File number: ".$wval->FormatCronNum($ecid)."\n";
		echo "Query: ".$wval->SearchQuery($ecid)."\n";
		
		//if($ecron["DORETRIEVE"]) { echo "Do Receive: True\n"; }
		
		//print_r($wval->CronList[$ecid]);
		
		if($wval->CronList[$ecid]["DORETRIEVE"]) { echo "Do Retrieve: True\n"; }
		else { echo "Do Retrieve: False\n"; }
		if($wval->CronList[$ecid]["RETRIEVED"]) { echo "Retrieved: True\n"; }
		else { echo "Retrieved: False\n"; }
		
		//$lcago = time() - $ecron["RETRIEVALDATA"]["LASTCHECKED"];
		$lcago = time() - $Wants->WantedLibrary[$wkey]->CronList[$ecid]["RETRIEVALDATA"]["LASTCHECKED"];
		echo "Last checked: ".$lcago." seconds ago.\n";
		
		if(!$Wants->WantedLibrary[$wkey]->TorrentURLFound($ecid)) { echo "Not yet spotted!\n"; }
		else { echo "Spotted at ".$Wants->WantedLibrary[$wkey]->TorrentURL($ecid)."\n"; }
		
		if(!$Wants->WantedLibrary[$wkey]->CronList[$ecid]["DORETRIEVE"])
		{

		}
		elseif($Wants->WantedLibrary[$wkey]->CronList[$ecid]["RETRIEVED"])
		{
			echo "This torrent has already been retrieved.\n";
		}
		elseif($dosearch)
		{
			echo "Performing a search now...\n";
			$scraper = new etree($wval->SearchQuery($ecid));
			$Wants->WantedLibrary[$wkey]->CronList[$ecid]["RETRIEVALDATA"]["LASTCHECKED"] = time();
			
			if($scraper->TotalResults > 0)
			{
				//	Results were found, so we need to update the
				//	Wanted Queue with that info
				
				$Wants->WantedLibrary[$wkey]->CronList[$ecid]["RETRIEVALDATA"]["URL"] = $scraper->BestUrl($wval->FormatCronNum($ecid));
				$Wants->WantedLibrary[$wkey]->CronList[$ecid]["RETRIEVALDATA"]["TITLE"] = $scraper->ResultTitle($wval->FormatCronNum($ecid));;
				
				if($Wants->WantedLibrary[$wkey]->CronList[$ecid]["RETRIEVALDATA"]["URL"] == "")
				{
					echo "No viable torrents were found.\n";
				}
				else
				{
					//
					//	This torrent was just found, so it needs to be downloaded
					//	and saved.
					//
					echo "Spotted at: ".$Wants->WantedLibrary[$wkey]->CronList[$ecid]["RETRIEVALDATA"]["URL"]."\n";
					$Wants->WantedLibrary[$wkey]->SaveTorrent($ecid);
					$Wants->Save();
				}
			}
			
			$Wants->Save();
		}
		//print_r($Wants->WantedLibrary[$wkey]->CronList);
		echo "-------------------------\n";
		$ttlloops++;
		
		if($dolimit > 0)
		{
			if($dolimit == $ttlloops) { break; }
		}
	}
}
?>
