<?php
/*
 *  glip.git_diff.php
 *  gitphp: A PHP git repository browser
 *  Component: Git utility - diff
 *
 *  Copyright (C) 2009 Christopher Han <xiphux@gmail.com>
 *  Copyright (C) 2010 Michael Vigovsky <xvmv@mail.ru>
 */

require_once('glip/lib/glip.php');
require_once('wikidiff/wikidiff.php');

function git_diff($proj,$from,$from_name,$to,$to_name)
{
	$fromdata = $from ? $proj->GetObject($from)->data : "";
	$todata   = $to   ? $proj->GetObject($to)->data : "";
	$fromdata = str_replace("\r\n","\n",$fromdata);
	$todata = str_replace("\r\n","\n",$todata);
	$diff = new Diff(explode("\n",$fromdata),explode("\n",$todata));
	$diffFormatter = new UnifiedDiffFormatter();
	$diffFormatter->leading_context_lines = 3;
	$diffFormatter->trailing_context_lines = 3;

	$out = "--- $from_name\n+++ $to_name\n" .
		$diffFormatter->format($diff);
	return $out;
}

?>
