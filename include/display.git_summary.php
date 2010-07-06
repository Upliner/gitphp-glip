<?php
/*
 *  display.git_summary.php
 *  gitphp: A PHP git repository browser
 *  Component: Display - summary page
 *
 *  Copyright (C) 2008 Christopher Han <xiphux@gmail.com>
 *  Copyright (C) 2009 Michael Vigovsky <xvmv@mail.ru>
 */

 require_once('glip/lib/glip.php');
 require_once('util.date_str.php');
 require_once('gitutil.git_project_descr.php');
 require_once('gitutil.git_project_owner.php');
 require_once('glip.git_read_head.php');
 require_once('glip.git_read_commit.php');
 require_once('glip.git_read_revlist.php');
 require_once('glip.git_read_refs.php');
 require_once('glip.read_info_ref.php');

function git_summary($projectroot,$project)
{
	global $tpl,$gitphp_conf;

	$cachekey = sha1($project);

	$git = new Git($projectroot . $project);
	if (!$tpl->is_cached('project.tpl', $cachekey)) {
		$descr = git_project_descr($projectroot,$project);
		$head = git_read_head($git);
		$commit = git_read_commit($git, $head);
		$commitdate = date_str($commit['committer_epoch'],$commit['committer_tz']);
		$owner = git_project_owner($projectroot,$project);
		$refs = read_info_ref($git);
		$tpl->assign("head",sha1_hex($head));
		$tpl->assign("description",$descr);
		$tpl->assign("owner",$owner);
		$tpl->assign("lastchange",$commitdate['rfc2822']);
		if (isset($gitphp_conf['cloneurl']))
			$tpl->assign('cloneurl', $gitphp_conf['cloneurl'] . $project);
		if (isset($gitphp_conf['pushurl']))
			$tpl->assign('pushurl', $gitphp_conf['pushurl'] . $project);
		$revlist = git_read_revlist($git, $head, 17);
		foreach ($revlist as $i => $rev) {
			$revhash = sha1_hex($rev->getName());
			$revdata = array();
			$revco = git_read_commit($git, $rev->getName());
			$authordate = date_str($revco['author_epoch']);
			$revdata["commit"] = $revhash;
			if (isset($refs[$rev->getName()]))
			{
				$revdata["commitref"] = $refs[$rev->getName()];
				$revdata["commitclass"] = get_commit_class($refs[$rev->getName()]);
		    }
			$revdata["commitage"] = $revco['age_string'];
			$revdata["commitauthor"] = $revco['author_name'];
			if (strlen($revco['title_short']) < strlen($revco['title'])) {
				$revdata["title"] = $revco['title'];
				$revdata["title_short"] = $revco['title_short'];
			} else
				$revdata["title_short"] = $revco['title'];
			$revlist[$i] = $revdata;
		}
		$tpl->assign("revlist",$revlist);

		$taglist = git_read_refs($git,"refs/tags");
		if (isset($taglist) && (count($taglist) > 0)) {
			foreach ($taglist as $i => $tag) {
				if (isset($tag['comment'])) {
					$com = trim($tag['comment'][0]);
					if (strlen($com) > GITPHP_TRIM_LENGTH)
						$com = substr($com,0,GITPHP_TRIM_LENGTH) . "...";
					$taglist[$i]['comment'] = $com;
				}
			}
			$tpl->assign("taglist",$taglist);
		}

		$headlist = git_read_refs($git,"refs/heads");
		if (isset($headlist) && (count($headlist) > 0)) {
			$tpl->assign("headlist",$headlist);
		}
	}
	$tpl->display('project.tpl', $cachekey);
}

?>
