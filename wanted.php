<?
////////////////////////////////////////////////////////////////////////////////////
//
//	Wanted Stuff Script
//
//	This script is a class that provides the functionality of maintaining what
//	new media is wanted.
//
////////////////////////////////////////////////////////////////////////////////////
//
//	February 28, 2012 Brian Murphy
//		-Updated script to be more compatible with an open scraping method
//
//	June 27, 2011 Brian Murphy
//	www.gurudigitalsolutions.com
//
////////////////////////////////////////////////////////////////////////////////////


class WantedMedia
{
	var $WantedLibrary = array();

	function WantedMedia()
	{
	
	}
	
	function AddItem($name, $query, $cronmin = 1, $cronmax = 26, $crondigits = 2)
	{
		//
		//	This function will add an item to the wanted-items list.
		//	Things like "Primus 2011 02 05 Live" or
		//	"The Grateful Dead 1985"
		//
		
		if($name == "" || $query == "")
		{
			return false;
		}
		
		$wlibcnt = count($this->WantedLibrary);
		//echo "-- CronMin : ".$cronmin . " :: CronMax : ".$cronmax." --\n";
		$this->WantedLibrary[$wlibcnt] = new WantedItem($name, $query, $cronmin, $cronmax);
		$this->WantedLibrary[$wlibcnt]->CronDigits = $crondigits;
		
		return $wlibcnt;
	}
	
	function Save($tofile = "wantedlib.txt")
	{
		//
		//	Save the list of stuff that is wanted.
		//
		
		file_put_contents($tofile, serialize($this));
		return true;
	}
}

class WantedItem
{
	var $Name = "";
	var $Query = "";
	var $CronMin = 1;
	var $CronMax = 26;
	var $CronDigits = 2;
	var $CronList = array();
	var $TorrentPath = "torrents";
	
	function WantedItem($name = "", $query = "", $cronmin = 1, $cronmax = 26)
	{
		//
		//	Construct a wanted item
		//
		
		$this->Name = $name;
		$this->Query = $query;
		
		if(!is_numeric($cronmin)) { $cronmin = 1; }
		if(!is_numeric($cronmax)) { $cronmax = 26; }
		
		$this->CronMin = $cronmin;
		$this->CronMax = $cronmax;
		
		for($elitem = $cronmin; $elitem < $cronmax+1; $elitem++)
		{
			$this->CronList[$elitem] = array(
				"DORETRIEVE"=>true,
				"RETRIEVED"=>false,
				"RETRIEVALDATA"=>array(
					"DATE"=>"",
					"TIME"=>"",
					"URL"=>"",
					"TITLE"=>"",
					"LASTCHECKED"=>time()
				)
			);
		}
	}
	
	function FormatCronNum($num)
	{
		//
		//	Format and return a cronology number.
		//
		
		while($this->CronDigits > strlen($num))
		{
			$num = "0".$num;
		}
		
		return $num;
	}
	
	function SearchQuery($itemno)
	{
		return str_replace("[CRONNUM]", $this->FormatCronNum($itemno), $this->Query);
	}
	
	function PerformSearch($cronnum)
	{
		//
		//	This function will perform a search for the given number.
		//
		
		$TheSearch = new etree($this->SearchQuery($itemno));
		return array("totalresults"=>$TheSearch->TotalResults, "searchresults"=>$TheSearch->ScrapeResults);
	}
	
	function TorrentURLFound($itemno)
	{
		//
		//	This function will determine whether or not a particular
		//	torrent has already been found or not.
		//
		
		$daurl = $this->CronList[$itemno]["RETRIEVALDATA"]["URL"];
		if($daurl == "") { return false; }
		else { return true; }
	}
	
	function TorrentURL($itemno)
	{
		$daurl = $this->CronList[$itemno]["RETRIEVALDATA"]["URL"];
		return $daurl;
	}
	
	function SaveTorrent($itemno)
	{
		//
		//	This here is what the whole thing is about, automatically
		//	savin some torrents :)
		//
		
		if(!$this->TorrentURLFound($itemno)) { return false; }
		$daurl = $this->TorrentURL($itemno);
		
		
		$cp = curl_init($daurl);
		curl_setopt($cp, CURLOPT_HEADER, false);
		curl_setopt($cp, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($cp, CURLOPT_USERAGENT, "Guru Scraper (www.gurudigitalsolutions.com)");
		
		$thistorrent = curl_exec($cp);
		curl_close($cp);
		
		if(substr($this->TorrentPath, -1, 1) == "/") { $this->TorrentPath = substr($this->TorrentPath, 0, strlen($this->TorrentPath) -1); }
		file_put_contents($this->TorrentPath."/".$this->Name."-".$itemno.".torrent", $thistorrent);
		
		$this->CronList[$itemno]["RETRIEVED"] = true;
		$this->CronList[$itemno]["RETRIEVALDATA"]["DATE"] = date("D M d, Y");
		$this->CronList[$itemno]["RETRIEVALDATA"]["TIME"] = date("G:i");
		
		return true;
	}
}

?>
