<?php
/*
Plugin Name:	EHT Visits
Plugin URI:		http://ociotec.com/index.php/2008/02/19/eht-visits-plugin-para-wordpress/
Description:	This plugin stores all the visits (hits, browser, bot, OS, date, IP, ...), it makes statistics of your web page, and it shows a paginable table view with all the visits (with all their associated hits).
Author:			Emilio Gonz&aacute;lez Monta&ntilde;a
Version:		0.7.2
Author URI:		http://ociotec.com/

History:		0.1		First release.
				0.2		Visits option panel is paginable. The visit info is updated into the header, so you can ask several times for the count (without increasing it). Added some graphics to the Statistics panel in administration options.
				0.2.5	Into URL table, now URL and query are separeted, so the URL visited statistic is not query dependent. Some error fixing. Added search terms statistic.
				0.5		Added referers (no bots) statistic. Added links to Google in search terms statistic. Added widget.
				0.5.5	Added queries count and page load time to hits table (if upgrade, reistall data base, don't need to remove it before), also added these values to visits table and added two new statistic graphs.
				0.6		Added a bars graphic (into statistics panel) to see the visits and hits count per day. Added last 24 hours visits and hits to the widget. Added flag of the country of the visit IP into the widget (also added flags to the plugin).
				0.6.5	Added statistic to the widget of the medium access time between visits & hits (issue #20). Added a configurable limit to make the statistics (issue #21). Modified the statistics of query count and load time to use a bars graphic (issue #22).
				0.7		Fix admin visits table view (issue #38). Changed the hit insertion from the header to the footer (issue #39).
				0.7.1	Error fixing (issue #41, issue #40).
				0.7.2	Error fixing (issue #42, issue #43, issue #44, issue #45).

Setup:
	1) Install the plugin.
	2) Go to the admin menus, and in "Options" panel, select "EHT Visits".
	3) Press "Install DB".
	4) Configure the plugin if you need.
	5) Put the plugin function (optional) anywhere in your template (show the example below) to get the visits counter. Don't use the function EHTVisitsRegistrateVisit, because it's called automatically from the header. 
	6) Add the EHT Visits widget (optional) into the Presentation menu.
	7) If you want country resolution from IPs (optional), you need to install the plugin IP 2 Nation (http://frenchfragfactory.net/ozh/archives/2004/08/27/ip-to-nation-plugin/).

Upgrade:
	1) Go to the admin menus, and in "Options" panel, select "EHT Visits".
	2) Press "Install DB" (this will upgrade database if needed).

Example:
<?php if function_exists ("EHTVisitsPrintCounter") { EHTVisitsPrintCounter (); } ?>

*/

define ("EHT_VISITS_PLUGIN_NAME", "eht-visits");
define ("EHT_VISITS_PLUGIN_TITLE", "EHT Visits");
define ("EHT_VISITS_PLUGIN_URL_BASE", get_option ("siteurl") . "/wp-content/plugins/eht-visits/");
define ("EHT_VISITS_PLUGIN_PATH_BASE", $_SERVER["DOCUMENT_ROOT"] . "/wp-content/plugins/eht-visits/");
define ("EHT_VISITS_PLUGIN_VERSION", "0.7.2");
define ("EHT_VISITS_PLUGIN_URI", "http://ociotec.com/eht-visits-plugin-para-wordpress/");
define ("EHT_VISITS_PLUGIN_AUTHOR", "Emilio Gonz&aacute;lez Monta&ntilde;a");
define ("EHT_VISITS_PLUGIN_SHORT_AUTHOR", "Emilio");
define ("EHT_VISITS_PLUGIN_AUTHOR_URI", "http://ociotec.com");
define ("EHT_VISITS_PLUGIN_DESCRIPTION",
		"Plugin <a href=\"" . EHT_VISITS_PLUGIN_URI . 
		"\" target=\"_blank\">" . EHT_VISITS_PLUGIN_TITLE . 
		" v" . EHT_VISITS_PLUGIN_VERSION . "</a> - " . 
		"Created by <a href=\"" . EHT_VISITS_PLUGIN_AUTHOR_URI . 
		"\" target=\"_blank\">" . EHT_VISITS_PLUGIN_AUTHOR . "</a>");
define ("EHT_VISITS_PLUGIN_SHORT_DESCRIPTION", 
		"<a href=\"" . EHT_VISITS_PLUGIN_URI .
		"\" target=\"_blank\">" . EHT_VISITS_PLUGIN_TITLE . 
		" v" . EHT_VISITS_PLUGIN_VERSION . "</a> " .
		"by <a href=\"" . EHT_VISITS_PLUGIN_AUTHOR_URI . 
		"\" target=\"_blank\">" . EHT_VISITS_PLUGIN_SHORT_AUTHOR . "</a>");
define ("EHT_VISITS_PLUGIN_ULTRA_SHORT_DESCRIPTION", 
		"<a href=\"" . EHT_VISITS_PLUGIN_URI . 
		"\" target=\"_blank\">&copy;</a>");
