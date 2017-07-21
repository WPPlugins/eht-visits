<?php

add_action ("admin_menu", "EHTVisitsAdminAddPages");
add_action ("init", "EHTVisitsRegisterWidget");

require_once (ABSPATH . "wp-admin/includes/upgrade.php");

$graphicColors = array (
	"FF0000", // Red
	"00FF00", // Green
	"0000FF", // Blue
	"FFFF00", // Yellow
	"00FFFF", // Cyan
	"FF00FF", // Magenta
	"FF7F00", // Orange
	"AAD4FF", // Soft blue
	"D4FF7F", // Soft green
	"FFD4FF"  // Pink
);

function EHTVisitsAdminAddPages ()
{
	if (function_exists ("add_options_page"))
	{
		add_options_page (EHT_VISITS_PLUGIN_TITLE, EHT_VISITS_PLUGIN_TITLE, 8, EHT_VISITS_PLUGIN_PAGE, "EHTVisitsAdminOptions");
	}
}

function EHTVisitsRegisterWidget ()
{
	if (function_exists ("register_sidebar_widget")) :
	function EHTVisitsWidget ($arguments)
	{
		global $EHTVisitsInfo, $wpdb;
		
		extract ($arguments);
		
		$count = EHTVisitsFormatNumber ($EHTVisitsInfo["count"]);
		$hits = sprintf ("%.03f", ($EHTVisitsInfo["hits"] / $EHTVisitsInfo["visits"]));
		
		$now = getdate ($EHTVisitsInfo["now"]);
		$oneDayBefore = ($EHTVisitsInfo["now"] - EHT_VISITS_SECONDS_IN_A_DAY);
		$sql = sprintf (EHT_VISITS_TABLE_VISIT_SELECT_DATES, $oneDayBefore, $EHTVisitsInfo["now"]);
		$visitsOneDay = $wpdb->get_var ($sql);
		$sql = sprintf (EHT_VISITS_TABLE_HIT_SELECT_DATES, $oneDayBefore, $EHTVisitsInfo["now"]);
		$hitsOneDay = $wpdb->get_var ($sql);
		if (EHTVisitsGenerateFlag ($EHTVisitsInfo["ip"], $flag))
		{
			$flag = " $flag";
	    }
	    $timeBetweenVisits = EHTVisitsTimeBetween (EHT_VISITS_TABLE_VISIT_SELECT_LAST,
	    										   EHTVisitsGetOption (EHT_VISITS_OPTION_VISITS_COUNT,
	    										   					   EHT_VISITS_DEFAULT_VISITS_COUNT));
	    $timeBetweenHits = EHTVisitsTimeBetween (EHT_VISITS_TABLE_HIT_SELECT_LAST,
	    										 EHTVisitsGetOption (EHT_VISITS_OPTION_HITS_COUNT,
	    										 					 EHT_VISITS_DEFAULT_HITS_COUNT));
    										   
		$title = EHTVisitsGetOption (EHT_VISITS_WIDGET_TITLE, EHT_VISITS_DEFAULT_WIDGET_TITLE);
		$text = $before_widget .
				$before_title . $title . $after_title . "\n" .
				"<div><ul class=\"pagenav\">\n" . 
				"   <li class=\"page_item\">$count visits</li>\n" . 
				"   <li class=\"page_item\">$hits hits per visit</li>\n" .
				"   <li class=\"page_item\">Last 24h: $visitsOneDay visits & $hitsOneDay hits</li>\n" . 
				"   <li class=\"page_item\">One visit each $timeBetweenVisits</li>\n" . 
				"   <li class=\"page_item\">One hit each $timeBetweenHits</li>\n" . 
				"   <li class=\"page_item\">Your IP: " . $EHTVisitsInfo["ip"] . "$flag</li>\n" . 
				"   <li class=\"page_item\">Your browser: " . $EHTVisitsInfo["browser"] . "</li>\n" . 
				"   <li class=\"page_item\">Your OS: " . $EHTVisitsInfo["os"] . "</li>\n" . 
				"</ul>\n" .
				"<small><i>" . EHT_VISITS_PLUGIN_SHORT_DESCRIPTION . "</i></small>\n" .
				"</div>\n" .
				$after_widget;
				
		echo $text;
	}
	
	function EHTVisitsWidgetControl ()
	{
		$title = $newTitle = get_option (EHT_VISITS_WIDGET_TITLE);
		if ($_POST[EHT_VISITS_WIDGET_SUBMIT])
		{
			$newTitle = $_POST[EHT_VISITS_WIDGET_TITLE];
			if ($newTitle == "")
			{
				$newTitle = EHT_VISITS_DEFAULT_WIDGET_TITLE;
			}
		}
		if ($title != $newTitle)
		{
			$title = $newTitle;
			update_option (EHT_VISITS_WIDGET_TITLE, $title);
		}

		if (($title == "") &&
			(!$_POST[EHT_VISITS_WIDGET_SUBMIT]))
		{
			$title = EHT_VISITS_DEFAULT_WIDGET_TITLE;
		}
		$title = htmlspecialchars ($title, ENT_QUOTES);

		echo "<p>\n" .
			 "   <label for=\"" . EHT_VISITS_WIDGET_TITLE . "\">Title\n" .
			 "   <input style=\"width: 250px;\" id=\"". EHT_VISITS_WIDGET_TITLE . "\"".
			 "    name=\"". EHT_VISITS_WIDGET_TITLE . "\" type=\"text\" value=\"$title\" /></label>\n" .
			 "</p>\n" .
			 "<input type=\"hidden\" id=\"". EHT_VISITS_WIDGET_SUBMIT . "\"" .
			 " name=\"". EHT_VISITS_WIDGET_SUBMIT . "\" value=\"1\" />\n";
	}

	register_sidebar_widget (EHT_VISITS_PLUGIN_TITLE, "EHTVisitsWidget", null, EHT_VISITS_PLUGIN_NAME);
	register_widget_control (EHT_VISITS_PLUGIN_TITLE, "EHTVisitsWidgetControl", 300, 75, EHT_VISITS_PLUGIN_NAME);
	
	endif;
}

