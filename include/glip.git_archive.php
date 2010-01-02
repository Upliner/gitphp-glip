<?php
/*
 *  glip.git_archive.php
 *  gitphp: A PHP git repository browser
 *  Component: Git utility - archive
 *
 *  Copyright (C) 2009 Christopher Han <xiphux@gmail.com>
 *  Copyright (C) 2010 Michael Vigovsky <xvmv@mail.ru>
 */

 require_once('glip/lib/glip.php');
 require_once('tar/tar.class.php');


function git_archive($proj,$hash,$rname = NULL, $fmt = "tar")
{
	function walktree($tar,$path,$tree)
	{
		$git   = $tree->repo;
		$nodes = $tree->nodes;
		foreach ($nodes as $name => $node)
		{
			if ($node->is_dir)
			{
				$tar->addDirectory($path . $name);
				walktree($tar,$path . $name . "/",$git->getObject($node->object));
			} else
			{
				$tar->addData($path . $name,$git->getObject($node->object)->data);
			}
		}
	}

	$hash = $proj->revParse($hash);
	$obj = $proj->getObject($hash);
	if ($obj->getType()==Git::OBJ_COMMIT) $obj = $obj->getTree();
	$tar = new Tar();
	$prefix = rtrim($rname,'/') . '/';
	walktree($tar,$prefix,$obj);
	return $tar->toTar(false);
}

?>
