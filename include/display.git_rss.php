<?php
/*
 *  display.git_rss.php
 *  gitphp: A PHP git repository browser
 *  Component: Display - RSS feed
 *
 *  Copyright (C) 2008 Christopher Han <xiphux@gmail.com>
 *  Copyright (C) 2010 Michael Vigovsky <xvmv@mail.ru>
 */

 require_once('defs.constants.php');
 require_once('util.date_str.php');
 require_once('util.script_url.php');
 require_once('glip/lib/glip.php');
 require_once('glip.git_read_head.php');
 require_once('glip.git_read_revlist.php');
 require_once('glip.git_read_commit.php');

function git_rss($projectroot,$project)
{
	global $tpl;
	header("Content-type: text/xml; charset=UTF-8");

	$cachekey = sha1($project);
	$git = new Git($projectroot . $project);

	if (!$tpl->is_cached('rss.tpl', $cachekey)) {
		$head = git_read_head($git);
		$revlist = git_read_revlist($git, $head, GITPHP_RSS_ITEMS);
		$tpl->assign("self",script_url());

		$commitlines = array();
		$revlistcount = count($revlist);
		for ($i = 0; $i < $revlistcount; ++$i) {
			$commit = $revlist[$i];
			$co = git_read_commit($git, $commit->getName());
			if (($i >= 20) && ((time() - $co['committer_epoch']) > 48*60*60))
				break;
			$cd = date_str($co['committer_epoch']);
			$difftree = array();
			$diffout = GitTree::diffTree(
					isset($commit->parent[0])?$commit->parent[0]->getTree():null,
					$commit->getTree());
			foreach ($diffout as $file => $diff) {
				$difftree[] = $file;
			}
			$commitline = array();
			$commitline["cdmday"] = $cd['mday'];
			$commitline["cdmonth"] = $cd['month'];
			$commitline["cdhour"] = $cd['hour'];
			$commitline["cdminute"] = $cd['minute'];
			$commitline["title"] = $co['title'];
			$commitline["author"] = $co['author'];
			$commitline["cdrfc2822"] = $cd['rfc2822'];
			$commitline["commit"] = $commit;
			$commitline["comment"] = $co['comment'];
			$commitline["difftree"] = $difftree;
			$commitlines[] = $commitline;
		}
		$tpl->assign("commitlines",$commitlines);
	}
	$tpl->display('rss.tpl', $cachekey);
}

?>
