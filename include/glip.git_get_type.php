<?php
/*
 *  glip.git_get_type.php
 *  gitphp: A PHP git repository browser
 *  Component: Git utility - get type
 *
 *  Copyright (C) 2008 Christopher Han <xiphux@gmail.com>
 *  Copyright (C) 2009 Michael Vigovsky <xvmv@mail.ru>
 */

function git_get_type($project, $hash)
{
	return trim(Git::GetTypeName($project->getObject($hash)->getType()));
}

?>
