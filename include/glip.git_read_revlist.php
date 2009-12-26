<?php
/*
 *  glip.git_read_revlist.php
 *  gitphp: A PHP git repository browser
 *  Component: Git utility - get and format revision list
 *
 *  Copyright (C) 2008 Christopher Han <xiphux@gmail.com>
 *  Copyright (C) 2009 Michael Vigovsky <xvmv@mail.ru>
 */

function git_read_revlist($proj,$head,$count = NULL,$skip = NULL)
{
	//$hash = $proj->revParse($head);
	$commit = $proj->getObject($head);
	while ($skip--) {
		if (isset($commit->parents[0]))
			$commit = $proj->getObject($commit->parents[0]);
		else
			return;
	}

	$tmp = array();

	while ($count--) {
		$tmp[] = $commit;

		if (isset($commit->parents[0]))
			$commit = $proj->getObject($commit->parents[0]);
		else
			return $tmp;
	}
	return $tmp;
}

?>
