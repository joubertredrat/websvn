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
// wsvn.php
//
// Glue for MultiViews

// --- CONFIGURE THESE VARIABLES ---

// Location of websvn directory via HTTP
//
// e.g.  For http://servername/websvn use /websvn
//
// Note that wsvn.php need not be in the /websvn directory (and normally isn't).
// If you want to use the root server directory, just use a blank string ('').
#$locwebsvnhttp = "/websvn";  
$locwebsvnhttp = '';

// Physical location of websvn directory
#$locwebsvnreal = "d:/websvn";
$locwebsvnreal = '/mnt/wsvn';

chdir($locwebsvnreal);

// --- DON'T CHANGE BELOW HERE ---

// this tells files that we are in multiviews if they are unable to access
// the $config variable
if (!defined('WSVN_MULTIVIEWS'))
   define('WSVN_MULTIVIEWS', 1);

ini_set("include_path", $locwebsvnreal);

require_once("include/setup.php");
require_once("include/svnlook.php");

if (!isset($_REQUEST["sc"]))
   $_REQUEST["sc"] = 1;

if ($config->multiViews)
{
   // If this is a form handling request, deal with it
   if (@$_REQUEST["op"] == "form")
   {
      include("$locwebsvnreal/form.php");
      exit;
   }

   $path = trim(empty($_SERVER['PATH_INFO']) ? $_SERVER['ORIG_PATH_INFO'] : $_SERVER['PATH_INFO']);

   // Remove initial slash
   $path = substr($path, 1);
   if (empty($path))
   {
      include("$locwebsvnreal/index.php");
      exit;
   }
   
   // Split the path into repository and path elements
   // Note: we have to cope with the repository name
   //       having a slash in it

   $found = false;

   foreach ($config->getRepositories() as $rep)
   {
      $pos = strlen($rep->getDisplayName());
      if (strlen($path) < $pos)
         continue;
         
      $name = substr($path, 0, $pos);
      if (strcasecmp($rep->getDisplayName(), $name) == 0)
      {
         $tpath = substr($path, $pos);
         if ($tpath[0] == "/") {
            $found = true;
            $path = $tpath;
            break;
         }
      }
   }

   if ($found == false)
   {
      include("$locwebsvnreal/index.php");
      exit;
   }

   createProjectSelectionForm();
   $vars["allowdownload"] = $rep->getAllowDownload();

   // find the operation type
   $op = @$_REQUEST["op"];
   switch ($op)
   {
      case "dir":
         $file = "listing.php";
         break;
         
      case "file":
         $file = "filedetails.php";
         break;

      case "log":
         $file = "log.php";
         break;

      case "diff":
         $file = "diff.php";
         break;

      case "blame":
         $file = "blame.php";
         break;

      case "rss":
         $file = "rss.php";
         break;
      
      case "dl":
         $file = "dl.php";
         break;

      case "comp":
         $file = "comp.php";
         break;

      default:
         if ($path[strlen($path) - 1] == "/")
            $file = "listing.php";
         else
            $file = "filedetails.php";
   }
   
   // Now include the file that handles it
   include("$locwebsvnreal/$file");
}
else
{
   print "<p>MultiViews must be configured in config.php in order to use this file";
   exit;
}
