<?php
/*
 *  glip.git_read_head.php
 *  gitphp: A PHP git repository browser
 *  Component: Git utility - read HEAD
 *
 *  Copyright (C) 2008 Christopher Han <xiphux@gmail.com>
 *  Copyright (C) 2009 Michael Vigovsky <xvmv@mail.ru>
 */

function git_read_head(Git $proj)
{
	return $proj->revParse("HEAD");
}


