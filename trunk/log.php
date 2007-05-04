<?php
// WebSVN - Subversion repository viewing via the web using PHP
// Copyright (C) 2004-2006 Tim Armes
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
// log.php
//
// Show the logs for the given path

require_once("include/setup.php");
require_once("include/svnlook.php");
require_once("include/utils.php");
require_once("include/template.php");
require_once("include/bugtraq.php");

$page = (int)@$_REQUEST["page"];
$all = (@$_REQUEST["all"] == 1)?1:0;
$isDir = (@$_REQUEST["isdir"] == 1)?1:0;
$dosearch = (@$_REQUEST["logsearch"] == 1)?1:0;
$search = trim(@$_REQUEST["search"]);
$words = preg_split('#\s+#', $search);
$fromRev = (int)@$_REQUEST["fr"];
$startrev = strtoupper(trim(@$_REQUEST["sr"]));
$endrev = strtoupper(trim(@$_REQUEST["er"]));
$max = @$_REQUEST["max"];

// Max number of results to find at a time
$numSearchResults = 15;

if ($search == "")
   $dosearch = false;   

// removeAccents
//
// Remove all the accents from a string.  This function doesn't seem
// ideal, but expecting everyone to install 'unac' seems a little
// excessive as well...

function removeAccents($string)
{ 
   return strtr($string,
                "�����������������������������������������������������",
                "AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn"); 
} 

// Normalise the search words
foreach ($words as $index => $word)
{
   $words[$index] = strtolower(removeAccents($word));
   
   // Remove empty string introduced by multiple spaces
   if (empty($words[$index]))
      unset($words[$index]);
}

if (empty($page)) $page = 1;

// If searching, display all the results
if ($dosearch) $all = true;

$maxperpage = 20;

// Make sure that we have a repository
if (!isset($rep))
{
   echo $lang["NOREP"];
   exit;
}

$svnrep = new SVNRepository($rep);

$passrev = $rev;

// If there's no revision info, go to the lastest revision for this path
$history = $svnrep->getLog($path, "", "", true);
$youngest = $history->entries[0]->rev;

if (empty($rev))
   $rev = $youngest;

// make sure path is prefixed by a /
$ppath = $path;
if ($path == "" || $path{0} != "/")
   $ppath = "/".$path;

$vars["action"] = $lang["LOG"];
$vars["repname"] = $rep->getDisplayName();
$vars["rev"] = $rev;
$vars["path"] = $ppath;

createDirLinks($rep, $ppath, $passrev, $showchanged);

$logurl = $config->getURL($rep, $path, "log");

if ($rev != $youngest)
   $vars["goyoungestlink"] = "<a href=\"${logurl}sc=1\">${lang["GOYOUNGEST"]}</a>";
else
   $vars["goyoungestlink"] = "";

// We get the bugtraq variable just once based on the HEAD
$bugtraq = new Bugtraq($rep, $svnrep, $ppath);

if ($startrev != "HEAD") $startrev = (int)$startrev;
if (empty($startrev)) $startrev = $rev;
if (empty($endrev)) $endrev = 1;

if (empty($_REQUEST["max"]))
{
   if (empty($_REQUEST["logsearch"]))
      $max = 30;
   else
      $max = 0;
}
else
{
   $max = (int)$max;
   if ($max < 0) $max = 30;
}

$history = $svnrep->getLog($path, $startrev, $endrev, true, $max);
$vars["logsearch_moreresultslink"] = "";
$vars["pagelinks"] = "";
$vars["showalllink"] = "";
$listing = array();

