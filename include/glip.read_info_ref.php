<?php
/*
 *  glip.read_info_ref.php
 *  gitphp: A PHP git repository browser
 *  Component: Git utility - read info on a ref
 *
 *  Copyright (C) 2008 Christopher Han <xiphux@gmail.com>
 *  Copyright (C) 2009 Michael Vigovsky <xvmv@mail.ru>
 */

function read_info_ref($project, $type = "")
{
	$refs = $project->getRefs();
	$result = array();
	foreach ($refs as $key => $value)
		$result[$value] = $key;
	return $result;
}

?>