function EHTVisitsAdminOptions ()
{
	$href = $PHP_SELF . "?page=" . EHT_VISITS_PLUGIN_PAGE . "&" . EHT_VISITS_FIELD_SUBPAGE . "=";
	echo "<div class=\"wrap\">\n" .
		 "<h2>" . EHT_VISITS_PLUGIN_TITLE . "</h2>\n" .
		 "<div id=\"menu\">\n" .
		 "<a href=\"$href" . EHT_VISITS_SUBPAGE_GENERAL . "\">" . EHT_VISITS_SUBPAGE_GENERAL . "</a> &middot;\n" .
		 "<a href=\"$href" . EHT_VISITS_SUBPAGE_STATISTICS . "\">" . EHT_VISITS_SUBPAGE_STATISTICS . "</a> &middot;\n" .
		 "<a href=\"$href" . EHT_VISITS_SUBPAGE_VISITS . "\">" . EHT_VISITS_SUBPAGE_VISITS . "</a>\n" .
		 "</div>\n" .
		 "<br>\n";
	$page = $_REQUEST[EHT_VISITS_FIELD_SUBPAGE];
	switch ($page)
	{
		case EHT_VISITS_SUBPAGE_GENERAL:
		default:
			EHTVisitsAdminSubpageGeneral ($href);
			break;
		case EHT_VISITS_SUBPAGE_STATISTICS:
			EHTVisitsAdminSubpageStatistics ($href);
			break;
		case EHT_VISITS_SUBPAGE_VISITS:
			EHTVisitsAdminSubpageVisits ($href);
			break;
	}
	echo "</div>\n" .
		 "<p align=\"center\">" . EHT_VISITS_PLUGIN_DESCRIPTION . "</p>\n";
}

