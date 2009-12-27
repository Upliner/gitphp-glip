<?php
/*
 *  display.git_heads.php
 *  gitphp: A PHP git repository browser
 *  Component: Display - heads
 *
 *  Copyright (C) 2008 Christopher Han <xiphux@gmail.com>
 *  Copyright (C) 2009 Michael Vigovsky <xvmv@mail.ru>
 */

 require_once('glip/lib/glip.php');
 require_once('glip.git_read_head.php');
 require_once('glip.git_read_refs.php');

function git_heads($projectroot,$project)
{
	global $tpl;

	$cachekey = sha1($project);

	$git = new Git($projectroot . $project);

	if (!$tpl->is_cached('heads.tpl', $cachekey)) {
		$head = git_read_head($git);
		$tpl->assign("head",sha1_hex($head));
		$headlist = git_read_refs($git, "refs/heads");
		$tpl->assign("headlist",$headlist);
	}
	$tpl->display('heads.tpl', $cachekey);
}

?>
