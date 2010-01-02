<?php
/*
 *  glip.git_read_ref.php
 *  gitphp: A PHP git repository browser
 *  Component: Git utility - read single ref
 *
 *  Copyright (C) 2009 Christopher Han <xiphux@gmail.com>
 *  Copyright (C) 2010 Michael Vigovsky <xvmv@mail.ru>
 */

 require_once('util.age_string.php');
 require_once('glip.git_read_tag.php');
 require_once('glip.git_read_commit.php');
 require_once('glip.git_get_type.php');

 function git_read_ref($project, $ref_id, $ref_file)
 {
	$hash = $project->revParse(trim($ref_id));
	$type = git_get_type($project, $hash);

	if (!$type)
		return null;

	$ref_item = array();
	$ref_item['type'] = $type;
	$ref_item['id'] = $ref_id;
	$ref_item['epoch'] = 0;
	$ref_item['age_string'] = "unknown";

	if ($type == "tag") {
		$tag = git_read_tag($project, $hash);
		$ref_item['comment'] = $tag['comment'];
		if ($tag['type'] == "commit") {
			$co = git_read_commit($project, sha1_bin($tag['object']));
			$ref_item['epoch'] = $co['committer_epoch'];
			$ref_item['age_string'] = $co['age_string'];
			$ref_item['age'] = $co['age'];
		} else if (isset($tag['epoch'])) {
			$age = time() - $tag['epoch'];
			$ref_item['epoch'] = $tag['epoch'];
			$ref_item['age_string'] = age_string($age);
			$ref_item['age'] = $age;
		}
		$ref_item['reftype'] = $tag['type'];
		$ref_item['name'] = $tag['name'];
		$ref_item['refid'] = $tag['object'];
	} else if ($type == "commit") {
		$co = git_read_commit($project, $hash);
		$ref_item['reftype'] = "commit";
		$ref_item['name'] = $ref_file;
		$ref_item['title'] = $co['title'];
		$ref_item['refid'] = $ref_id;
		$ref_item['epoch'] = $co['committer_epoch'];
		$ref_item['age_string'] = $co['age_string'];
		$ref_item['age'] = $co['age'];
	}

	return $ref_item;
 }

?>