if (!empty($history))
{
   // Get the number of separate revisions
   $revisions = count($history->entries);
   
   if ($all)
   {
      $firstrevindex = 0;
      $lastrevindex = $revisions - 1;
      $pages = 1;
   }
   else
   {
      // Calculate the number of pages
      $pages = floor($revisions / $maxperpage);
      if (($revisions % $maxperpage) > 0) $pages++;
      
      if ($page > $pages) $page = $pages;
      
      // Word out where to start and stop
      $firstrevindex = ($page - 1) * $maxperpage;
      $lastrevindex = $firstrevindex + $maxperpage - 1;
      if ($lastrevindex > $revisions - 1) $lastrevindex = $revisions - 1;
   }
   
   $history = $svnrep->getLog($path, $history->entries[$firstrevindex ]->rev,  $history->entries[$lastrevindex]->rev, false, 0);
   
   $row = 0;
   $index = 0;
   $listing = array();
   $found = false;
   
   foreach ($history->entries as $r)
   {
      // Assume a good match
      $match = true;
      $thisrev = $r->rev;
         
      // Check the log for the search words, if searching
      if ($dosearch)
      {
         if ((empty($fromRev) || $fromRev > $thisrev))
         {
            // Turn all the HTML entities into real characters.  
            
            // Make sure that each word in the search in also in the log
            foreach($words as $word)
            {
               if (strpos(strtolower(removeAccents($r->msg)), $word) === false)
               {
                  $match = false;
                  break;
               }
            }
            
            if ($match)
            {
               $numSearchResults--;
               $found = true;
            }
         }
         else
            $match = false;
      }
      
      if ($match)
      {
         // Add the trailing slash if we need to (svnlook history doesn't return trailing slashes!)
         $rpath = $r->path;
   
         if (empty($rpath))
            $rpath = "/";
         else if ($isDir && $rpath{strlen($rpath) - 1} != "/")
            $rpath .= "/";
      
         // Find the parent path (or the whole path if it's already a directory)
         $pos = strrpos($rpath, "/");
         $parent = substr($rpath, 0, $pos + 1);
      
         $url = $config->getURL($rep, $parent, "dir");
         $listing[$index]["revlink"] = "<a href=\"${url}rev=$thisrev&amp;sc=1\">$thisrev</a>";
      
         if ($isDir)
         {
            $listing[$index]["compare_box"] = "<input type=\"checkbox\" name=\"compare[]\" value=\"$parent@$thisrev\" onclick=\"checkCB(this)\" />";
            $url = $config->getURL($rep, $rpath, "dir"); 
            $listing[$index]["revpathlink"] = "<a href=\"${url}rev=$thisrev&amp;sc=$showchanged\">$rpath</a>";
         }
         else
         {
            $listing[$index]["compare_box"] = "<input type=\"checkbox\" name=\"compare[]\" value=\"$rpath@$thisrev\" onclick=\"checkCB(this)\" />";
            $url = $config->getURL($rep, $rpath, "file"); 
            $listing[$index]["revpathlink"] = "<a href=\"${url}rev=$thisrev&amp;sc=$showchanged\">$rpath</a>";
         }
         
         $listing[$index]["revauthor"] = $r->author;
         $listing[$index]["revage"] = $r->age;
         $listing[$index]["revlog"] = nl2br($bugtraq->replaceIDs(create_anchors($r->msg)));
         $listing[$index]["rowparity"] = "$row";
         
         $row = 1 - $row;
         $index++;
      }
      
      // If we've reached the search limit, stop here...
      if (!$numSearchResults)
      {
         $url = $config->getURL($rep, $path, "log");
         $vars["logsearch_moreresultslink"] = "<a href=\"${url}rev=$rev&amp;sc=$showchanged&amp;isdir=$isDir&amp;logsearch=1&amp;search=$search&amp;fr=$thisrev\">${lang["MORERESULTS"]}</a>";         
         break;
      }         
   }
   
   $vars["logsearch_resultsfound"] = true;
   
   if ($dosearch && !$found)
   {
      if ($fromRev == 0)
      {
         $vars["logsearch_nomatches"] = true;
         $vars["logsearch_resultsfound"] = false;
      }
      else
         $vars["logsearch_nomorematches"] = true;
   }
   else if ($dosearch && $numSearchResults > 0)
   {
      $vars["logsearch_nomorematches"] = true;
   }
   
   // Work out the paging options
      
   if ($pages > 1)
   {
      $prev = $page - 1;
      $next = $page + 1;
      echo "<p><center>";
         
      if ($page > 1) $vars["pagelinks"] .= "<a href=\"${logurl}rev=$rev&amp;sr=$startrev&amp;er=$endrev&amp;sc=$showchanged&amp;max=$max&amp;page=$prev\"><&nbsp;${lang["PREV"]}</a> ";
      for ($p = 1; $p <= $pages; $p++)
      {
         if ($p != $page)
            $vars["pagelinks"].= "<a href=\"${logurl}rev=$rev&amp;sr=$startrev&amp;er=$endrev&amp;sc=$showchanged&amp;max=$max&amp;page=$p\">$p</a> "; 
         else
            $vars["pagelinks"] .= "<b>$p </b>";
      }
      if ($page < $pages) $vars["pagelinks"] .=" <a href=\"${logurl}rev=$rev&amp;sr=$startrev&amp;er=$endrev&amp;sc=$showchanged&amp;max=$max&amp;page=$next\">${lang["NEXT"]}&nbsp;></a>";   
      
      $vars["showalllink"] = "<a href=\"${logurl}rev=$rev&amp;sr=$startrev&amp;er=$endrev&amp;sc=$showchanged&amp;all=1&amp;max=$max\">${lang["SHOWALL"]}</a>";
      echo "</center>";
   }
}

