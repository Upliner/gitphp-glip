<?php
/*
 *  glip.git_read_commit.php
 *  gitphp: A PHP git repository browser
 *  Component: Git utility - read a commit
 *
 *  Copyright (C) 2008 Christopher Han <xiphux@gmail.com>
 *  Copyright (C) 2009 Michael Vigovsky <xvmv@mail.ru>
 */

require_once('defs.constants.php');
require_once('util.age_string.php');

function git_read_commit($proj,$head)
{
	$obj = $proj->getObject($head);
	if ($obj->getType()!=Git::OBJ_COMMIT) return;

	$commit = array();
	$commit['id'] = sha1_hex($obj->getName());
	$parents = array();
	foreach ($obj->parents as $par)
		$parents[] = sha1_hex($par);
	$commit['parents'] = $parents;
	if (isset($parents[0]))
		$commit['parent'] = $parents[0];

	$commit['tree'] = sha1_hex($obj->tree);

	$commit['author'] = sprintf("%s <%s>",$obj->author->name,$obj->author->email);
	$commit['author_epoch'] = $obj->author->time;
	$commit['author_tz'] = sprintf("%+03d%02d",$obj->author->offset/3600,abs($obj->author->offset%3600/60));
	$commit['author_name'] = $obj->author->name;

	$commit['committer'] = sprintf("%s <%s>",$obj->committer->name,$obj->committer->email);
	$commit['committer_epoch'] = $obj->committer->time;
	$commit['committer_tz'] = sprintf("%+03d%02d",$obj->committer->offset/3600,abs($obj->committer->offset%3600/60));
	$commit['committer_name'] = $obj->committer->name;

	$commit['title'] = $obj->summary;
	if (strlen($obj->summary) > GITPHP_TRIM_LENGTH)
		$commit['title_short'] = substr($obj->summary,0,GITPHP_TRIM_LENGTH) . "...";
	else
		$commit['title_short'] = $obj->summary;

	$comment = explode("\n",$obj->summary . "\n" . $obj->detail);
	$commit['comment'] = $comment;

	$age = time() - $commit['committer_epoch'];
	$commit['age'] = $age;
	$commit['age_string'] = age_string($age);
	date_default_timezone_set("UTC");
	if ($age > 60*60*24*7*2) {
		$commit['age_string_date'] = date("Y-m-d",$commit['committer_epoch']);
		$commit['age_string_age'] = $commit['age_string'];
	} else {
		$commit['age_string_date'] = $commit['age_string'];
		$commit['age_string_age'] = date("Y-m-d",$commit['committer_epoch']);
	}
	return $commit;
}

?>
