<?php
/*
 *  index.php
 *  gitphp: A PHP git repository browser
 *  Component: Index script
 *
 *  Copyright (C) 2006 Christopher Han <xiphux@gmail.com>
 *  Copyright (C) 2010 Michael Vigovsky <xvmv@mail.ru>
 */

 /*
  * Version
  */
 include_once('include/version.php');

 /*
  * Constants
  */
 require_once('include/defs.constants.php');

 /*
  * Configuration
  */
 require_once('config/gitphp.conf.php');

 $project = null;

 if (isset($_GET['p'])) {
	$fullpath = $gitphp_conf['projectroot'] . $_GET['p'];
	$realprojroot = $gitphp_conf['projectroot'];
	$pathpiece = substr($fullpath, 0, strlen($realprojroot));
	if (strcmp($pathpiece, $realprojroot) === 0) {
		$project = str_replace(chr(0), '', $_GET['p']);
	}
 }

 $extraoutput = FALSE;

 /*
  * Instantiate Smarty
  */
 require_once($gitphp_conf['smarty_prefix'] . "Smarty.class.php");
 $tpl = new Smarty;
 if ((!isset($_GET['a'])) || (
     	($_GET['a'] != "commitdiff_plain") &&
     	($_GET['a'] != "blob_plain") &&
     	($_GET['a'] != "blobdiff_plain") &&
     	($_GET['a'] != "rss") &&
     	($_GET['a'] != "opml") &&
	($_GET['a'] != "snapshot"))) {
	$tpl->load_filter('output','trimwhitespace');
	$extraoutput = TRUE;
}

 /*
  * Debug
  */
 if ($gitphp_conf['debug']) {
 	if ($extraoutput) {
		define('GITPHP_START_TIME', microtime(true));
		error_reporting(E_ALL|E_STRICT);
	}
 }

/*
 * Development
 */
 if (!(isset($gitphp_conf['dev']) && $gitphp_conf['dev'])) {
 	$tpl->compile_check = false;
 }

/*
 * Caching
 */
 if ($gitphp_conf['cache']) {
 	$tpl->caching = 2;
	if (isset($gitphp_conf['cachelifetime']))
		$tpl->cache_lifetime = $gitphp_conf['cachelifetime'];
	if (!(isset($gitphp_conf['cacheexpire']) && ($gitphp_conf['cacheexpire'] === FALSE))) {
		require_once('include/cache.cache_expire.php');
		cache_expire($gitphp_conf['projectroot'], $project, (isset($git_projects) ? $git_projects : null));
	}
 }

/*
 * Setup global assigns used everywhere (such as header/footer)
 */
 $tpl->assign("stylesheet",$gitphp_conf['stylesheet']);
 $tpl->assign("version",$gitphp_version);
 $tpl->assign("pagetitle",$gitphp_conf['title']);
  $tpl->assign("maintitle",$gitphp_conf['maintitle']);
 if ($project) {
	$tpl->assign("validproject",TRUE);
	$tpl->assign("project",$project);
	require_once('include/gitutil.git_project_descr.php');
	$tpl->assign("projectdescription",git_project_descr($gitphp_conf['projectroot'],$project));
	if (isset($_GET['a'])) {
		$tpl->assign("action",$_GET['a']);
		$tpl->assign("validaction", TRUE);
	}
 }
 if (isset($_GET['st']))
 	$tpl->assign("currentsearchtype",$_GET['st']);
 else
	$tpl->assign("currentsearchtype","commit");
if (isset($_GET['s']))
	$tpl->assign("currentsearch",$_GET['s']);
if (isset($_GET['hb']))
	$tpl->assign("currentsearchhash",$_GET['hb']);
else if (isset($_GET['h']))
	$tpl->assign("currentsearchhash",$_GET['h']);
if ($gitphp_conf['search'])
	$tpl->assign("enablesearch",TRUE);