define ("EHT_VISITS_PLUGIN_FLAGS_URL_BASE", EHT_VISITS_PLUGIN_URL_BASE . "flags/");
define ("EHT_VISITS_PLUGIN_FLAGS_FILE_NAME", "flag_");
define ("EHT_VISITS_PLUGIN_FLAGS_FILE_EXTENSION", ".gif");
define ("EHT_VISITS_PLUGIN_PAGE", EHT_VISITS_PLUGIN_NAME . "-options");
define ("EHT_VISITS_OPTION_VISIT_TIMEOUT", EHT_VISITS_PLUGIN_NAME . "-option-visit-timeout");
define ("EHT_VISITS_OPTION_INITIAL_COUNT", EHT_VISITS_PLUGIN_NAME . "-option-initial-count");
define ("EHT_VISITS_OPTION_VISITS_COUNT", EHT_VISITS_PLUGIN_NAME . "-option-visits-count");
define ("EHT_VISITS_OPTION_HITS_COUNT", EHT_VISITS_PLUGIN_NAME . "-option-hits-count");
define ("EHT_VISITS_OPTION_RESULTS", EHT_VISITS_PLUGIN_NAME . "-option-results");
define ("EHT_VISITS_WIDGET_TITLE", EHT_VISITS_PLUGIN_NAME . "-widget-title");
define ("EHT_VISITS_WIDGET_SHOW_COUNTER", EHT_VISITS_PLUGIN_NAME . "-widget-show-counter");
define ("EHT_VISITS_WIDGET_SUBMIT", EHT_VISITS_PLUGIN_NAME . "-widget-submit");
define ("EHT_VISITS_DEFAULT_VISIT_TIMEOUT", 3600);
define ("EHT_VISITS_MIN_VISIT_TIMEOUT", 60);
define ("EHT_VISITS_MAX_VISIT_TIMEOUT", 86400);
define ("EHT_VISITS_DEFAULT_INITIAL_COUNT", 0);
define ("EHT_VISITS_MIN_INITIAL_COUNT", 0);
define ("EHT_VISITS_MAX_INITIAL_COUNT", 1000000000);
define ("EHT_VISITS_DEFAULT_VISITS_COUNT", 3000);
define ("EHT_VISITS_MIN_VISITS_COUNT", 0);
define ("EHT_VISITS_MAX_VISITS_COUNT", 50000);
define ("EHT_VISITS_DEFAULT_HITS_COUNT", 3000);
define ("EHT_VISITS_MIN_HITS_COUNT", 0);
define ("EHT_VISITS_MAX_HITS_COUNT", 50000);
define ("EHT_VISITS_DEFAULT_RESULTS", 32);
define ("EHT_VISITS_MIN_RESULTS", 8);
define ("EHT_VISITS_MAX_RESULTS", 1024);
define ("EHT_VISITS_DEFAULT_WIDGET_TITLE", "Visits");
define ("EHT_VISITS_FIELD_SUBPAGE", EHT_VISITS_PLUGIN_NAME . "-subpage");
define ("EHT_VISITS_FIELD_OFFSET", EHT_VISITS_PLUGIN_NAME . "-field-offset");
define ("EHT_VISITS_SUBPAGE_GENERAL", "General");
define ("EHT_VISITS_SUBPAGE_STATISTICS", "Statistics");
define ("EHT_VISITS_SUBPAGE_VISITS", "Visits");
define ("EHT_VISITS_FIELD_ACTION", EHT_VISITS_PLUGIN_NAME . "-field-action");
define ("EHT_VISITS_ACTION_INSTALL", "Install DB");
define ("EHT_VISITS_ACTION_UNINSTALL", "Uninstall DB");
define ("EHT_VISITS_ACTION_UPDATE", "Update");
define ("EHT_VISITS_ACTION_RESET", "Reset statistics");
define ("EHT_VISITS_GRAPHIC_PIE", EHT_VISITS_PLUGIN_URL_BASE . "GraphicPie.php");
define ("EHT_VISITS_GRAPHIC_PIE_MAX_COUNT", 10);
define ("EHT_VISITS_GRAPHIC_PIE_WIDTH", 350);
define ("EHT_VISITS_GRAPHIC_PIE_HEIGHT", 200);
define ("EHT_VISITS_GRAPHIC_PIE_BORDER", 20); 
define ("EHT_VISITS_GRAPHIC_PIE_FROM", 0);
define ("EHT_VISITS_GRAPHIC_PIE_MIN_VALUE", 0.5);
define ("EHT_VISITS_GRAPHIC_BARS", EHT_VISITS_PLUGIN_URL_BASE . "GraphicBars.php");
define ("EHT_VISITS_GRAPHIC_BARS_HEIGHT", 300);
define ("EHT_VISITS_GRAPHIC_BARS_BAR_WIDTH", 25);
define ("EHT_VISITS_GRAPHIC_BARS_BORDER", 5);
define ("EHT_VISITS_GRAPHIC_BARS_FONT", EHT_VISITS_PLUGIN_PATH_BASE . "fonts/Verdana.ttf");
define ("EHT_VISITS_GRAPHIC_BARS_LIMIT", 20);
define ("EHT_VISITS_GRAPHIC_TEXT", "000000");
define ("EHT_VISITS_GRAPHIC_TRANSPARENT", "FFFFFF");
define ("EHT_VISITS_GRAPHIC_OTHERS", "Others");
define ("EHT_VISITS_SECONDS_IN_A_DAY", (24 * 60 * 60));
define ("EHT_VISITS_SLASH", strstr (PHP_OS, "WIN") ? "\\" : "/");
define ("EHT_VISITS_MAX_URL_LENGTH", 80);
define ("EHT_VISITS_TABLE_CHECK", "SHOW TABLES LIKE \"%s\";");
define ("EHT_VISITS_TABLE_DROP", "DROP TABLE %s;");
define ("EHT_VISITS_TABLE_COUNT", "SELECT COUNT(*) AS count FROM %s;");
define ("EHT_VISITS_TABLE_VISIT", $wpdb->prefix . "eht_visits_visit");
define ("EHT_VISITS_TABLE_URL", $wpdb->prefix . "eht_visits_url");
define ("EHT_VISITS_TABLE_HIT", $wpdb->prefix . "eht_visits_hit");
define ("EHT_VISITS_TABLE_VISIT_CREATE",
		"CREATE TABLE " . EHT_VISITS_TABLE_VISIT . " (
		  id INT NOT NULL AUTO_INCREMENT,
		  ip VARCHAR (15) NOT NULL,
		  date INT UNSIGNED NOT NULL,
		  referer VARCHAR (256) NOT NULL,
		  browser VARCHAR (25) NOT NULL,
		  bot TINYINT UNSIGNED NOT NULL,
		  os VARCHAR (25) NOT NULL,
		  terms VARCHAR (70) NOT NULL,
		  PRIMARY KEY (id),
		  INDEX ipUnique (ip),
		  INDEX dateUnique (date)
		 );");
