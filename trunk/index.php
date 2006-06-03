<?php
# vim:et:ts=3:sts=3:sw=3:fdm=marker:

// WebSVN - Subversion repository viewing via the web using PHP
// Copyright © 2004-2006 Tim Armes, Matt Sicker
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
// index.php
//
// Main page.  Lists all the projects

require_once("include/setup.inc");
require_once("include/svnlook.inc");
require_once("include/template.inc");

$vars["action"] = $lang["PROJECTS"];
$vars["repname"] = "";
$vars["rev"] = 0;
$vars["path"] = "";

// Sort the repositories by group
$config->sortByGroup();

if ($config->flatIndex)
{
   // Create the flat view
   
   $projects = $config->getRepositories();
   $i = 0;
   $listing = array ();
   foreach ($projects as $project)
   {
      if ($project->hasReadAccess("/", true))
      {
         $url = $config->getURL($project, "/", "dir");
      
         $listing[$i]["rowparity"] = $i % 2;
         $listing[$i++]["projlink"] = "<a href=\"${url}sc=0\">".$project->getDisplayName()."</a>";
      }
   } 
   $vars["flatview"] = true;
   $vars["treeview"] = false;   
}
else
{
   // Create the tree view
   
   $projects = $config->getRepositories();
   reset($projects);
   $i = 0;
   $listing = array ();
   $curgroup = NULL;
   $parity = 0;
   foreach ($projects as $project)
   {
      if ($project->hasReadAccess("/", true))
      {
         $listing[$i]["rowparity"] = $parity % 2;
         $url = $config->getURL($project, "/", "dir");
         if ($curgroup != $project->group)
         {
            if (!empty($curgroup))
               $listing[$i]["listitem"] = "</div>\n";  // Close the switchcontent div
            else
               $listing[$i]["listitem"] = "";

            $listing[$i]["isprojlink"] = false;
            $listing[$i]["isgrouphead"] = true;
            
            $curgroup = $project->group;
            $listing[$i++]["listitem"] .= "<div class=\"groupname\" onclick=\"expandcontent(this, '$curgroup');\" style=\"cursor:hand; cursor:pointer\"><div class=\"a\"><span class=\"showstate\"></span>$curgroup</div></div>\n<div id=\"$curgroup\" class=\"switchcontent\">";
         }

         $parity++;       
         $listing[$i]["isgrouphead"] = false;
         $listing[$i]["isprojlink"] = true;
         $listing[$i++]["listitem"] = "<a href=\"${url}sc=0\">".$project->name."</a>\n";
      }
   } 

   if (!empty($curgroup))
   $listing[$i]["isprojlink"] = false;
   $listing[$i]["isgrouphead"] = false;
   $listing[$i]["listitem"] = "</div>";  // Close the switchcontent div

   $vars["flatview"] = false;
   $vars["treeview"] = true;   
   $vars["opentree"] = $config->openTree;
}

$vars["version"] = $version;
parseTemplate($config->getTemplatePath()."header.tmpl", $vars, $listing);
parseTemplate($config->getTemplatePath()."index.tmpl", $vars, $listing);
parseTemplate($config->getTemplatePath()."footer.tmpl", $vars, $listing);

?>
