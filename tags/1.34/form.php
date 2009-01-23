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
// form.php
//
// Handling of WebSVN forms

require("include/setup.inc");

// Generic redirect handling

function redirect($loc)
{
   $url = "http://${_SERVER["HTTP_HOST"]}$loc";
   header("Location: $url");
   echo "<html><script language=\"JavaScript\" type=\"text/JavaScript\">window.location = \"$url\"</script></html>";
}

// Handle project selection

if (@$_REQUEST["selectproj"])
{
   $rep = (int)@$_REQUEST["rep"];
   
   $basedir = dirname($_SERVER["PHP_SELF"]);
   $url = $config->getURL($rep, "/", "dir");
   
   if ($config->multiViews)
      redirect($url);
   else
      redirect($basedir."/".$url);   
}


?>