if ($gitphp_conf['filesearch'])
	$tpl->assign("filesearch",TRUE);


 if (isset($_GET['a']) && $_GET['a'] == "expire") {
 	require_once('include/cache.cache_expire.php');
	require_once('include/display.git_message.php');
	cache_expire(null, null, null, true);
	git_message("Cache expired");
 } else if (isset($_GET['a']) && $_GET['a'] == "opml") {
	require_once('include/display.git_opml.php');
	git_opml($gitphp_conf['projectroot'],(isset($git_projects) ? $git_projects : null));
 } else if (isset($_GET['a']) && $_GET['a'] == "project_index") {
	require_once('include/display.git_project_index.php');
	git_project_index($gitphp_conf['projectroot'],(isset($git_projects) ? $git_projects : null));
 } else if ($project) {
 	if (!is_dir($gitphp_conf['projectroot'] . $project)) {
		$tpl->assign("validproject",FALSE);
		require_once('include/display.git_message.php');
		git_message("No such directory",TRUE);
	} else if (!is_file($gitphp_conf['projectroot'] . $project . "/HEAD")) {
		$tpl->assign("validproject",FALSE);
		require_once('include/display.git_message.php');
		git_message("No such project",TRUE);
	} else {
		if (!isset($_GET['a'])) {
			require_once('include/display.git_summary.php');
			git_summary($gitphp_conf['projectroot'],$project);
		} else {
			switch ($_GET['a']) {
				case "summary":
					require_once('include/display.git_summary.php');
					git_summary($gitphp_conf['projectroot'],$project);
					break;
				case "tree":
					require_once('include/display.git_tree.php');
					git_tree($gitphp_conf['projectroot'], $project, (isset($_GET['h']) ? $_GET['h'] : NULL), (isset($_GET['f']) ? $_GET['f'] : NULL), (isset($_GET['hb']) ? $_GET['hb'] : NULL));
					break;
				case "shortlog":
					require_once('include/display.git_shortlog.php');
					git_shortlog($gitphp_conf['projectroot'],$project,(isset($_GET['h']) ? $_GET['h'] : NULL), (isset($_GET['pg']) ? $_GET['pg'] : NULL));
					break;
				case "log":
					require_once('include/display.git_log.php');
					git_log($gitphp_conf['projectroot'],$project, (isset($_GET['h']) ? $_GET['h'] : NULL), (isset($_GET['pg']) ? $_GET['pg'] : NULL));
					break;
				case "commit":
					require_once('include/display.git_commit.php');
					git_commit($gitphp_conf['projectroot'],$project,$_GET['h']);
					break;
				case "commitdiff":
					require_once('include/display.git_commitdiff.php');
					git_commitdiff($gitphp_conf['projectroot'],$project,$_GET['h'], (isset($_GET['hp']) ? $_GET['hp'] : NULL));
					break;
				case "commitdiff_plain":
					require_once('include/display.git_commitdiff_plain.php');
					git_commitdiff_plain($gitphp_conf['projectroot'],$project,$_GET['h'],(isset($_GET['hp']) ? $_GET['hp'] : NULL));
					break;
				case "heads":
					require_once('include/display.git_heads.php');
					git_heads($gitphp_conf['projectroot'],$project);
					break;
				case "tags":
					require_once('include/display.git_tags.php');
					git_tags($gitphp_conf['projectroot'],$project);
					break;
				case "rss":
					require_once('include/display.git_rss.php');
					git_rss($gitphp_conf['projectroot'],$project);
					break;
				case "blob":
					require_once('include/display.git_blob.php');
					git_blob($gitphp_conf['projectroot'],$project, (isset($_GET['h']) ? $_GET['h'] : NULL), (isset($_GET['f']) ? $_GET['f'] : NULL), (isset($_GET['hb']) ? $_GET['hb'] : NULL));
					break;
				case "blob_plain":
					require_once('include/display.git_blob_plain.php');
					git_blob_plain($gitphp_conf['projectroot'],$project,$_GET['h'],(isset($_GET['f']) ? $_GET['f'] : NULL));
					break;
				case "blobdiff":
					require_once('include/display.git_blobdiff.php');
					git_blobdiff($gitphp_conf['projectroot'],$project,$_GET['h'],$_GET['hb'],$_GET['hp'],(isset($_GET['f']) ? $_GET['f'] : NULL));
					break;
				case "blobdiff_plain":
					require_once('include/display.git_blobdiff_plain.php');
					git_blobdiff_plain($gitphp_conf['projectroot'],$project,$_GET['h'],$_GET['hp'], (isset($_GET['f']) ? $_GET['f'] : NULL));
					break;
				case "snapshot":
					require_once('include/display.git_snapshot.php');
					git_snapshot($gitphp_conf['projectroot'],$project, (isset($_GET['h']) ? $_GET['h'] : NULL));
					break;
				case "tag":
					require_once('include/display.git_tag.php');
					git_tag($gitphp_conf['projectroot'],$project,$_GET['h']);
					break;
				default:
					$tpl->assign("validaction", FALSE);
					require_once('include/display.git_message.php');
					git_message("Unknown action", TRUE);
					break;
			}
		}
	}
 } else {
	require_once('include/display.git_project_list.php');
	git_project_list($gitphp_conf['projectroot'], (isset($git_projects) ? $git_projects : null), (isset($_GET['o']) ? $_GET['o'] : "project"));
 }

 if ($gitphp_conf['debug'] && $extraoutput)
 	echo "Execution time: " . round(microtime(true) - GITPHP_START_TIME, 8) . " sec";

?>