function EHTVisitsAdminSubpageGeneral ($href)
{
	$action = $_REQUEST[EHT_VISITS_FIELD_ACTION];
	if ($action == EHT_VISITS_ACTION_UPDATE)
	{
		$optionVisitTimeout = $_REQUEST[EHT_VISITS_OPTION_VISIT_TIMEOUT];
		if ($optionVisitTimeout < EHT_VISITS_MIN_VISIT_TIMEOUT)
		{
			echo "<div class=\"error\">The visit timeout $optionVisitTimeout is fewer than the minimum " . EHT_VISITS_MIN_VISIT_TIMEOUT . ", the minimum will be used.</div>\n";
			$optionVisitTimeout = EHT_VISITS_MIN_VISIT_TIMEOUT;
		}
		else if ($optionVisitTimeout > EHT_VISITS_MAX_VISIT_TIMEOUT)
		{
			echo "<div class=\"error\">The visit timeout $optionVisitTimeout is greater than the maximum " . EHT_VISITS_MAX_VISIT_TIMEOUT . ", the maximum will be used.</div>\n";
			$optionVisitTimeout = EHT_VISITS_MAX_VISIT_TIMEOUT;
		}

		$optionInitialCount = $_REQUEST[EHT_VISITS_OPTION_INITIAL_COUNT];
		if ($optionInitialCount < EHT_VISITS_MIN_INITIAL_COUNT)
		{
			echo "<div class=\"error\">The initial count $optionInitialCount is fewer than the minimum " . EHT_VISITS_MIN_INITIAL_COUNT . ", the minimum will be used.</div>\n";
			$optionInitialCount = EHT_VISITS_MIN_INITIAL_COUNT;
		}
		else if ($optionInitialCount > EHT_VISITS_MAX_INITIAL_COUNT)
		{
			echo "<div class=\"error\">The initial count $optionInitialCount is greater than the maximum " . EHT_VISITS_MAX_INITIAL_COUNT . ", the maximum will be used.</div>\n";
			$optionInitialCount = EHT_VISITS_MAX_INITIAL_COUNT;
		}

		$optionVisitsCount = $_REQUEST[EHT_VISITS_OPTION_VISITS_COUNT];
		if ($optionVisitsCount < EHT_VISITS_MIN_VISITS_COUNT)
		{
			echo "<div class=\"error\">The initial count $optionVisitsCount is fewer than the minimum " . EHT_VISITS_MIN_VISITS_COUNT . ", the minimum will be used.</div>\n";
			$optionVisitsCount = EHT_VISITS_MIN_VISITS_COUNT;
		}
		else if ($optionVisitsCount > EHT_VISITS_MAX_VISITS_COUNT)
		{
			echo "<div class=\"error\">The initial count $optionVisitsCount is greater than the maximum " . EHT_VISITS_MAX_VISITS_COUNT . ", the maximum will be used.</div>\n";
			$optionVisitsCount = EHT_VISITS_MAX_VISITS_COUNT;
		}

		$optionHitsCount = $_REQUEST[EHT_VISITS_OPTION_HITS_COUNT];
		if ($optionHitsCount < EHT_VISITS_MIN_HITS_COUNT)
		{
			echo "<div class=\"error\">The initial count $optionHitsCount is fewer than the minimum " . EHT_VISITS_MIN_HITS_COUNT . ", the minimum will be used.</div>\n";
			$optionHitsCount = EHT_VISITS_MIN_HITS_COUNT;
		}
		else if ($optionHitsCount > EHT_VISITS_MAX_HITS_COUNT)
		{
			echo "<div class=\"error\">The initial count $optionHitsCount is greater than the maximum " . EHT_VISITS_MAX_HITS_COUNT . ", the maximum will be used.</div>\n";
			$optionHitsCount = EHT_VISITS_MAX_HITS_COUNT;
		}

		$optionResults = $_REQUEST[EHT_VISITS_OPTION_RESULTS];
		if ($optionResults < EHT_VISITS_MIN_RESULTS)
		{
			echo "<div class=\"error\">The results per page count $optionResults is fewer than the minimum " . EHT_VISITS_MIN_RESULTS . ", the minimum will be used.</div>\n";
			$optionResults = EHT_VISITS_MIN_RESULTS;
		}
		else if ($optionResults > EHT_VISITS_MAX_RESULTS)
		{
			echo "<div class=\"error\">The results per page count $optionResults is greater than the maximum " . EHT_VISITS_MAX_RESULTS . ", the maximum will be used.</div>\n";
			$optionResults = EHT_VISITS_MAX_RESULTS;
		}
	}
	else
	{
		$optionVisitTimeout = get_option (EHT_VISITS_OPTION_VISIT_TIMEOUT);
		$optionInitialCount = get_option (EHT_VISITS_OPTION_INITIAL_COUNT);
		$optionVisitsCount = get_option (EHT_VISITS_OPTION_VISITS_COUNT);
		$optionHitsCount = get_option (EHT_VISITS_OPTION_HITS_COUNT);
		$optionResults = get_option (EHT_VISITS_OPTION_RESULTS);
	}

	if ($optionVisitTimeout == "")
	{
		$optionVisitTimeout = EHT_VISITS_DEFAULT_VISIT_TIMEOUT;
		$action = EHT_VISITS_ACTION_UPDATE;
	}
	if ($optionInitialCount == "")
	{
		$optionInitialCount = EHT_VISITS_DEFAULT_INITIAL_COUNT;
		$action = EHT_VISITS_ACTION_UPDATE;
	}
	if ($optionVisitsCount == "")
	{
		$optionVisitsCount = EHT_VISITS_DEFAULT_VISITS_COUNT;
		$action = EHT_VISITS_ACTION_UPDATE;
	}
	if ($optionHitsCount == "")
	{
		$optionHitsCount = EHT_VISITS_DEFAULT_HITS_COUNT;
		$action = EHT_VISITS_ACTION_UPDATE;
	}
	if ($optionResults == "")
	{
		$optionResults = EHT_VISITS_DEFAULT_RESULTS;
		$action = EHT_VISITS_ACTION_UPDATE;
	}
	
	$firstUse = (($optionVisitTimeout == "") && 
				 ($optionInitialCount == "") && 
				 ($optionVisitsCount == "") && 
				 ($optionHitsCount == "") && 
				 ($optionResults == ""));
			
	if ($action == EHT_VISITS_ACTION_UPDATE)
	{
        update_option (EHT_VISITS_OPTION_VISIT_TIMEOUT, $optionVisitTimeout);
        update_option (EHT_VISITS_OPTION_INITIAL_COUNT, $optionInitialCount);
        update_option (EHT_VISITS_OPTION_VISITS_COUNT, $optionVisitsCount);
        update_option (EHT_VISITS_OPTION_HITS_COUNT, $optionHitsCount);
        update_option (EHT_VISITS_OPTION_RESULTS, $optionResults);
        echo "<div class=\"updated\">The options have been updated.</div>\n";
	}
	else if ($action == EHT_VISITS_ACTION_RESET)
	{
		if (!EHTVisitsReset ($message))
		{
        	echo "<div class=\"error\">Fail to reset the statistics: $message.</div>\n";
		}
		else
		{
        	echo "<div class=\"updated\">The statistics have been reset.</div>\n";
		}
		$optionInitialCount = get_option (EHT_VISITS_OPTION_INITIAL_COUNT);
	}
	else if ($action == EHT_VISITS_ACTION_INSTALL)
	{
		if (!EHTVisitsInstall ($message))
		{
        	echo "<div class=\"error\">Fail to intall the DB: $message.</div>\n";
		}
		else
		{
        	echo "<div class=\"updated\">The plugin data base has been installed.</div>\n";
		}
	}
	else if ($action == EHT_VISITS_ACTION_UNINSTALL)
	{
		if (!EHTVisitsUninstall ($message))
		{
        	echo "<div class=\"error\">Fail to unintall the DB: $message.</div>\n";
		}
		else
		{
        	echo "<div class=\"updated\">The plugin data base has been uninstalled.</div>\n";
		}
	}

	$tables = array (EHT_VISITS_TABLE_VISIT,
					 EHT_VISITS_TABLE_URL,
					 EHT_VISITS_TABLE_HIT);
	foreach ($tables as $table)
	{
		if (!EHTVisitsCheckTable ($table))
		{
			echo "<div class=\"error\">The table \"$table\" is NOT installed, please press the button " . EHT_VISITS_ACTION_INSTALL . ".</div>\n"; 
		}
	}
	
	echo "<form method=\"post\" action=\"" . str_replace( '%7E', '~', $_SERVER['REQUEST_URI']) . "\">\n" .
		 "<p>Visit timeout (in seconds, 3600 = 1 hour) [" . EHT_VISITS_MIN_VISIT_TIMEOUT . ", " . EHT_VISITS_MAX_VISIT_TIMEOUT . "]:<br>\n" .
		 "<input type=\"text\" name=\"" . EHT_VISITS_OPTION_VISIT_TIMEOUT . "\" value=\"$optionVisitTimeout\"></p>\n" .
		 "<p>Initial count [" . EHT_VISITS_MIN_INITIAL_COUNT . ", " . EHT_VISITS_MAX_INITIAL_COUNT . "]:<br>\n" .
		 "<input type=\"text\" name=\"" . EHT_VISITS_OPTION_INITIAL_COUNT . "\" value=\"$optionInitialCount\"></p>\n" .
		 "<p>Visits count to make statistics [" . EHT_VISITS_MIN_VISITS_COUNT . ", " . EHT_VISITS_MAX_VISITS_COUNT . "]:<br>\n" .
		 "<input type=\"text\" name=\"" . EHT_VISITS_OPTION_VISITS_COUNT . "\" value=\"$optionVisitsCount\"></p>\n" .
		 "<p>Hits count to make statistics [" . EHT_VISITS_MIN_HITS_COUNT . ", " . EHT_VISITS_MAX_HITS_COUNT . "]:<br>\n" .
		 "<input type=\"text\" name=\"" . EHT_VISITS_OPTION_HITS_COUNT . "\" value=\"$optionHitsCount\"></p>\n" .
		 "<p>Results per page count [" . EHT_VISITS_MIN_RESULTS . ", " . EHT_VISITS_MAX_RESULTS . "]:<br>\n" .
		 "<input type=\"text\" name=\"" . EHT_VISITS_OPTION_RESULTS . "\" value=\"$optionResults\"></p>\n" .
		 "<p class=\"submit\">\n" .
		 "<input type=\"submit\" name=\"" . EHT_VISITS_FIELD_ACTION . "\" value=\"" . EHT_VISITS_ACTION_INSTALL . "\" onclick=\"return confirm ('Do you really want to install the data base?');\">\n" .
		 "<input type=\"submit\" name=\"" . EHT_VISITS_FIELD_ACTION . "\" value=\"" . EHT_VISITS_ACTION_UNINSTALL . "\" onclick=\"return confirm ('Do you really want to uninstall the data base?');\">\n" .
		 "<input type=\"submit\" name=\"" . EHT_VISITS_FIELD_ACTION . "\" value=\"" . EHT_VISITS_ACTION_RESET . "\" onclick=\"return confirm ('Do you really want to reset the statistics?');\">\n" .
		 "<input type=\"submit\" name=\"" . EHT_VISITS_FIELD_ACTION . "\" value=\"" . EHT_VISITS_ACTION_UPDATE . "\" default>\n" .
		 "</p>\n" .
		 "</form>\n";
}

