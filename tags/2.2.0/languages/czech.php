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
// czech.php
//
// Czech language strings

// The language name is displayed in the drop down box.  It MUST be encoded as Unicode (no HTML entities).
$lang["LANGUAGENAME"] = "Česky";
// This is the RFC 2616 (§3.10) language tag that corresponds to this translation
// see also RFC 4646
$lang['LANGUAGETAG'] = 'cs';

$lang["LOG"] = "Log";
$lang["DIFF"] = "Diff";

$lang["NOREP"] = "Není určen žádný repozitář";
$lang["NOPATH"] = "Cesta nebyla nalezena";
$lang["NOACCESS"] = "Nemáte dostatečná přístupová práva pro čtení adresáře";
$lang["RESTRICTED"] = "Omezený přístup";
$lang["SUPPLYREP"] = "Nastavte prosím cestu k repozitáři v include/config.php pomocí \$config->parentPath nebo \$config->addRepository<p>Podívejte se do insatlační příručky pro podrobnější informace";

$lang["DIFFREVS"] = "Rozdíly mezi revizemi";
$lang["AND"] = "a";
$lang["REV"] = "Revevize";
$lang["LINE"] = "Line";
$lang["SHOWENTIREFILE"] = "Zobraz celý soubor";
$lang["SHOWCOMPACT"] = "Zobraz pouze rozdílné části";

$lang["DIFFPREV"] = "Porovnej s předchozí";
$lang["BLAME"] = "Blame";

$lang["REVINFO"] = "Informace o revizi";
$lang["GOYOUNGEST"] = "Přejdi na současnou revizi";
$lang["LASTMOD"] = "Poslední změna";
$lang["LOGMSG"] = "Záznam";
$lang["CHANGES"] = "Změny";
$lang["SHOWCHANGED"] = "Zobraz změněné soubory";
$lang["HIDECHANGED"] = "Schovej změněné soubory";
$lang["NEWFILES"] = "Nové soubory";
$lang["CHANGEDFILES"] = "Změněné soubory";
$lang["DELETEDFILES"] = "Smazané soubory";
$lang["VIEWLOG"] = "Ukaž";
$lang["PATH"] = "Cesta";
$lang["AUTHOR"] = "Autor";
$lang["AGE"] = "Stáří";
$lang["LOG"] = "Záznam";
$lang["CURDIR"] = "Aktuální adresář";
$lang["TARBALL"] = "Tarball";

$lang["PREV"] = "Předchozí";
$lang["NEXT"] = "Následující";
$lang["SHOWALL"] = "Ukaž všechny";

$lang["BADCMD"] = "Nepodařilo se spustit tento příkaz";
$lang["UNKNOWNREVISION"] = "Revize nebyla nalezena";

$lang["POWERED"] = "Poháněno <a href=\"http://www.websvn.info/\">WebSVN</a>";
$lang["PROJECTS"] = "Subversion&nbsp;Repozitáře";
$lang["SERVER"] = "Subversion&nbsp;Servery";

$lang["FILTER"] = "Nastavení filtrování";
$lang["STARTLOG"] = "Od revize";
$lang["ENDLOG"] = "Do revize";
$lang["MAXLOG"] = "Max revizí";
$lang["SEARCHLOG"] = "Hledat";
$lang["CLEARLOG"] = "Zruš aktuální filtr";
$lang["MORERESULTS"] = "Najdi další...";
$lang["NORESULTS"] = "Nejsou tu žádné zázanmy odpovídající vašim požadavkům";
$lang["NOMORERESULTS"] = "Nejsou tu žádné další záznamy odpovídající vašim požadavkům";
$lang['NOPREVREV'] = 'Není předchozí revize.';

$lang["RSSFEEDTITLE"] = "WebSVN RSS feed";
$lang["FILESMODIFIED"] = "soubor(y) změněn(y)";
$lang["RSSFEED"] = "RSS";

$lang["LINENO"] = "Číslo řádky";
$lang["BLAMEFOR"] = "Blame information for rev";

$lang["DAYLETTER"] = "d";
$lang["HOURLETTER"] = "h";
$lang["MINUTELETTER"] = "m";
$lang["SECONDLETTER"] = "s";

$lang["GO"] = "Go";

$lang["PATHCOMPARISON"] = "Porovnání cest";
$lang["COMPAREPATHS"] = "Porovnej cesty";
$lang["COMPAREREVS"] = "Porovnej revize";
$lang["PROPCHANGES"] = "Změněné vlastnosti:";
$lang["CONVFROM"] = "Toto porovnání ukazuje změny pro převedení";
$lang["TO"] = "na";
$lang["REVCOMP"] = "Reverzní porovnání";
$lang["COMPPATH"] = "Porovnej cestu:";
$lang["WITHPATH"] = "S umístěním:";
$lang["FILEDELETED"] = "Soubor smazán";
$lang["FILEADDED"] = "Nový soubor";

// The following are defined by some languages to stop unwanted line splitting
// in the template files.

$lang["NOBR"] = "";
$lang["ENDNOBR"] = "";

// $lang["NOBR"] = "<nobr>";
// $lang["ENDNOBR"] = "</nobr>";
