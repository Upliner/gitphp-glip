<?php
/*
 *  display.git_tag.php
 *  gitphp: A PHP git repository browser
 *  Component: Display - tag
 *
 *  Copyright (C) 2009 Christopher Han <xiphux@gmail.com>
 *  Copyright (C) 2010 Michael Vigovsky <xvmv@mail.ru>
 */

 require_once('glip/lib/glip.php');
 require_once('util.date_str.php');
 require_once('glip.git_read_tag.php');
 require_once('glip.git_read_head.php');

function git_tag($projectroot, $project, $hash)
{
	global $tpl;

	$cachekey = sha1($project) . "|" . $hash;

	$git = new Git($projectroot . $project);

	$hash = $git->revParse($hash);

	if (!$tpl->is_cached('tag.tpl', $cachekey)) {

		$head = git_read_head($git);
		$tpl->assign("head", sha1_hex($head));
		$tpl->assign("hash", sha1_hex($hash));

		$tag = git_read_tag($git, $hash);

		$tpl->assign("tag",$tag);
		if (isset($tag['author'])) {
			$ad = date_str($tag['epoch'],$tag['tz']);
			$tpl->assign("datedata",$ad);
		}
	}
	$tpl->display('tag.tpl', $cachekey);
}

?>
