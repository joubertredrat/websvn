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
// filedetails.php
//
// Simply lists the contents of a file

require_once('include/setup.php');
require_once('include/svnlook.php');
require_once('include/utils.php');
require_once('include/template.php');

// Make sure that we have a repository
if ($rep) {
$svnrep = new SVNRepository($rep);

if ($path{0} != '/') {
  $ppath = '/'.$path;
} else {
  $ppath = $path;
}

$passrev = $rev;
$useMime = false;

// If there's no revision info, go to the lastest revision for this path
$history = $svnrep->getLog($path, '', '', false, 2, $peg);
$youngest = ($history && isset($history->entries[0])) ? $history->entries[0]->rev: false;

if (empty($rev)) {
  $rev = $youngest;
} else if ($rev > $youngest) {
  $vars['warning'] = 'Revision '.$rev.' of this resource does not exist.';
}

$extn = strtolower(strrchr($path, '.'));

// Check to see if the user has requested that this type be zipped and sent
// to the browser as an attachment

if (in_array($extn, $zipped) && $rep->hasReadAccess($path, false)) {
  $base = basename($path);
  header('Content-Type: application/x-gzip');
  header('Content-Disposition: attachment; filename='.urlencode($base).'.gz');

  // Get the file contents and pipe into gzip.  All this without creating
  // a temporary file.  Damn clever.
  $svnrep->getFileContents($path, '', $rev, '| '.$config->gzip.' -n -f');
  exit;
}

// Check to see if we should serve it with a particular content-type.
// The content-type could come from an svn:mime-type property on the
// file, or from the $contentType array in setup.php.

if (!$rep->getIgnoreSvnMimeTypes()) {
  $svnMimeType = $svnrep->getProperty($path, 'svn:mime-type', $rev);
}

if (!$rep->getIgnoreWebSVNContentTypes()) {
  $setupContentType = @$contentType[$extn];
}

// Use the documented priorities when establishing what content-type to use.
if (!empty($svnMimeType) && $svnMimeType != 'application/octet-stream') {
  $mimeType = $svnMimeType;
} else if (!empty($setupContentType)) {
  $mimeType = $setupContentType;
} else if (!empty($svnMimeType)) {
  $mimeType = $svnMimeType; // Use SVN's default of 'application/octet-stream'
} else {
  $mimeType = '';
}

$useMime = ($mimeType) ? @$_REQUEST['usemime'] : false;
if (!empty($mimeType) && !$useMime) {
  $useMime = $mimeType; // Save MIME type for later before possibly clobbering
  // If a MIME type exists but is set to be ignored, set it to an empty string.
  foreach ($config->inlineMimeTypes as $inlineType) {
    if (preg_match('|'.$inlineType.'|', $mimeType)) {
      $mimeType = '';
      break;
    }
  }
}

// If a MIME type is associated with the file, deliver with Content-Type header.
if (!empty($mimeType) && $rep->hasReadAccess($path, false)) {
  $base = basename($path);
  header('Content-Type: '.$mimeType);
  //header('Content-Length: '.$size);
  header('Content-Disposition: inline; filename='.urlencode($base));
  $svnrep->getFileContents($path, '', $rev);
  exit;
}

// Display the file inline using WebSVN.

if ($rev != $youngest) {
  $url = $config->getURL($rep, $path, 'file');
  $vars['goyoungestlink'] = '<a href="'.$url.'">'.$lang['GOYOUNGEST'].'</a>';
}

$vars['action'] = '';
$vars['path'] = htmlentities($ppath, ENT_QUOTES, 'UTF-8');

if ($history) {
  $vars['log'] = $history->entries[0]->msg;
  $vars['date'] = $history->entries[0]->date;
  $vars['author'] = $history->entries[0]->author;
}
createDirLinks($rep, $ppath, $passrev, $peg);
$passRevString = ($passrev) ? 'rev='.$passrev : '';
if ($peg)
  $passRevString .= '&amp;peg='.$peg;

$vars['indexurl'] = $config->getURL($rep, '', 'index');

$url = $config->getURL($rep, $path, 'blame').$passRevString;
$vars['blamelink'] = '<a href="'.$url.'">'.$lang['BLAME'].'</a>';

$url = $config->getURL($rep, $path, 'log').$passRevString;
$vars['loglink'] = '<a href="'.$url.'">'.$lang['VIEWLOG'].'</a>';

if ($history == null || sizeof($history->entries) > 1) {
  $url = $config->getURL($rep, $path, 'diff').$passRevString;
  $vars['difflink'] = '<a href="'.$url.'">'.$lang['DIFFPREV'].'</a>';
}

if ($rep->isDownloadAllowed($path)) {
  $url = $config->getURL($rep, $path, 'dl').$passRevString;
  $vars['downloadlink'] = '<a href="'.$url.'">'.$lang['DOWNLOAD'].'</a>';
}

if ($rep->getHideRss()) {
  $url = $config->getURL($rep, $path, 'rss');
  $vars['rssurl'] = $url;
  $vars['rsslink'] = '<a href="'.$url.'">'.$lang['RSSFEED'].'</a>';
}
  
$mimeType = $useMime; // Restore preserved value to use for 'mimelink' variable.
// If there was a MIME type, create a link to display file with that type.
if ($mimeType && !isset($vars['warning'])) {
  $url = $config->getURL($rep, $path, 'file').'usemime=1&amp;'.$passRevString;
  $vars['mimelink'] = '<a href="'.$url.'">View as '.$mimeType.'</a>';
}

}
$vars['rev'] = htmlentities($rev, ENT_QUOTES, 'UTF-8');
$vars['repurl'] = $config->getURL($rep, '', 'dir');

if (!$rep->hasReadAccess($path, true)) {
  $vars['error'] = $lang['NOACCESS'];
}


$listing = array();
// $listing is populated with file data when file.tmpl calls [websvn-getlisting]

$vars['template'] = 'file';
$template = ($rep) ? $rep->getTemplatePath() : $config->templatePath;
parseTemplate($template.'header.tmpl', $vars, $listing);
parseTemplate($template.'file.tmpl', $vars, $listing);
parseTemplate($template.'footer.tmpl', $vars, $listing);