function EHTVisitsAdminSubpageStatistics ($href)
{
	global $wpdb;

	$text = "";
	
	$initialCount = sprintf ("%d", get_option (EHT_VISITS_OPTION_INITIAL_COUNT));
	$visitCount = sprintf ("%d", EHTVisitsGetCount (EHT_VISITS_TABLE_VISIT));
	$hitCount = sprintf ("%d", EHTVisitsGetCount (EHT_VISITS_TABLE_HIT));
	$urlCount = sprintf ("%d", EHTVisitsGetCount (EHT_VISITS_TABLE_URL));

	$totalCount = $initialCount + $visitCount;
	$hitsPerVisit = sprintf ("%.03f", (($visitCount == 0) ? 0 : ($hitCount / $visitCount)));
	
	$text .= "<h3>General statistics:</h3>\n" .
			 "<table>\n" .
			 "   <tr>\n" .
			 "      <th align=\"left\" class=\"alternate\">\n" .
			 "         Total count:\n" .
			 "      </th>\n" .
			 "      <td>\n" .
			 "         " . EHTVisitsFormatNumber ($totalCount) . "\n" .
			 "      </td>\n" .
			 "   </tr>\n" .
			 "   <tr>\n" .
			 "      <th align=\"left\" class=\"alternate\">\n" .
			 "         Initial count:\n" .
			 "      </th>\n" .
			 "      <td>\n" .
			 "         " . EHTVisitsFormatNumber ($initialCount) . "\n" .
			 "      </td>\n" .
			 "   </tr>\n" .
			 "   <tr>\n" .
			 "      <th align=\"left\" class=\"alternate\">\n" .
			 "         Visits count:\n" .
			 "      </th>\n" .
			 "      <td>\n" .
			 "         " . EHTVisitsFormatNumber ($visitCount) . "\n" .
			 "      </td>\n" .
			 "   </tr>\n" .
			 "   <tr>\n" .
			 "      <th align=\"left\" class=\"alternate\">\n" .
			 "         Hits count:\n" .
			 "      </th>\n" .
			 "      <td>\n" .
			 "         " . EHTVisitsFormatNumber ($hitCount) . "\n" .
			 "      </td>\n" .
			 "   </tr>\n" .
			 "   <tr>\n" .
			 "      <th align=\"left\" class=\"alternate\">\n" .
			 "         Hits per visit:\n" .
			 "      </th>\n" .
			 "      <td>\n" .
			 "         $hitsPerVisit\n" .
			 "      </td>\n" .
			 "   </tr>\n" .
			 "   <tr>\n" .
			 "      <th align=\"left\" class=\"alternate\">\n" .
			 "         URL count:\n" .
			 "      </th>\n" .
			 "      <td>\n" .
			 "         " . EHTVisitsFormatNumber ($urlCount) . "\n" .
			 "      </td>\n" .
			 "   </tr>\n" .
			 "</table>\n" .
			 "<br>\n";

	$sql = sprintf (EHT_VISITS_TABLE_VISIT_SELECT_LAST,
					EHTVisitsGetOption (EHT_VISITS_OPTION_VISITS_COUNT, EHT_VISITS_DEFAULT_VISITS_COUNT));
	$rows = $wpdb->get_results ($sql);
	$count = count ($rows);
	$startVisitsId = (($count > 0) ? $rows[$count - 1]->id : 0);

	$sql = sprintf (EHT_VISITS_TABLE_HIT_SELECT_LAST,
					EHTVisitsGetOption (EHT_VISITS_OPTION_HITS_COUNT, EHT_VISITS_DEFAULT_HITS_COUNT));
	$rows = $wpdb->get_results ($sql);
	$count = count ($rows);
	$startHitsId = (($count > 0) ? $rows[$count - 1]->id : 0);

	$text .= EHTVisitsStatisticsDays ();
	$text .= EHTVisitsStatistic (sprintf (EHT_VISITS_TABLE_VISIT_SELECT_REFERER, $startVisitsId),
								 "Referers");
	$text .= EHTVisitsStatistic (sprintf (EHT_VISITS_TABLE_VISIT_SELECT_TERMS, $startVisitsId),
								 "Search terms", false, true);
	$text .= EHTVisitsStatistic (sprintf (EHT_VISITS_TABLE_HIT_SELECT_URL, $startHitsId),
								 "URLs visited");
	$text .= EHTVisitsStatisticsURLs (sprintf (EHT_VISITS_TABLE_HIT_SELECT_URL_QUERIES_COUNT, $startHitsId),
									  "URLs media queries count");
	$text .= EHTVisitsStatisticsURLs (sprintf (EHT_VISITS_TABLE_HIT_SELECT_URL_LOAD_TIME, $startHitsId),
									  "URLs media load time (in milliseconds)");
	$text .= EHTVisitsStatistic (sprintf (EHT_VISITS_TABLE_VISIT_SELECT_BROWSER, $startVisitsId),
								 "Browsers", false);
	$text .= EHTVisitsStatistic (sprintf (EHT_VISITS_TABLE_VISIT_SELECT_OS, $startVisitsId),
								 "Operative Systems", false);
	
	echo $text;
}

