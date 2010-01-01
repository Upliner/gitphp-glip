<?php
/*
 *  display.git_commit.php
 *  gitphp: A PHP git repository browser
 *  Component: Display - commit
 *
 *  Copyright (C) 2008 Christopher Han <xiphux@gmail.com>
 *  Copyright (C) 2009 Michael Vigovsky <xvmv@mail.ru>
 */

 require_once('glip/lib/glip.php');
 require_once('util.file_type.php');
 require_once('util.date_str.php');
 require_once('glip.git_read_commit.php');
// require_once('gitutil.git_diff_tree.php');
 require_once('glip.read_info_ref.php');

function git_commit($projectroot,$project,$hash)
{
	global $tpl;

	$cachekey = sha1($project) . "|" . $hash;

	$git = new Git($projectroot . $project);
	$hash = $git->revParse($hash);

	if (!$tpl->is_cached('commit.tpl', $cachekey)) {
		$co = git_read_commit($git, $hash);
		$ad = date_str($co['author_epoch'],$co['author_tz']);
		$cd = date_str($co['committer_epoch'],$co['committer_tz']);
		if (isset($co['parent'])) {
			$a_tree = $git->getObject(sha1_bin($co['parent']))->getTree();
		} else {
			$a_tree = false;
		}
		$b_tree = $git->getObject(sha1_bin($co['tree']));
		$difftree = GitTree::diffTree($a_tree,$b_tree);
		ksort($difftree);

		$tpl->assign("hash",sha1_hex($hash));
		$tpl->assign("tree",$co['tree']);
		if (isset($co['parent']))
			$tpl->assign("parent",$co['parent']);
		$tpl->assign("title",$co['title']);
		$refs = read_info_ref($git);
		if (isset($refs[$hash]))
			$tpl->assign("commitref",$refs[$hash]);
		$tpl->assign("author",$co['author']);
		$tpl->assign("adrfc2822",$ad['rfc2822']);
		$tpl->assign("adhourlocal",$ad['hour_local']);
		$tpl->assign("adminutelocal",$ad['minute_local']);
		$tpl->assign("adtzlocal",$ad['tz_local']);
		$tpl->assign("committer",$co['committer']);
		$tpl->assign("cdrfc2822",$cd['rfc2822']);
		$tpl->assign("cdhourlocal",$cd['hour_local']);
		$tpl->assign("cdminutelocal",$cd['minute_local']);
		$tpl->assign("cdtzlocal",$cd['tz_local']);
		$tpl->assign("id",$co['id']);
		$tpl->assign("parents",$co['parents']);
		$tpl->assign("comment",$co['comment']);
		$tpl->assign("difftreesize",count($difftree)+1);
		$status_map = array(
			GitTree::TREEDIFF_ADDED   => "A",
			GitTree::TREEDIFF_REMOVED => "D",
			GitTree::TREEDIFF_CHANGED => "M");
		$difftreelines = array();
		foreach ($difftree as $file => $diff) {
			$difftreeline = array();
			$difftreeline["from_mode"] = decoct($diff->old_mode);
			$difftreeline["to_mode"]   = decoct($diff->new_mode);
			$difftreeline["from_mode_cut"] = substr(decoct($diff->old_mode),-4);
			$difftreeline["to_mode_cut"]   = substr(decoct($diff->new_mode),-4);
			$difftreeline["from_id"] = sha1_hex($diff->old_obj);
			$difftreeline["to_id"]   = sha1_hex($diff->new_obj);
			$difftreeline["status"] = $status_map[$diff->status];
			$difftreeline["similarity"] = "";
			$difftreeline["file"] = $file;
			$difftreeline["from_file"] = "";
			$difftreeline["from_filetype"] = "";
			$difftreeline["to_file"] = "";
			$difftreeline["to_filetype"] = "";
			$difftreeline["isreg"] = TRUE;

			$modestr = "";
			/*if ((octdec($regs[1]) & 0x17000) != (octdec($regs[2]) & 0x17000))
				$modestr .= " from " . file_type($regs[1]) . " to " . file_type($regs[2]);
			if ((octdec($regs[1]) & 0777) != (octdec($regs[2]) & 0777)) {
				if ((octdec($regs[1]) & 0x8000) && (octdec($regs[2]) & 0x8000))
					$modestr .= " mode: " . (octdec($regs[1]) & 0777) . "->" . (octdec($regs[2]) & 0777);
				else if (octdec($regs[2]) & 0x8000)
					$modestr .= " mode: " . (octdec($regs[2]) & 0777);*/
			$difftreeline["modechange"] = $modestr;
			$simmodechg = "";
			/*if ($regs[1] != $regs[2])
				$simmodechg .= ", mode: " . (octdec($regs[2]) & 0777);*/
			$difftreeline["simmodechg"] = $simmodechg;
			$difftreelines[] = $difftreeline;
		}
		$tpl->assign("difftreelines",$difftreelines);
	}
	$tpl->display('commit.tpl', $cachekey);
}

?>
