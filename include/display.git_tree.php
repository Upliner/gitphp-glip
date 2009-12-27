<?php
/*
 *  display.git_tree.php
 *  gitphp: A PHP git repository browser
 *  Component: Display - tree
 *
 *  Copyright (C) 2008 Christopher Han <xiphux@gmail.com>
 *  Copyright (C) 2009 Michael Vigovsky <xvmv@mail.ru>
 */

 require_once('glip/lib/glip.php');
 require_once('util.mode_str.php');
 require_once('glip.git_read_head.php');
 require_once('glip.git_get_hash_by_path.php');
// require_once('gitutil.git_ls_tree.php');
 require_once('glip.read_info_ref.php');
 require_once('glip.git_read_commit.php');
 require_once('glip.git_path_trees.php');

function git_tree($projectroot,$project,$hash,$file,$hashbase)
{
	global $tpl;

	$cachekey = sha1($project) . "|" . $hashbase . "|" . $hash . "|" . sha1($file);

	$git = new Git($projectroot . $project);

	if (isset($hash)) $hash = sha1_bin($hash);
	if (isset($hashbase)) $hashbase = sha1_bin($hashbase);

	if (!$tpl->is_cached('tree.tpl', $cachekey)) {
		if (!isset($hash)) {
			$hash = git_read_head($git);
			if (!isset($hashbase))
				$hashbase = $hash;
			if (isset($file))
				$hash = git_get_hash_by_path($git, $git->getObject($hashbase?$hashbase:$hash),$file,"tree");
		}
		$refs = read_info_ref($git);

		$tpl->assign("hash",sha1_hex($hash));
		if (isset($hashbase))
			$tpl->assign("hashbase",sha1_hex($hashbase));

		if (isset($hashbase) && ($co = git_read_commit($git, $hashbase))) {
			$basekey = $hashbase;
			$tpl->assign("fullnav",TRUE);
			$tpl->assign("title",$co['title']);
			if (isset($refs[$hashbase]))
				$tpl->assign("hashbaseref",$refs[$hashbase]);
		}
		if (isset($hashbase)) {
			$objbase = $git->getObject($hashbase);
			if ($objbase->getType()==Git::OBJ_COMMIT) $objbase = $objbase->getTree();
			$paths = git_path_trees($git, $objbase, $file);
			$tpl->assign("paths",$paths);
		}

		if (isset($file))
			$tpl->assign("base",$file . "/");

		$obj = $git->getObject($hash);
		if ($obj->getType()==Git::OBJ_COMMIT) $obj = $obj->getTree();

		$treelines = array();
		foreach ($obj->nodes as $node) {
			$treeline = array();
			$treeline["filemode"] = mode_str(decoct($node->mode));
			$treeline["type"] = $node->is_dir?"tree":"blob";
			$treeline["hash"] = sha1_hex($node->object);
			$treeline["name"] = $node->name;
			$treelines[] = $treeline;
			$tok = strtok("\0");
		}
		$tpl->assign("treelines",$treelines);
	}
	$tpl->display('tree.tpl', $cachekey);
}

?>