function EHTVisitsAdminSubpageVisits ($href)
{
	global $wpdb;

	$text = "";
	$href .= EHT_VISITS_SUBPAGE_VISITS . "&";
	
	$sql = sprintf (EHT_VISITS_TABLE_COUNT, EHT_VISITS_TABLE_VISIT);
	$row = $wpdb->get_row ($sql);
	$rowCount = $row->count;
	$offset = $_REQUEST[EHT_VISITS_FIELD_OFFSET];
	$offset = (($offset == "") ? 0 : $offset);
	$size = get_option (EHT_VISITS_OPTION_RESULTS);
	if ($size == "")
	{
		$size = EHT_VISITS_DEFAULT_RESULTS;
	}
	$sql = sprintf (EHT_VISITS_TABLE_VISIT_SELECT, $offset, $size);
	$rows = $wpdb->get_results ($sql);
	
	$text .= "<form action=\"none\">\n" .
			 "   Page to show:\n" .
			 "   <select onchange=\"window.location = '$href" . EHT_VISITS_FIELD_OFFSET . "=' + this.options[this.selectedIndex].value;\">\n";
	$pages = floor (($rowCount + $size - 1) / $size);
	for ($i = 0; $i < $pages; $i++)
	{
		$text .= "      <option " . ((($i * $size) == $offset) ? "selected " : "") . "value=\"" . ($i * $size) . "\">" . ($i + 1) . "</option>\n";
	}
	$text .= "   </select>\n" .
			 "</form><br>\n";
	
	$text .= "<b><i>Visits statistics:</i></b><br>\n" .
			 "<table style=\"border-color: black;\" cellspacing=\"0\" border=\"1\" width=\"100%\">\n" .
			 "   <tr>\n" .
			 "      <th>\n" .
			 "         Date:\n" .
			 "      </th>\n" .
			 "      <th>\n" .
			 "         Hits:\n" .
			 "      </th>\n" .
			 "      <th>\n" .
			 "         IP:\n" .
			 "      </th>\n" .
			 "      <th>\n" .
			 "         Browser:\n" .
			 "      </th>\n" .
			 "      <th>\n" .
			 "         OS:\n" .
			 "      </th>\n" .
			 "      <th width=\"100%\" colspan=\"5\">\n" .
			 "         Referer & search terms & hits:\n" .
			 "      </th>\n" .
			 "   </tr>\n";
	$class = "";
	foreach ($rows as $row)
	{
		$class = ("alternate" == $class) ? "" : "alternate";
		
		$referer = EHTVisitsTrunk ($row->referer);
		$date = date ("Y/m/d H:i:s", $row->date);
		$sql = sprintf (EHT_VISITS_TABLE_HIT_SELECT, $row->id);
		$hits = $wpdb->get_results ($sql);
		$hitCount = count ($hits);
		if ($hitCount > 0)
		{
			$ip = EHTVisitsCorrectIP ($row->ip);
			if (EHTVisitsGenerateFlag ($ip, $country))
			{
				$country = "<br>$country";
		    }
			
			$rowSpan = (($row->referer) ? 1 : 0) + (($row->terms) ? 1 : 0) +
					   (($hitCount > 0) ? 1 : 0) + $hitCount;
			
			$text .= "   <tr class=\"$class\" valign=\"top\">\n" .
					 "      <td align=\"center\" rowSpan=\"$rowSpan\">\n" .
					 "         $date\n" .
					 "      </td>\n" .
					 "      <td align=\"center\" rowSpan=\"$rowSpan\">\n" .
					 "         $hitCount\n" .
					 "      </td>\n" .
					 "      <td align=\"center\" rowSpan=\"$rowSpan\">\n" .
					 "         $ip$country\n" .
					 "      </td>\n" .
					 "      <td align=\"center\" rowSpan=\"$rowSpan\">\n" .
					 "         $row->browser\n" .
					 (($row->bot == 1) ? "         <br>\n         <b>Bot</b>\n" : "") .
					 "      </td>\n" .
					 "      <td align=\"center\" rowSpan=\"$rowSpan\">\n" .
					 "         $row->os\n" .
					 "      </td>\n";
			$trEnded = false;
			if ($row->referer)
			{
				$text .= "      <td width=\"100%\" colspan=\"5\">\n" .
						 "         <small><b>Referer:</b> $referer</small><br>\n" .
						 "      </td>\n" .
						 "   </tr>\n";
				$trEnded = true;
			}
			if ($row->terms)
			{
				$text .= ($trEnded ? "   <tr class=\"$class\" valign=\"top\">\n" : "") .
					 	 "      <td width=\"100%\" colspan=\"5\">\n" .
						 "         <small><b>Terms:</b> $row->terms</small><br>\n" .
						 "      </td>\n" .
						 "   </tr>\n";
				$trEnded = true;
			}

			$text .= ($trEnded ? "   <tr class=\"$class\" valign=\"top\">\n" : "") .
				 	 "      <th>\n" .
					 "         Date:\n" .
					 "      </th>\n" .
					 "      <th>\n" .
					 "         Time:\n" .
					 "      </th>\n" .
					 "      <th>\n" .
					 "         Queries:\n" .
					 "      </th>\n" .
					 "      <th>\n" .
					 "         Load:\n" .
					 "      </th>\n" .
					 "      <th width=\"100%\" align=\"left\">\n" .
					 "         URL:\n" .
					 "      </th>\n" .
					 "   </tr>\n";
			$first = true;
			foreach ($hits as $hit)
			{
				$date = date ("H:i:s", $hit->date);
				if ($first)
				{
					$time = "";
					$first = false;
				}
				else
				{
					$time = EHTVisitsFormatTime (($previousDate - $hit->date), false);
				}
				$previousDate = $hit->date;
				$loadTime = EHTVisitsFormatTime ($hit->loadTime, true);
				$url = $hit->url;
				$urlLink = $hit->url;
				if ($hit->query != "")
				{
					$url .= "?" . $hit->query;
					$urlLink .= "?" . $hit->query;
				}
				if (strlen ($url) > EHT_VISITS_MAX_URL_LENGTH)
				{
					$url = substr ($url, 0, EHT_VISITS_MAX_URL_LENGTH) . "...";
				}
	
				$text .= "   <tr class=\"$class\" valign=\"top\">\n" .
				 	 	 "      <td>\n" .
						 "         $date\n" .
						 "      </td>\n" .
						 "      <td align=\"right\">\n" .
						 "         $time\n" .
						 "      </td>\n" .
						 "      <td align=\"center\">\n" .
						 "         " . (($hit->queriesCount != 0) ? $hit->queriesCount : "") . "\n" .
						 "      </td>\n" .
						 "      <td align=\"right\">\n" .
						 "         $loadTime\n" .
						 "      </td>\n" .
						 "      <td width=\"100%\">\n" .
						 "         <small><a href=\"$urlLink\" target=\"_blank\">$url</a></small>\n" .
						 "      </td>\n" .
						 "   </tr>\n";
			}
		}
	}	
	$text .= "</table>\n" .
			 "<br>\n";
			
	echo $text;
}