define ("EHT_VISITS_TABLE_VISIT_SELECT",
		"SELECT * FROM " . EHT_VISITS_TABLE_VISIT . " ORDER BY date DESC LIMIT %d, %d;");
define ("EHT_VISITS_TABLE_VISIT_SELECT_PREVIOUS",
		"SELECT id FROM " . EHT_VISITS_TABLE_VISIT . " WHERE ip = \"%s\" AND date > %u;");
define ("EHT_VISITS_TABLE_VISIT_SELECT_REFERER",
		"SELECT COUNT(referer) AS count, referer AS name FROM " . EHT_VISITS_TABLE_VISIT . " WHERE referer != \"\" AND terms = \"\" AND id >= %d GROUP BY name ORDER BY count DESC;");
define ("EHT_VISITS_TABLE_VISIT_SELECT_BROWSER",
		"SELECT COUNT(browser) AS count, browser AS name FROM " . EHT_VISITS_TABLE_VISIT . " WHERE browser != \"\" AND bot = 0 AND id >= %d GROUP BY name ORDER BY count DESC;");
define ("EHT_VISITS_TABLE_VISIT_SELECT_OS",
		"SELECT COUNT(os) AS count, os AS name FROM " . EHT_VISITS_TABLE_VISIT . " WHERE os != \"\" AND bot = 0 AND id >= %d GROUP BY name ORDER BY count DESC;");
define ("EHT_VISITS_TABLE_VISIT_SELECT_TERMS",
		"SELECT COUNT(os) AS count, terms AS name FROM " . EHT_VISITS_TABLE_VISIT . " WHERE terms != \"\" AND bot = 0 AND id >= %d GROUP BY name ORDER BY count DESC;");
define ("EHT_VISITS_TABLE_VISIT_SELECT_DATES",
		"SELECT COUNT(id) AS count FROM " . EHT_VISITS_TABLE_VISIT . " WHERE date >= %d AND date <= %d;");
define ("EHT_VISITS_TABLE_VISIT_SELECT_LAST",
		"SELECT id, date FROM " . EHT_VISITS_TABLE_VISIT . " ORDER BY date DESC LIMIT 0, %d;");
define ("EHT_VISITS_TABLE_VISIT_INSERT",
		"INSERT INTO " . EHT_VISITS_TABLE_VISIT . " (ip, date, referer, browser, bot, os, terms) VALUES (\"%s\", %u, \"%s\", \"%s\", %u, \"%s\", \"%s\");");
define ("EHT_VISITS_TABLE_VISIT_UPDATE_DATE",
		"UPDATE " . EHT_VISITS_TABLE_VISIT . " SET date = %u WHERE id = %d;");
define ("EHT_VISITS_TABLE_VISIT_DELETE_ALL",
		"DELETE FROM " . EHT_VISITS_TABLE_VISIT . ";");
define ("EHT_VISITS_TABLE_URL_CREATE",
		"CREATE TABLE " . EHT_VISITS_TABLE_URL . " (
		  id INT NOT NULL AUTO_INCREMENT,
		  url VARCHAR (100) NOT NULL,
		  query VARCHAR (156) NOT NULL,
		  PRIMARY KEY (id),
		  INDEX urlQueryUnique (url, query)
		 );");
define ("EHT_VISITS_TABLE_URL_SELECT",
		"SELECT id FROM " . EHT_VISITS_TABLE_URL . " WHERE url = \"%s\" and query = \"%s\";");
