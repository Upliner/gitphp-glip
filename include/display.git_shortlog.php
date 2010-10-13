<?php
/*
 *  display.git_shortlog.php
 *  gitphp: A PHP git repository browser
 *  Component: Display - short log
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

function git_shortlog($projectroot,$project,$hash,$page)
{
	global $tpl;

	$cachekey = sha1($project) . "|" . $hash . "|" . (isset($page) ? $page : 0);
    $tpl->assign('project',$project);

	$git = new Git($projectroot . $project);

	if (!$tpl->is_cached('shortlog.tpl', $cachekey)) {
		$head = git_read_head($git);
		if (!isset($hash))
			$hash = $head;
		else
			$hash = $git->revParse($hash);
		if (!isset($page))
			$page = 0;
		$refs = read_info_ref($git);
		$tpl->assign("hash",sha1_hex($hash));
		$tpl->assign("head",sha1_hex($head));

		if ($page)
			$tpl->assign("page",$page);

		$revlist = git_read_revlist($git, $hash, 101, ($page * 100));

		$revlistcount = count($revlist);
		$tpl->assign("revlistcount",$revlistcount);

		$commitlines = array();
		$commitcount = min(100,count($revlist));
		for ($i = 0; $i < $commitcount; ++$i) {
			$commit = $revlist[$i];
			$commithash = sha1_hex($commit->getName());
			$commitline = array();
			if (isset($refs[$commit->getName()]))
			{
				$commitline["commitref"] = $refs[$commit->getName()];
				$commitline["commitclass"] = get_commit_class($refs[$commit->getName()]);
		    }
			$co = git_read_commit($git, $commit->getName());
			$ad = date_str($co['author_epoch']);
			$commitline["commit"] = $commithash;
			$commitline["agestringage"] = $co['age_string_age'];
			$commitline["agestringdate"] = $co['age_string_date'];
			$commitline["authorname"] = $co['author_name'];
			$commitline["title_short"] = $co['title_short'];
			if (strlen($co['title_short']) < strlen($co['title']))
				$commitline["title"] = $co['title'];
			$commitlines[] = $commitline;
		}
		$tpl->assign("commitlines",$commitlines);
	}
	$tpl->display('shortlog.tpl', $cachekey);
}

?>
