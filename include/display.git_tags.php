<?php
/*
 *  display.git_tags.php
 *  gitphp: A PHP git repository browser
 *  Component: Display - tags
 *
 *  Copyright (C) 2008 Christopher Han <xiphux@gmail.com>
 *  Copyright (C) 2009 Michael Vigovsky <xvmv@mail.ru>
 */

 require_once('glip/lib/glip.php');
 require_once('glip.git_read_head.php');
 require_once('glip.git_read_refs.php');

function git_tags($projectroot,$project)
{
	global $tpl;

	$cachekey = sha1($project);

	$git = new Git($projectroot . $project);

	if (!$tpl->is_cached('tags.tpl', $cachekey)) {
		$head = git_read_head($git);
		$tpl->assign("head",sha1_hex($head));
		$taglist = git_read_refs($git, "refs/tags");
		if (isset($taglist) && (count($taglist) > 0)) {
			$tpl->assign("taglist",$taglist);
		}
	}
	$tpl->display('tags.tpl', $cachekey);
}

?>
