<?php
/*
 *  glip.git_get_hash_by_path.php
 *  gitphp: A PHP git repository browser
 *  Component: Git utility - get hash from a path
 *
 *  Copyright (C) 2008 Christopher Han <xiphux@gmail.com>
 *  Copyright (C) 2009 Michael Vigovsky <xvmv@mail.ru>
 */

function git_get_hash_by_path($project,$base,$path,$type = null)
{
	//FIXME: $type is unused now.
	$tree = $base;
	if ($tree->getType()==Git::OBJ_COMMIT) $tree = $tree->getTree();

	$parts = explode("/",$path);
	$partcount = count($parts);
	foreach ($parts as $part) {
		foreach ($tree->nodes as $node) {
			if ($node->name == $part) {
				if ($node->is_dir) {
					$tree = $project->GetObject($node->object);
					continue 2;
				} else
					return $node->object;
			}
		}
		return $tree->getName();
	}
	return $tree->getName();
}

?>