function EHTVisitsReset (&$message)
{
	global $wpdb;
	
	$ok = true;
	$message = "";
	
	$count = get_option (EHT_VISITS_OPTION_INITIAL_COUNT);
	$count += EHTVisitsGetCount (EHT_VISITS_TABLE_VISIT);

	$sql = sprintf (EHT_VISITS_TABLE_VISIT_DELETE_ALL);
	$wpdb->query ($sql);
	$sql = sprintf (EHT_VISITS_TABLE_URL_DELETE_ALL);
	$wpdb->query ($sql);
	$sql = sprintf (EHT_VISITS_TABLE_HIT_DELETE_ALL);
	$wpdb->query ($sql);
	
	update_option (EHT_VISITS_OPTION_INITIAL_COUNT, $count);
	
	return ($ok);
}

function EHTVisitsCheckTable ($table)
{
	global $wpdb;

	$sql = sprintf (EHT_VISITS_TABLE_CHECK, $table);
	$result = $wpdb->get_var ($sql);
	
	return ($result == $table);
}

function EHTVisitsInstall (&$message)
{
	global $wpdb;
	$tables = array (EHT_VISITS_TABLE_VISIT => EHT_VISITS_TABLE_VISIT_CREATE,
					 EHT_VISITS_TABLE_URL => EHT_VISITS_TABLE_URL_CREATE,
					 EHT_VISITS_TABLE_HIT => EHT_VISITS_TABLE_HIT_CREATE);
	$values = array ();
	
	$ok = true;
	$message = "";
	foreach ($tables as $table => $query)
	{
		dbDelta ($query);
		
		if (!EHTVisitsCheckTable ($table))
		{
			if ($message != "")
			{
				$message .= "<br>\n";
			}
			$message .= "Fail to create the table \"$table\" with query \"$query\"";
			$ok = false;
		}
	}
	if ($ok)
	{
		foreach ($values as $table => $query)
		{
			$wpdb->query ($query);
		}
	}
	
	return ($ok);
}