define ("EHT_VISITS_TABLE_URL_INSERT",
		"INSERT INTO " . EHT_VISITS_TABLE_URL . " (url, query) VALUES (\"%s\", \"%s\");");
define ("EHT_VISITS_TABLE_URL_DELETE_ALL",
		"DELETE FROM " . EHT_VISITS_TABLE_URL . ";");
define ("EHT_VISITS_TABLE_HIT_CREATE",
		"CREATE TABLE " . EHT_VISITS_TABLE_HIT . " (
		  id INT NOT NULL AUTO_INCREMENT,
		  visit INT NOT NULL,
		  url INT NOT NULL,
		  date INT UNSIGNED NOT NULL,
		  queriesCount INT UNSIGNED NOT NULL,
		  loadTime INT UNSIGNED NOT NULL,
		  PRIMARY KEY (id),
		  INDEX visitUnique (visit),
		  INDEX urlUnique (url)
		 );");
define ("EHT_VISITS_TABLE_HIT_SELECT",
		"SELECT hit.id AS id, url.url AS url, url.query AS query, hit.date AS date, hit.queriesCount AS queriesCount, hit.loadTime AS loadTime FROM " . EHT_VISITS_TABLE_VISIT . " AS visit, " . EHT_VISITS_TABLE_URL . " AS url, " . EHT_VISITS_TABLE_HIT . " AS hit WHERE visit.id = %d AND visit.id = hit.visit AND hit.url = url.id AND hit.queriesCount > 0 AND hit.loadTime > 0 ORDER BY hit.date DESC;");
define ("EHT_VISITS_TABLE_HIT_SELECT_URL",
		"SELECT COUNT(h.url) AS count, u.url AS name FROM " . EHT_VISITS_TABLE_URL . " AS u, " . EHT_VISITS_TABLE_HIT . " AS h WHERE h.url = u.id AND h.id >= %d AND h.queriesCount > 0 AND h.loadTime > 0 GROUP BY u.url ORDER BY count DESC;");
define ("EHT_VISITS_TABLE_HIT_SELECT_URL_QUERIES_COUNT",
		"SELECT (SUM(h.queriesCount) DIV COUNT(h.url)) AS count, u.url AS name FROM " . EHT_VISITS_TABLE_URL . " AS u, " . EHT_VISITS_TABLE_HIT . " AS h WHERE h.url = u.id AND h.queriesCount > 0 AND h.id >= %d GROUP BY u.url ORDER BY count DESC LIMIT 0, " . EHT_VISITS_GRAPHIC_BARS_LIMIT . ";");
define ("EHT_VISITS_TABLE_HIT_SELECT_URL_LOAD_TIME",
		"SELECT (SUM(h.loadTime) DIV COUNT(h.url)) AS count, u.url AS name FROM " . EHT_VISITS_TABLE_URL . " AS u, " . EHT_VISITS_TABLE_HIT . " AS h WHERE h.url = u.id AND h.loadTime > 0 AND h.id >= %d GROUP BY u.url ORDER BY count DESC LIMIT 0, " . EHT_VISITS_GRAPHIC_BARS_LIMIT . ";");
define ("EHT_VISITS_TABLE_HIT_SELECT_DATES",
		"SELECT COUNT(id) AS count FROM " . EHT_VISITS_TABLE_HIT . " WHERE date >= %d AND date <= %d AND queriesCount > 0 AND loadTime > 0;");
define ("EHT_VISITS_TABLE_HIT_SELECT_LAST",
		"SELECT date FROM " . EHT_VISITS_TABLE_HIT . " WHERE queriesCount > 0 AND loadTime > 0 ORDER BY date DESC LIMIT 0, %d;");
define ("EHT_VISITS_TABLE_HIT_INSERT",
		"INSERT INTO " . EHT_VISITS_TABLE_HIT . " (visit, url, date, queriesCount, loadTime) VALUES (%d, %d, %u, 0, 0);");
define ("EHT_VISITS_TABLE_HIT_UPDATE",
		"UPDATE " . EHT_VISITS_TABLE_HIT . " SET queriesCount = %u, loadTime = %u WHERE id = %d;");
define ("EHT_VISITS_TABLE_HIT_DELETE_ALL",
		"DELETE FROM " . EHT_VISITS_TABLE_HIT . ";");

require_once ("Admin.php");

add_action ("get_header", "EHTVisitsRegistrateVisit");
add_action ("wp_footer", "EHTVisitsRegistrateLoadTime");

$EHTVisitsInfo = array ();

