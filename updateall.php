#!/usr/bin/php
<?//////////////////////////////////////////////////////////////////////////////////
//
//	Update All Wanted Torrents Script
//
//	This script will update all of the torrent searches for the stuff you want.
//
//	This should be run either on a cron job or just from the command line.
//
////////////////////////////////////////////////////////////////////////////////////
//
//	Written July 3rd, 2011 by Brian Murphy
//	www.gurudigitalsolutions.com
//
////////////////////////////////////////////////////////////////////////////////////

$iamwho = `whoami`;
$Where = "/home/" . trim($iamwho) . "/Code/torrentscrape";
chdir($Where);

if(!file_exists("wantedlib.txt"))
{
	echo "You don't have anything queued up to look for.\n";
	echo "Try running add.php first :)\n";
	echo getcwd()."\n";
	echo $Where."\n";
	exit;
}

if(count($argv) > 1
&& strtoupper($argv[1]) != "-V")
{
	echo "Update All Wanted Stuff Script\n";
	echo "\n";
	echo "USAGE: ./updateall.php [OPTIONS]\n";
	echo "Options:\n";
	echo "\t-v\tDisplay all output.\n";
	exit;
}

$cmd = "./status.php -n ALL -s --st";
$output = `$cmd`;

if(count($argv) > 1
&& strtoupper($argv[1]) == "-V")
{
	echo $output;
}

?>