function EHTVisitsUninstall (&$message)
{
	global $wpdb;
	$tables = array (EHT_VISITS_TABLE_VISIT,
					 EHT_VISITS_TABLE_URL,
					 EHT_VISITS_TABLE_HIT);
	
	$ok = true;
	$message = "";
	foreach ($tables as $table)
	{
		if (!EHTVisitsCheckTable ($table))
		{
			if ($message != "")
			{
				$message .= "<br>\n";
			}
			$message .= "The table to drop \"$table\" doesn't exist";
			$ok = false;
		}
		else
		{
			$query = sprintf (EHT_VISITS_TABLE_DROP, $table);
			$wpdb->query ($query);
			if (EHTVisitsCheckTable ($table))
			{
				if ($message != "")
				{
					$message .= "<br>\n";
				}
				$message .= "Fail to drop the table \"$table\" with query \"$query\"";
				$ok = false;
			}
		}
	}
	
	return ($ok);
}

function EHTVisitsGraphic ($values,
						   $title = "")
{
	global $graphicColors;
	
	$text .= "<h3>$title</h3>\n" .
			 "<table>\n" .
			 "   <tr valign=\"center\">\n" .
			 "      <td>\n";
	
	$count = count ($values);
	$count = (($count + 1) > EHT_VISITS_GRAPHIC_PIE_MAX_COUNT) ? (EHT_VISITS_GRAPHIC_PIE_MAX_COUNT - 1): $count;
	$i = 0;
	$total = 0;
	$othersCount = 0;
	$elements = array ();
	foreach ($values as $value)
	{
		if (($i < $count) && ($value["percentage"] > EHT_VISITS_GRAPHIC_PIE_MIN_VALUE))
		{
			$total += $value["percentage"];
			$elements[$i] = array ();
			$elements[$i]["count"] = $value["count"];
			$elements[$i]["value"] = $value["percentage"];
			$elements[$i]["color"] = $graphicColors[$i];
			$elements[$i]["name"] = $value["name"];
			$i++;
		}
		else
		{
			$othersCount += $value["count"];
		}
	}
	$count = $i;
	if ($othersCount > 0)
	{
		$elements[$count]["count"] = $othersCount;
		$elements[$count]["value"] =  sprintf ("%.03f", (100 - $total));
		$elements[$count]["color"] = $graphicColors[$count];
		$elements[$count]["name"] = EHT_VISITS_GRAPHIC_OTHERS;
	}
	$count = count ($elements);
	
	$text .= "         <img src=\"" . EHT_VISITS_GRAPHIC_PIE .
			 "?width=" . EHT_VISITS_GRAPHIC_PIE_WIDTH . 
			 "&height=" . EHT_VISITS_GRAPHIC_PIE_HEIGHT .
			 "&border=" . EHT_VISITS_GRAPHIC_PIE_BORDER . 
			 "&from=" . EHT_VISITS_GRAPHIC_PIE_FROM . 
			 "&transparent=" . EHT_VISITS_GRAPHIC_TRANSPARENT . 
			 "&count=$count";
	for ($i = 0; $i < $count; $i++)
	{
		$text .= "&color$i=" . $elements[$i]["color"] .
				 "&value$i=" . $elements[$i]["value"]; 
	}
	$text .= "\">\n" .
			 "      </td>\n" .
			 "      <td>\n" .
			 "         &nbsp;&nbsp;&nbsp;&nbsp;\n" .
			 "      </td>\n" .
			 "      <td>\n" .
			 "         <table>";
	for ($i = 0; $i < $count; $i++)
	{
		$text .= "<tr><td>" .
				 "<table style=\"border-color: black; border-style: solid; border-collapse: collapse; background-color: #" . $elements[$i]["color"] . ";\"><tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table> " .
				 "</td><td align=\"right\">" . $elements[$i]["value"] . "%" . 
				 "</td><td align=\"right\">" . EHTVisitsFormatNumber ($elements[$i]["count"]) .
				 "</td><td>" . $elements[$i]["name"] . "</td></tr>\n"; 
	}
	$text .= "         </table>\n" .
			 "      </td>\n" .
			 "   </tr>\n" .
			 "</table>\n";
	
	return ($text);
}

