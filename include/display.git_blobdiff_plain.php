<?php
/*
 *  display.git_blobdiff_plain.php
 *  gitphp: A PHP git repository browser
 *  Component: Display - blob diff (plaintext)
 *
 *  Copyright (C) 2008 Christopher Han <xiphux@gmail.com>
 *  Copyright (C) 2010 Michael Vigovsky <xvmv@mail.ru>
 */

 require_once('glip/lib/glip.php');
 require_once('util.prep_tmpdir.php');
 require_once('glip.git_diff.php');

function git_blobdiff_plain($projectroot,$project,$hash,$hashparent,$file)
{
	global $tpl;

	header("Content-type: text/plain; charset=UTF-8");

	$cachekey = sha1($project) . "|" . $hash . "|" . $hashparent . "|" . sha1($file);

	$git = new Git($projectroot . $project);

	if (!$tpl->is_cached('blobdiffplain.tpl', $cachekey)) {
		$ret = prep_tmpdir();
		if ($ret !== TRUE) {
			echo $ret;
			return;
		}
		$tpl->assign("blobdiff",git_diff($git, sha1_bin($hashparent),($file?"a/".$file:$hashparent),sha1_bin($hash),($file?"b/".$file:$hash)));
	}
	$tpl->display('blobdiffplain.tpl', $cachekey);
}

?>