function EHTVisitsRegistrateVisit ()
{
	global $wpdb, $EHTVisitsInfo;
	
	EHTVisitsGetInfo ($EHTVisitsInfo["ip"],
					  $EHTVisitsInfo["now"],
					  $EHTVisitsInfo["lastDate"], 
					  $EHTVisitsInfo["referer"],
					  $EHTVisitsInfo["url"],
					  $EHTVisitsInfo["query"],
					  $EHTVisitsInfo["browser"], 
					  $EHTVisitsInfo["bot"],
					  $EHTVisitsInfo["os"],
					  $EHTVisitsInfo["terms"]);

	$sql = sprintf (EHT_VISITS_TABLE_VISIT_SELECT_PREVIOUS,
					$EHTVisitsInfo["ip"], 
					$EHTVisitsInfo["lastDate"]);
	$row = $wpdb->get_row ($sql);
	if ($row->id == 0)
	{
		$sql = sprintf (EHT_VISITS_TABLE_VISIT_INSERT,
						$EHTVisitsInfo["ip"],
						$EHTVisitsInfo["now"],
						$EHTVisitsInfo["referer"],
						$EHTVisitsInfo["browser"],
						$EHTVisitsInfo["bot"] ? 1 : 0,
						$EHTVisitsInfo["os"], 
						$EHTVisitsInfo["terms"]);
		$wpdb->query ($sql);
		$EHTVisitsInfo["visitId"] = $wpdb->insert_id;
	}
	else
	{
		$EHTVisitsInfo["visitId"] = $row->id;
	}
	
	$EHTVisitsInfo["visits"] = EHTVisitsGetCount (EHT_VISITS_TABLE_VISIT);
	$EHTVisitsInfo["hits"] = EHTVisitsGetCount (EHT_VISITS_TABLE_HIT);
	$EHTVisitsInfo["count"] = get_option (EHT_VISITS_OPTION_INITIAL_COUNT);
	$EHTVisitsInfo["count"] += $EHTVisitsInfo["visits"];
}

function EHTVisitsRegistrateLoadTime ()
{
	global $wpdb, $EHTVisitsInfo;
	
	$EHTVisitsInfo["queriesCount"] = $wpdb->num_queries;
	$EHTVisitsInfo["loadTime"] = (timer_stop (0) * 1000);

	if ($EHTVisitsInfo["visitId"] != 0)
	{
		$sql = sprintf (EHT_VISITS_TABLE_VISIT_UPDATE_DATE,
						$EHTVisitsInfo["now"],
						$EHTVisitsInfo["visitId"]);
		$wpdb->query ($sql);
	}
	
	$sql = sprintf (EHT_VISITS_TABLE_URL_SELECT,
					$EHTVisitsInfo["url"],
					$EHTVisitsInfo["query"]);
	$row = $wpdb->get_row ($sql);
	if ($row->id == 0)
	{
		$sql = sprintf (EHT_VISITS_TABLE_URL_INSERT,
						$EHTVisitsInfo["url"],
						$EHTVisitsInfo["query"]);
		$wpdb->query ($sql);
		$EHTVisitsInfo["urlId"] = $wpdb->insert_id;
	}
	else
	{
		$EHTVisitsInfo["urlId"] = $row->id;
	}

	$sql = sprintf (EHT_VISITS_TABLE_HIT_INSERT,
					$EHTVisitsInfo["visitId"],
					$EHTVisitsInfo["urlId"],
					$EHTVisitsInfo["now"]);
	$wpdb->query ($sql);
	$EHTVisitsInfo["hitId"] = $wpdb->insert_id;
	
	if ($EHTVisitsInfo["hitId"] != 0)
	{
		$sql = sprintf (EHT_VISITS_TABLE_HIT_UPDATE,
						$EHTVisitsInfo["queriesCount"],
						$EHTVisitsInfo["loadTime"],
						$EHTVisitsInfo["hitId"]);
		$wpdb->query ($sql);
	}
}

function EHTVisitsPrintCounter ($echo = true)
{
	global $EHTVisitsInfo;
	
	$number = EHTVisitsFormatNumber ($EHTVisitsInfo["count"]);
	$text = $number . " visits " .
			EHT_VISITS_PLUGIN_ULTRA_SHORT_DESCRIPTION;
	
	if ($echo)
	{
		echo $text;
	}
		
	return ($text);
}

function EHTVisitsGetCount ($table)
{
	global $wpdb;
	
	$sql = sprintf (EHT_VISITS_TABLE_COUNT, $table);
	$row = $wpdb->get_row ($sql);
	$count = $row->count;
	
	return ($count);
}