// Create the project change combo box
 
$url = $config->getURL($rep, $path, "log");
# XXX: forms don't have the name attribute, but _everything_ has the id attribute,
#      so what you're trying to do (if anything?) should be done via that ~J
$vars["logsearch_form"] = "<form action=\"$url\" method=\"post\" name=\"logsearchform\">";

$vars["logsearch_startbox"] = "<input name=\"sr\" size=\"5\" value=\"$startrev\" />";
$vars["logsearch_endbox"  ] = "<input name=\"er\" size=\"5\" value=\"$endrev\" />";
$vars["logsearch_maxbox"  ] = "<input name=\"max\" size=\"5\" value=\"".($max==0?"":$max)."\" />";
$vars["logsearch_inputbox"] = "<input name=\"search\" value=\"$search\" />";

$vars["logsearch_submit"] = "<input type=\"submit\" value=\"${lang["GO"]}\" />";
$vars["logsearch_endform"] = "<input type=\"hidden\" name=\"logsearch\" value=\"1\" />".
                             "<input type=\"hidden\" name=\"op\" value=\"log\" />".
                             "<input type=\"hidden\" name=\"rev\" value=\"$rev\" />".
                             "<input type=\"hidden\" name=\"sc\" value=\"$showchanged\" />".
                             "<input type=\"hidden\" name=\"isdir\" value=\"$isDir\" />".
                             "</form>";   

$url = $config->getURL($rep, $path, "log");
$vars["logsearch_clearloglink"] = "<a href=\"${url}rev=$rev&amp;sc=$showchanged&amp;isdir=$isDir\">${lang["CLEARLOG"]}</a>";

$url = $config->getURL($rep, "/", "comp");
$vars["compare_form"] = "<form action=\"$url\" method=\"post\" name=\"compareform\">";
$vars["compare_submit"] = "<input name=\"comparesubmit\" type=\"submit\" value=\"${lang["COMPAREREVS"]}\" />";
$vars["compare_endform"] = "<input type=\"hidden\" name=\"op\" value=\"comp\" /><input type=\"hidden\" name=\"sc\" value=\"$showchanged\" /></form>";   

$vars["version"] = $version;

if (!$rep->hasReadAccess($path, false))
   $vars["noaccess"] = true;

parseTemplate($rep->getTemplatePath()."header.tmpl", $vars, $listing);
parseTemplate($rep->getTemplatePath()."log.tmpl", $vars, $listing);
parseTemplate($rep->getTemplatePath()."footer.tmpl", $vars, $listing);

?>
