<?php
/*
 *  gitutil.git_read_tag.php
 *  gitphp: A PHP git repository browser
 *  Component: Git utility - read tag
 *
 *  Copyright (C) 2008 Christopher Han <xiphux@gmail.com>
 *  Copyright (C) 2010 Michael Vigovsky <xvmv@mail.ru>
 */

function git_read_tag($proj, $tag_id)
{
	$obj = $proj->getObject($tag_id);
	if ($obj->getType()!=Git::OBJ_TAG) return;

	$tag['id'] = sha1_hex($tag_id);

	if ($obj->object  !== null) $tag['object'] = sha1_hex($obj->object);
	if ($obj->objtype !== null) $tag['type'] = Git::getTypeName($obj->objtype);
	if ($obj->tag     !== null) $tag['name'] = $obj->tag;
	if ($obj->tagger  !== null)
	{
		$tag['author'] = $obj->tagger->name;
		$tag['epoch'] = $obj->tagger->time;
		$tag['tz'] = sprintf("%+03d%02d",$obj->tagger->offset/3600,abs($obj->tagger->offset%3600/60));
	}
	$tag['comment'] = explode("\n",$obj->summary . "\n" . $obj->detail);
	if (!isset($tag['name']))
		return null;
	return $tag;
}

?>
