<?php

// WebSVN - Subversion repository viewing via the web using PHP
// Copyright (C) 2004 Tim Armes
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
//
// --
//
// listing.php
//
// Show the listing for the given repository/path/revision

require("include/config.inc");
require("include/svnlook.inc");
require("include/template.inc");

$rep = @$_REQUEST["rep"];
$path = @$_REQUEST["path"];
$rev = @$_REQUEST["rev"];
$showchanged = (@$_REQUEST["sc"] == 1)?1:0;

function fileLink($path, $file)
{
   global $rep, $rev, $showchanged;
   
   $isDir = $file{strlen($file) - 1} == "/";

   if ($isDir)
      return "<a href=\"listing.php?rep=$rep&path=$path$file&rev=$rev&sc=$showchanged\">$file</a>";
   else
      return "<a href=\"filedetails.php?rep=$rep&path=$path$file&rev=$rev&sc=$showchanged\">$file</a>";
}

// Make sure that we have a repository
if (!isset($rep))
{
   echo $lang["NOREP"];
   exit;
}

list ($repname, $reppath) = $config->getRepository($rep);
$svnrep = new SVNRepository($reppath);
$contents = $svnrep->dirContents($path, $rev);
$log = $svnrep->getLogDetails($path, $rev);
$youngest = $svnrep->getLogDetails($path);
$youngest = $youngest["rev"];

if ($path == "" || $path{0} != "/")
   $ppath = "/".$path;
else
   $ppath = $path;

$vars["repname"] = $repname;

if ($log["rev"] < $youngest)
   $vars["goheadlink"] = "<a href=\"listing.php?rep=$rep&path=$path&sc=1\">${lang["GOHEAD"]}</a>";
else
   $vars["goheadlink"] = "";

$vars["author"] = $log['author'];
$vars["date"] = $log['date'];
$vars["log"] = $log['message'];
$vars["rev"] = $log["rev"];

if (!$showchanged)
{
   $vars["showchangeslink"] = "<a href=\"listing.php?rep=$rep&path=$path&rev=$rev&sc=1\">${lang["SHOWCHANGED"]}</a>";
   $vars["hidechangeslink"] = "";

   $vars["hidechanges"] = true;
   $vars["showchanges"] = false;
}
else
{
   $vars["showchangeslink"] = "";
   
   $changes = $svnrep->getChangedFiles($rev);

   $first = true;
   $vars["newfiles"] = "";
   foreach ($changes["added"] as $file)
   {
      if (!$first) $vars["newfiles"] .= "<br>";
      $first = false;
      $vars["newfiles"] .= fileLink("", $file);
   }
      
   $first = true;
   $vars["changedfiles"] = "";
   foreach ($changes["updated"] as $file)
   {
      if (!$first) $vars["changedfiles"] .= "<br>";
      $first = false;
      $vars["changedfiles"] .= fileLink("", $file);
   }

   $first = true;
   $vars["deletedfiles"] = "";
   foreach ($changes["deleted"] as $file)
   {
      if (!$first) $vars["changedfiles"] .= "<br>";
      $first = false;
      $vars["changedfiles"] .= $file;
   }

   $vars["hidechangeslink"] = "<a href=\"listing.php?rep=$rep&path=$path&rev=$rev&sc=0\">${lang["HIDECHANGED"]}</a>";
   
   $vars["hidechanges"] = false;
   $vars["showchanges"] = true;
}

$subs = explode("/", $ppath);
$sofar = "";
$count = count($subs);
$vars["curdirlinks"] = "";

for ($n = 0; $n < $count - 2; $n++)
{
   $sofar .= $subs[$n]."/";
   $vars["curdirlinks"] .= "[<a href=\"listing.php?rep=$rep&path=$sofar&rev=$rev&sc=$showchanged\">".$subs[$n]."/]</a> ";
}
$vars["curdirlinks"] .=  "[".$subs[$n]."/]";
$vars["curdirloglink"] = "<a href=\"log.php?rep=$rep&path=$path&rev=$rev&sc=$showchanged&isdir=1\">${lang["VIEWLOG"]}</a>";

$index = 0;
$listing = array();

// Give the user a chance to go back up the tree
if ($ppath != "/")
{
   // Find the parent path (or the whole path if it's already a directory)
   $pos = strrpos(substr($ppath, 0, -1), "/");
   $parent = substr($ppath, 0, $pos + 1);

   $listing[$index]["filelink"] = "<a href=\"listing.php?rep=$rep&path=$parent&rev=$rev&sc=$showchanged\">../</a>";
   $listing[$index]["fileviewloglink"] = "";
   $index++;
}

// List each file in the current directory
$row = 0;
foreach($contents as $file)
{
   $listing[$index]["rowparity"] = "$row";
   $listing[$index]["filelink"] = fileLink($path, $file);

   // The history command doesn't return with a trailing slash.  We need to remember here if the
   // file is a directory or not! 
   
   $isDir = ($file{strlen($file) - 1} == "/"?1:0);
   $listing[$index]["fileviewloglink"] = "<a href=\"log.php?rep=$rep&path=$path$file&rev=$rev&sc=$showchanged&isdir=$isDir\">${lang["VIEWLOG"]}</a>";
   
   $row = 1 - $row;
   $index++;
}

$vars["version"] = $version;
parseTemplate("templates/header.tmpl", $vars, $listing);
parseTemplate("templates/listing.tmpl", $vars, $listing);
parseTemplate("templates/footer.tmpl", $vars, $listing);

?>