function EHTVisitsGetInfo (&$ip, &$now, &$lastDate, &$referer, &$url, &$query, &$browser, &$bot, &$os, &$terms)
{
	$ip = mysql_real_escape_string(isset ($_SERVER["HTTP_X_FORWARDED_FOR"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : (isset ($_SERVER["HTTP_CLIENT_IP"]) ? $_SERVER["HTTP_CLIENT_IP"] : $_SERVER["REMOTE_ADDR"]));
	$ip = EHTVisitsCorrectIP ($ip);
	$now = time ();
	$timeout = get_option (EHT_VISITS_OPTION_VISIT_TIMEOUT);
	$lastDate = ($now - $timeout);
	$referer = $_SERVER["HTTP_REFERER"];
	$siteurl = get_option ("siteurl");
	if (strncasecmp ($siteurl, $referer, strlen ($siteurl)) == 0)
	{
		$referer = "";
	}
	$referer = mysql_real_escape_string ($referer);
	$url = $_SERVER["REQUEST_URI"];
	EHTVisitsCorrectEndingSlash ($url);
	$queryString = $_SERVER["QUERY_STRING"];
	$url = mysql_real_escape_string ($url);
	$query = mysql_real_escape_string ($queryString);
	$userAgent = $_SERVER['HTTP_USER_AGENT'];
	$browser = EHTVisitsGetBrowser ($userAgent, $bot);
	$os = EHTVisitsGetOS ($userAgent);
	$terms = mysql_real_escape_string (EHTVisitsGetSearchTerms ($_SERVER["HTTP_REFERER"]));
}

function EHTVisitsGetBrowser ($text, &$bot)
{
	$bot = false;
	$text = strtolower($text);
	if (strpos($text, "opera") !== false) { $browser = "Opera"; }
	else if (strpos ($text, "msie 6.0") !== false) { $browser = "MSIE 6.0"; }
	else if (strpos ($text, "msie 7.0") !== false) { $browser = "MSIE 7.0"; }
	else if (strpos ($text, "yahoofeedseeker") !== false) { $browser = "Yahoo Feed Seeker"; $bot = true; }
	else if (strpos ($text, "msie 5.5") !== false) {$browser = "MSIE 5.5"; }
	else if (strpos ($text, "msie 5.0") !== false) { $browser = "MSIE 5.0"; }
	else if (strpos ($text, "msie 4.") !== false) { $browser = "MSIE 4.0"; }
	else if ((strpos ($text, "gecko") !== false) && (strpos($text, "firebird") !== false)) { $browser = "Mozilla Firebird"; }
	else if ((strpos ($text, "gecko") !== false) && (strpos($text, "firefox") !== false)) { $browser = "Mozilla Firefox"; }
	else if ((strpos ($text, "gecko") !== false) && (strpos($text, "safari") !== false)) { $browser = "Apple Safari"; }
	else if (strpos ($text, "konqueror") !== false) { $browser = "Konqueror"; }
	else if (strpos ($text, "gecko") !== false) { $browser = "Mozilla"; }
	else if (strpos ($text, "mozilla/4.") !== false) { $browser = "Netscape 4.X"; }
	else if (strpos ($text, "mozilla/3.") !== false) { $browser = "Netscape 3.X"; }
	else if (strpos ($text, "trillianpro") !== false) { $browser = "Trillian Pro"; }
	else if (strpos ($text, "feedster") !== false) { $browser = "Feedster"; $bot = true; }
	else if (strpos ($text, "feedrover")) { $browser = "FeedRover"; $bot = true; }
	else if (strpos ($text, "lmspider") !== false) { $browser = "lmspider"; $bot = true; }
	else if (strpos ($text, "googlebot") !== false) { $browser = "Googlebot"; $bot = true; }
	else if (strpos ($text, "technoratibot") !== false) { $browser = "Technoratibot"; $bot = true; }
	else if (strpos ($text, "blo.gs") !== false) { $browser = "blo.gs"; $bot = true; }
	else if (strpos ($text, "obidos-bot") !== false) { $browser = "obidos-bot"; $bot = true; }
	else if (strpos ($text, "blogsnowbot") !== false) { $browser = "blogsnowbot"; $bot = true; }
	else if (strpos ($text, "fresh search") !== false) { $browser = "Fresh Search"; $bot = true; }
	else if (strpos ($text, "larbin") !== false) { $browser = "Larbin"; $bot = true; }
	else if (strpos ($text, "bloglines") !== false) { $browser = "Bloglines"; $bot = true; }
	else if (strpos ($text, "blogpulse") !== false) { $browser = "BlogPulse"; $bot = true; }
	else if (strpos ($text, "feedsucker") !== false) { $browser = $session_id; $bot = true; }
	else if (strpos ($text, "npbot") !== false) { $browser = "NPBot"; $bot = true; }
	else if (strpos ($text, "almaden") !== false) { $browser = "IBM Research Crawler"; $bot = true; }
	else if (strpos ($text, "msnbot") !== false) { $browser = "msnbot"; $bot = true; }
	else if (strpos ($text, "bot") !== false) { $browser = "Bot"; $bot = true; }
	else if (strpos ($text, "feeddemon") !== false) { $browser = "FeedDemon"; $bot = true; }
	else if (strpos ($text, "syndic8") !== false) { $browser = "Syndic8"; $bot = true; }
	else if (strpos ($text, "w3c_validator") !== false) { $browser = "W3C Validator"; $bot = true; }
	else if (strpos ($text, "w3c_css_validator") !== false) { $browser = "W3C CSS Validator"; $bot = true; }
	else if (strpos ($text, "feedfixer") !== false) { $browser = "FeedFixer"; $bot = true; }
	else if (strpos ($text, "feedvalidator") !== false) { $browser = "FeedValidator"; $bot = true; }
	else if ((strpos ($text, "slurp/cat") !== false) || (strpos ($text, "yahoo! slurp") !== false)) { $browser = "Inktomi/Yahoo"; $bot = true; }
	else if (strpos ($text, "fast-webcrawler") !== false) { $browser = "Fast WebCrawler"; $bot = true; }
	else if (strpos ($text, "ask jeeves") !== false) { $browser = "Ask Jeeves"; $bot = true; }
	else if (strpos ($text, "feed") !== false) { $browser = "Some Feed Monger"; $bot = true; }
	else if (strpos ($text, "lynx") !== false) { $browser = "Lynx"; }
	else if (strpos ($text, "mozilla/5.0") !== false) { $browser = "Mozilla 5.0"; }
	else { $browser = "Other"; $bot = true; }
	
	return ($browser);
}

function EHTVisitsGetOS ($text)
{
	$text = strtolower($text);
	if (strpos ($text, "amiga") !== false) { $os = "Amiga OS"; }
	else if (strpos ($text, "windows 3.1") || strpos($text, "win16") || (strpos($text, "win95") && strpos($text, "16bit")) !== false) { $os = "Windows 3.1/3.11"; }
	else if (strpos ($text, "nt 3.51") || strpos($text, "nt3.51") !== false) { $os = "Windows NT 3.51"; }
	else if (strpos ($text, "windows 95") || strpos($text, "win95") !== false) { $os = "Windows 95"; }
	else if (strpos ($text, "windows me") || (strpos($text, "win") && strpos($text, "4.90")) !== false) { $os = "Windows ME"; }
	else if (strpos ($text, "windows 98") || (strpos($text, "win98") !== false) || (strpos($text, "win") && strpos($text, "3.95") !== false)) { $os = "Windows 98"; }
	else if (strpos ($text, "nt 5.0") || strpos($text, "windows 2000") !== false) { $os = "Windows 2000"; }
	else if (strpos ($text, "nt 5.1") || strpos($text, "windows xp") !== false) { $os = "Windows XP"; }
	else if (strpos ($text, "nt 5.2") !== false)  { $os = "Win Server 2003"; }
	else if (strpos ($text, "nt 6.0") !== false)  { $os = "Windows Vista"; }
	else if (strpos ($text, "windows CE") !== false) { $os = "Windows Pocket PC"; }
	else if (strpos ($text, "nt 4") || strpos($text, "nt4") || strpos($text, "winnt") || strpos($text, "windows nt") !== false) { $os = "Windows NT 4.0"; }
	else if (strpos ($text, "windows") !== false) { $os = "Windows"; }
	else if (strpos ($text, "mac os x") !== false) { $os = "Mac OS X"; }
	else if (strpos ($text, "68k") !== false) { $os = "Mac 68K"; }
	else if (strpos ($text, "mac_powerpc") || strpos($text, "ppc") || strpos($text, "macintosh") !== false) { $os = "Mac OS 8/9"; }
	else if (strpos ($text, "linux") !== false) { $os = "Linux"; }
	else if (strpos ($text, "freebsd") !== false) { $os = "FreeBSD"; }
	else if (strpos ($text, "openbsd") !== false) { $os = "OpenBSD"; }
	else if (strpos ($text, "netbsd") !== false) { $os = "NetBSD"; }
	else if (strpos ($text, "beos") !== false) { $os = "BeOS"; }
	else if (strpos ($text, "sunos") || strpos($text, "solaris") !== false) { $os = "Sun Solaris"; }	
	else if (strpos ($text, "qnx") || strpos($text, "photon") !== false) { $os = "QNX"; }
	else if (strpos ($text, "hp-ux") !== false) { $os = "HP-UX"; }
	else if (strpos ($text, "irix") !== false) { $os = "SGI IRIX"; }
	else if (strpos ($text, "aix") || strpos($text, "ibm") !== false) { $os = "IBM AIX"; }
	else if (strpos ($text, "os/2") && strpos($text, "warp") !== false) { $os = "OS/2 Warp"; }
	else if (strpos ($text, "os/2") !== false) { $os = "OS/2"; }
	else if (strpos ($text, "HURD") || (strpos($text, "GNU") && strpos($text, "HURD") !== false)) { $os = "Unix (GNU Hurd)"; }
	else if (strpos ($text, "unix") || strpos($text, "x11") !== false) { $os = "Unix"; }
	else if (strpos ($text, "openssl") !== false) { $os = "openSSL"; }
	else { $os = "Unknown"; }
	
	return ($os);
}

function EHTVisitsGetSearchTerms ($text)
{
	$url = parse_url ($text);
	parse_str ($url['query'], $query);
	if (preg_match("/google\./",$url['host'])) 						{ $terms = $query['q']; }
	else if (preg_match("/lycos\./",$url['host'])) 					{ $terms = $query['query']; }
	else if (preg_match("/need2find\./",$url['host']))			 	{ $terms = $query['searchfor']; }
	else if (preg_match("/mywebsearch\./",$url['host'])) 			{ $terms = $query['searchfor']; }
	else if (preg_match("/alltheweb\./",$url['host'])) 				{ $terms = $query['q']; }
	else if (preg_match("/altavista\./",$url['host'])) 				{ $terms = $query['q']; }
	else if (preg_match("/mongenie\./",$url['host'])) 				{ $terms = $query['Keywords']; }
	else if (preg_match("/yahoo\./",$url['host'])) 					{ $terms = $query['p']; }
	else if (preg_match("/search\.aol\./",$url['host'])) 			{ $terms = $query['query']; }
	else if (preg_match("/search\.live\./",$url['host'])) 			{ $terms = $query['q']; }
	else if (preg_match("/search\.msn\./",$url['host'])) 			{ $terms = $query['q']; }
	else if (preg_match("/search\.latam\.msn\./",$url['host'])) 	{ $terms = $query['q']; }
	else if (preg_match("/search\.prodigy\.msn\./",$url['host'])) 	{ $terms = $query['q']; }
	else if (preg_match("/clarin\./",$url['host'])) 				{ $terms = $query['Busqueda']; }
	else if (preg_match("/wp-plugins\./",$url['host']))				{ $terms = $query['filter']; }
	else															{ $tems = ""; }
	
	return ($terms);
}

function EHTVisitsGenerateFlag ($ip,
								&$text)
{
	$ok = false;
	$text = "";
	$goodIp = EHTVisitsCorrectIP ($ip);
	if (function_exists ("wp_ozh_getCountryCode") &&
	function_exists ("wp_ozh_getCountryName"))
	{
		$countryCode = wp_ozh_getCountryCode (0, $goodIp);
		$countryName = wp_ozh_getCountryName (0, $goodIp);
		if ((!$countryCode) || (!$countryName))
		{
			$countryCode = "lo";
			$countryName = "Unknown";
		}
		$text .= "<img src=\"" . EHT_VISITS_PLUGIN_FLAGS_URL_BASE . EHT_VISITS_PLUGIN_FLAGS_FILE_NAME .
				 (($goodIp == "127.0.0.1") ? "lo" : $countryCode) . 
				 EHT_VISITS_PLUGIN_FLAGS_FILE_EXTENSION . "\" " . 
				 "title=\"" . (($goodIp == "127.0.0.1") ? "Localhost" : $countryName) .
				 "\">";
		$ok = true;
	}
    
	return ($ok);
}

function EHTVisitsCorrectEndingSlash (&$text)
{
	$length = strlen ($text);
	if ($length > 0)
	{
		if ($text[$length - 1] != EHT_VISITS_SLASH)
		{
			$text .= EHT_VISITS_SLASH;
		}
	}
}

function EHTVisitsCorrectIP (&$ip)
{
	$ipBadPosition = strpos ($ip, ",");
	$ip = $ipBadPosition ? substr ($ip, 0, $ipBadPosition) : $ip;

	return ($ip);
}

function EHTVisitsFormatNumber ($number)
{
	$text = "";
	$temp = $number;
	while ($temp > 0)
	{
		$rest = ($temp % 1000);
		$temp = floor ($temp / 1000);
		if ($text != "")
		{
			$text = "." . $text;
		}
		$text = sprintf ((($temp > 0) ? "%03d" : "%d"), $rest) . $text; 
	}
	
	return ($text);
}

function EHTVisitsFormatTime ($time,
							  $withMilliseconds)
{
	$tempTime = $time;
	if ($withMilliseconds)
	{
		$milliseconds = $tempTime % 1000;
		$tempTime = (($tempTime - $milliseconds) / 1000);
	}
	$seconds = $tempTime % 60;
	$tempTime = (($tempTime - $seconds) / 60);
	$minutes = $tempTime % 60;
	$tempTime = (($tempTime - $minutes) / 60);
	$hours = $tempTime;
	
	$text = "";
	if ($hours > 0)
	{
		$text .= $hours . "h";
	}
	if (($hours > 0) || ($minutes > 0))
	{
		$text .= (sprintf ((($hours > 0) ? "%02d" : "%d"), $minutes) . "'");
	}
	if (($hours > 0) || ($minutes > 0) || ($seconds > 0))
	{
		$text .= (sprintf (((($hours > 0) || ($minutes > 0)) ? "%02d" : "%d"), $seconds) . "''");
	}
	if ($withMilliseconds)
	{
		if (($hours > 0) || ($minutes > 0) || ($seconds > 0) || ($milliseconds > 0))
		{
			$text .= (sprintf (((($hours > 0) || ($minutes > 0) || ($seconds > 0)) ? "%03d" : "%d"), $milliseconds) . "ms");
		}
	}
	
	return ($text);
}

function EHTVisitsTimeBetween ($query,
	    					   $limit,
	    					   $formatAsText = true)
{
	global $wpdb;
	
	$timeBetween = 0;
	
	$sql = sprintf ($query, $limit);
	$rows = $wpdb->get_results ($sql);
    $count = count ($rows);
    if ($count > 0)
    {
    	$endDate = $rows[0]->date;
    	$startDate = $rows[$count - 1]->date;
    	if ($endDate > $startDate)
    	{
			$timeBetween = floor (($endDate - $startDate) / ($count - 1));
    	}
    }
    
    if ($formatAsText)
    {
    	$timeBetween = EHTVisitsFormatTime ($timeBetween, false);
    }

    return ($timeBetween);
}

function EHTVisitsGetOption ($optionName,
							 $default = "")
{
	$option = get_option ($optionName);
	if ($option == "")
	{
		$option = $default;
	}
	
	return ($option);
}

?>