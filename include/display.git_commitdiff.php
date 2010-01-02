<?php
/*
 *  display.git_commitdiff.php
 *  gitphp: A PHP git repository browser
 *  Component: Display - commit diff
 *
 *  Copyright (C) 2008 Christopher Han <xiphux@gmail.com>
 *  Copyright (C) 2010 Michael Vigovsky <xvmv@mail.ru>
 */

 require_once('glip/lib/glip.php');
 require_once('util.file_type.php');
 require_once('glip.git_read_commit.php');
// require_once('glip.git_diff_tree.php');
 require_once('glip.read_info_ref.php');
 require_once('glip.git_diff.php');

function git_commitdiff($projectroot,$project,$hash,$hash_parent)
{
	global $tpl;

	$cachekey = sha1($project) . "|" . $hash . "|" . $hash_parent;

	$git = new Git($projectroot . $project);

	$hash = sha1_bin($hash);
	if (isset($hash_parent)) $hash_parent = sha1_bin($hash_parent);

	if (!$tpl->is_cached('commitdiff.tpl', $cachekey)) {
		$co = git_read_commit($git, $hash);
		if (!isset($hash_parent) && isset($co['parent']))
			$hash_parent = sha1_bin($co['parent']);

		$a_tree = isset($hash_parent) ? $git->getObject($hash_parent)->getTree() : array();
		$b_tree = $git->getObject(sha1_bin($co['tree']));

		$difftree = GitTree::diffTree($a_tree,$b_tree);
		$tpl->assign("hash",sha1_hex($hash));
		$tpl->assign("tree",$co['tree']);
		$tpl->assign("hashparent",sha1_hex($hash_parent));
		$tpl->assign("title",$co['title']);
		$refs = read_info_ref($git);
		if (isset($refs[$hash]))
			$tpl->assign("commitref",$refs[$hash]);
		$tpl->assign("comment",$co['comment']);
		$difftreelines = array();
		$status_map = array(
			GitTree::TREEDIFF_ADDED   => "A",
			GitTree::TREEDIFF_REMOVED => "D",
			GitTree::TREEDIFF_CHANGED => "M");

		foreach ($difftree as $file => $diff) {
			$difftreeline = array();
			$difftreeline["from_mode"] = decoct($diff->old_mode);
			$difftreeline["to_mode"]   = decoct($diff->new_mode);
			$difftreeline["from_id"] = sha1_hex($diff->old_obj);
			$difftreeline["to_id"]   = sha1_hex($diff->new_obj);
			$difftreeline["status"] = $status_map[$diff->status];
			$difftreeline["file"] = $file;
			$difftreeline["from_type"] = file_type($difftreeline["from_mode"]);
			$difftreeline["to_type"] = file_type($difftreeline["to_mode"]);
			if ($diff->status == GitTree::TREEDIFF_ADDED)
				$difftreeline['diffout'] = explode("\n",git_diff($git, null,"/dev/null",$diff->new_obj,"b/" . $file));
			else if ($diff->status == GitTree::TREEDIFF_REMOVED)
				$difftreeline['diffout'] = explode("\n",git_diff($git, $diff->old_obj,"a/" . $file,null,"/dev/null"));
			else if ($diff->status == GitTree::TREEDIFF_CHANGED && $diff->old_obj != $diff->new_obj)
				$difftreeline['diffout'] = explode("\n",git_diff($git, $diff->old_obj,"a/" . $file,$diff->new_obj,"b/" . $file));
			$difftreelines[] = $difftreeline;
		}
		$tpl->assign("difftreelines",$difftreelines);
	}
	$tpl->display('commitdiff.tpl', $cachekey);
}

?>
