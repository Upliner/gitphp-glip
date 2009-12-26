<?php
/*
 *  display.git_log.php
 *  gitphp: A PHP git repository browser
 *  Component: Display - log
 *
 *  Copyright (C) 2008 Christopher Han <xiphux@gmail.com>
 *  Copyright (C) 2009 Michael Vigovsky <xvmv@mail.ru>
 */

 require_once('glip/lib/glip.php');
 require_once('util.date_str.php');
 require_once('glip.git_read_head.php');
 require_once('glip.git_read_revlist.php');
 require_once('glip.git_read_commit.php');
 require_once('glip.read_info_ref.php');

function git_log($projectroot,$project,$hash,$page)
{
	global $tpl;

	$cachekey = sha1($project) . "|" . $hash . "|" . (isset($page) ? $page : 0);

	$git = new Git($projectroot . $project);

	if (!$tpl->is_cached('shortlog.tpl', $cachekey)) {
		$head = git_read_head($git);
		if (!isset($hash))
			$hash = $head;
		else
			$hash = $git->revParse($hash);
		if (!isset($page))
			$page = 0;
		$refs = read_info_ref($projectroot . $project);
		$tpl->assign("hash",sha1_hex($hash));
		$tpl->assign("head",sha1_hex($head));

		if ($page)
			$tpl->assign("page",$page);

		$revlist = git_read_revlist($git, $hash, 101, ($page * 100));

		$revlistcount = count($revlist);
		$tpl->assign("revlistcount",$revlistcount);

		if (!$revlist) {
			$tpl->assign("norevlist",TRUE);
			$co = git_read_commit($git, $hash);
			$tpl->assign("lastchange",$co['age_string']);
		}

		$commitlines = array();
		$commitcount = min(100,$revlistcount);
		for ($i = 0; $i < $commitcount; ++$i) {
			$commit = $revlist[$i];
			$commithash = sha1_hex($commit->getName());

			$commitline = array();
			$co = git_read_commit($git, $commit->getName());
			$ad = date_str($co['author_epoch']);
			$commitline["project"] = $project;
			$commitline["commit"] = $commithash;
			if (isset($refs[$commithash]))
				$commitline["commitref"] = $refs[$commithash];
			$commitline["agestring"] = $co['age_string'];
			$commitline["title"] = $co['title'];
			$commitline["authorname"] = $co['author_name'];
			$commitline["rfc2822"] = $ad['rfc2822'];
			$commitline["comment"] = $co['comment'];
			$commitlines[] = $commitline;
		}
		$tpl->assign("commitlines",$commitlines);
	}
	$tpl->display('log.tpl', $cachekey);
}

?>
