<?php
/*
 *  display.git_blobdiff.php
 *  gitphp: A PHP git repository browser
 *  Component: Display - blob diff
 *
 *  Copyright (C) 2008 Christopher Han <xiphux@gmail.com>
 *  Copyright (C) 2010 Michael Vigovsky <xvmv@mail.ru>
 */

 require_once('glip/lib/glip.php');
 require_once('util.prep_tmpdir.php');
 require_once('glip.git_read_commit.php');
 require_once('glip.read_info_ref.php');
 require_once('glip.git_path_trees.php');
 require_once('glip.git_diff.php');

function git_blobdiff($projectroot,$project,$hash,$hashbase,$hashparent,$file)
{
	global $tpl;

	$cachekey = sha1($project) . "|" . $hashbase . "|" . $hash . "|" . $hashparent . "|" . sha1($file);

	$git = new Git($projectroot . $project);

	if (!$tpl->is_cached('blobdiff.tpl', $cachekey)) {
		$ret = prep_tmpdir();
		if ($ret !== TRUE) {
			echo $ret;
			return;
		}
		$tpl->assign("hash",$hash);
		$tpl->assign("hashparent",$hashparent);
		$tpl->assign("hashbase",$hashbase);
		if (isset($file))
			$tpl->assign("file",$file);

		$hash       = sha1_bin($hash);
		$hashbase   = sha1_bin($hashbase);
		$hashparent = sha1_bin($hashparent);
		if ($co = git_read_commit($git, $hashbase)) {
			$tpl->assign("fullnav",TRUE);
			$tpl->assign("tree",sha1_hex($co['tree']));
			$tpl->assign("title",$co['title']);
			$refs = read_info_ref($git);
			if (isset($refs[$hashbase]))
				$tpl->assign("hashbaseref",$refs[$hashbase]);
		}
		$paths = git_path_trees($git, $git->getObject($hashbase), $file);
		$tpl->assign("paths",$paths);
		$diffout = explode("\n",git_diff($git, $hashparent,($file?$file:$hashparent),$hash,($file?$file:$hash)));
		$tpl->assign("diff",$diffout);
	}
	$tpl->display('blobdiff.tpl', $cachekey);
}

?>
