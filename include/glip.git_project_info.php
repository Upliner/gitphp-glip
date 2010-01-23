<?php
/*
 *  glip.git_project_info.php
 *  gitphp: A PHP git repository browser
 *  Component: Git utility - single project info
 *
 *  Copyright (C) 2009 Christopher Han <xiphux@gmail.com>
 *  Copyright (C) 2009 Michael Vigovsky <xvmv@mail.ru>
 */

 require_once('glip/lib/glip.php');
 require_once('gitutil.git_project_descr.php');
 require_once('gitutil.git_project_owner.php');
 require_once('glip.git_read_head.php');
 require_once('glip.git_read_commit.php');

function git_project_info($projectroot,$project)
{
	$git = new Git($projectroot . $project);
	$projinfo = array();
	$projinfo["project"] = $project;
	$projinfo["descr"] = git_project_descr($projectroot,$project,TRUE);
	$projinfo["owner"] = git_project_owner($projectroot,$project);
	$head = git_read_head($git, $project);
	$commit = git_read_commit($git,$head);
	$projinfo["age"] = $commit['age'];
	$projinfo["age_string"] = $commit['age_string'];
	return $projinfo;
}

?>