function EHTVisitsTrunk ($text,
						 $makeLink = true,
						 $googleIt = false,
						 $size = EHT_VISITS_MAX_URL_LENGTH)
{
	$trunkedText = $text;
	if (strlen ($text) > $size)
	{
		$trunkedText = substr ($text, 0, $size) . "...";
	}
	$result = $trunkedText;
	if ($makeLink)
	{
		$result = "<a href=\"$text\" target=\"_blank\">$trunkedText</a>";	
	}
	else if ($googleIt)
	{
		$terms = str_replace (" ", "+", $text);
		$result .= " <a href=\"http://www.google.com/search?q=$terms\" target=\"_blank\">(see in Google)</a>";
	}
	
	return ($result);
}

function EHTVisitsMakePorcentages ($rows,
								   $makeLink = true,
								   $googleIt = false)
{
	$total = 0;
	foreach ($rows as $row)
	{
		$total += $row->count;
	}
	$values = array ();
	$i = 0;
	foreach ($rows as $row)
	{
		$values[$i] = array ();
		$values[$i]["count"] = $row->count;
		$values[$i]["percentage"] = sprintf ("%.03f", (($row->count * 100) / $total));
		$values[$i]["name"] = EHTVisitsTrunk ($row->name, $makeLink, $googleIt);
		$i++;
	}
	
	return ($values);
}

function EHTVisitsStatistic ($query,
							 $caption,
							 $makeLink = true,
							 $googleIt = false)
{
	global $wpdb;
	
	$rows = $wpdb->get_results ($query);
	$values = EHTVisitsMakePorcentages ($rows, $makeLink, $googleIt);
	$text = EHTVisitsGraphic ($values, "$caption:");
	
	return ($text);
}

function EHTVisitsStatisticsDays ($days = EHT_VISITS_GRAPHIC_BARS_LIMIT)
{
	global $wpdb, $graphicColors;
	
	$text .= "<h3>Visits & hits per day:</h3>\n" .
			 "<img src=\"" . EHT_VISITS_GRAPHIC_BARS .
			 "?height=" . EHT_VISITS_GRAPHIC_BARS_HEIGHT .
			 "&bar=" . EHT_VISITS_GRAPHIC_BARS_BAR_WIDTH .
			 "&border=" . EHT_VISITS_GRAPHIC_BARS_BORDER .
			 "&font=" . EHT_VISITS_GRAPHIC_BARS_FONT .
			 "&transparent=" . EHT_VISITS_GRAPHIC_TRANSPARENT . 
			 "&text=" . EHT_VISITS_GRAPHIC_TEXT .
			 "&color0=$graphicColors[0]&color1=$graphicColors[1]";
	
	$now = getdate ();
	$startToday = mktime (0, 0, 0, $now["mon"], $now["mday"], $now["year"]);
	$endToday = mktime (23, 59, 59, $now["mon"], $now["mday"], $now["year"]);
	$startToday -= (EHT_VISITS_SECONDS_IN_A_DAY * ($days - 1));
	$endToday -= (EHT_VISITS_SECONDS_IN_A_DAY * ($days - 1));
	for ($i = 0, $empty = true, $realDays = 0; $i < $days; $i++)
	{
		$sql = sprintf (EHT_VISITS_TABLE_VISIT_SELECT_DATES, $startToday, $endToday);
		$visits = $wpdb->get_var ($sql);
		$sql = sprintf (EHT_VISITS_TABLE_HIT_SELECT_DATES, $startToday, $endToday);
		$hits = $wpdb->get_var ($sql);
		$date = getdate ($startToday);
		$day = $date["mday"];
		$month = substr ($date["month"], 0, 3);
		$empty = ($empty && ($visits <= 0) && ($hits <= 0));
		if (!$empty)
		{
			$text .= "&value0-$realDays=$visits&value1-$realDays=$hits&text$realDays=$day $month";
			$realDays++;
		}
		$startToday += EHT_VISITS_SECONDS_IN_A_DAY;
		$endToday += EHT_VISITS_SECONDS_IN_A_DAY;
	}
	$text .= "&x=$realDays" .
			 "&y=2" .
			 "\"><br>\n";
	
	return ($text);
}

function EHTVisitsStatisticsURLs ($query,
								  $tittle)
{
	global $wpdb, $graphicColors;
	
	$text .= "<h3>$tittle:</h3>\n" .
			 "<img src=\"" . EHT_VISITS_GRAPHIC_BARS .
			 "?height=" . EHT_VISITS_GRAPHIC_BARS_HEIGHT .
			 "&bar=" . EHT_VISITS_GRAPHIC_BARS_BAR_WIDTH .
			 "&border=" . EHT_VISITS_GRAPHIC_BARS_BORDER .
			 "&font=" . EHT_VISITS_GRAPHIC_BARS_FONT .
			 "&transparent=" . EHT_VISITS_GRAPHIC_TRANSPARENT . 
			 "&text=" . EHT_VISITS_GRAPHIC_TEXT .
			 "&color0=$graphicColors[0]";
	
	$rows = $wpdb->get_results ($query);
	$count = count ($rows);
	$legend = "<br>\n";
	for ($i = 0; $i < $count; $i++)
	{
		$value = $rows[$i]->count;
		$name = $rows[$i]->name;
		$caption = sprintf ("URL%02d", ($i + 1));
		$text .= "&value0-$i=$value&text$i=$caption";
		$legend .= "$caption <a href=\"$name\" target=\"_blank\">$name</a> ($value)<br>\n";
	}

	$text .= "&x=$count" .
			 "&y=1" .
			 "\"><br>\n" .
			 "$legend";
	
	return ($text);
}

?>