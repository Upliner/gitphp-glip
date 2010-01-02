<?php
/*
 *  display.git_commitdiff_plain.php
 *  gitphp: A PHP git repository browser
 *  Component: Display - commit diff (plaintext)
 *
 *  Copyright (C) 2008 Christopher Han <xiphux@gmail.com>
 *  Copyright (C) 2010 Michael Vigovsky <xvmv@mail.ru>
 */

 require_once('glip/lib/glip.php');
 require_once('util.date_str.php');
 require_once('util.script_url.php');
 require_once('glip.git_read_commit.php');
 require_once('glip.git_read_revlist.php');
 require_once('glip.read_info_ref.php');
 require_once('glip.git_diff.php');

function git_commitdiff_plain($projectroot,$project,$hash,$hash_parent)
{
	global $tpl;

	$cachekey = sha1($project) . "|" . $hash . "|" . $hash_parent;

	header("Content-type: text/plain; charset=UTF-8");
	header("Content-disposition: inline; filename=\"git-" . $hash . ".patch\"");

	$git = new Git($projectroot . $project);

	$hash = sha1_bin($hash);
	if (isset($hash_parent)) $hash_parent = sha1_bin($hash_parent);

	if (!$tpl->is_cached('diff_plaintext.tpl', $cachekey)) {
		$co = git_read_commit($git, $hash);
		if (!isset($hash_parent) && isset($co['parent']))
			$hash_parent = sha1_bin($co['parent']);

		$a_tree = isset($hash_parent) ? $git->getObject($hash_parent)->getTree() : array();
		$b_tree = $git->getObject(sha1_bin($co['tree']));

		$difftree = GitTree::diffTree($a_tree,$b_tree);

		// FIXME: simplified tagname search is not implemented yet
		/*$refs = read_info_ref($git,"tags");
		$listout = git_read_revlist($git, "HEAD");
		foreach ($listout as $i => $rev) {
			if (isset($refs[$rev]))
				$tagname = $refs[$rev];
			if ($rev == $hash)
				break;
		}*/
		$ad = date_str($co['author_epoch'],$co['author_tz']);
		$tpl->assign("from",$co['author']);
		$tpl->assign("date",$ad['rfc2822']);
		$tpl->assign("subject",$co['title']);
		if (isset($tagname))
			$tpl->assign("tagname",$tagname);
		$tpl->assign("url",script_url() . "?p=" . $project . "&a=commitdiff&h=" . sha1_hex($hash));
		$tpl->assign("comment",$co['comment']);
		$diffs = array();
		foreach ($difftree as $file => $diff) {
			if ($diff->status == GitTree::TREEDIFF_ADDED)
				$diffs[] = git_diff($git, null, "/dev/null", $diff->new_obj, "b/" . $file);
			else if ($diff->status == GitTree::TREEDIFF_REMOVED)
				$diffs[] = git_diff($git, $diff->old_obj, "a/" . $file, null, "/dev/null");
			else if ($diff->status == GitTree::TREEDIFF_CHANGED )
				$diffs[] = git_diff($git, $diff->old_obj, "a/" . $file, $diff->new_obj, "b/" . $file);
		}
		$tpl->assign("diffs",$diffs);
	}
	$tpl->display('diff_plaintext.tpl', $cachekey);
}

?>
