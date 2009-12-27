<?php
/*
 *  display.git_blob.php
 *  gitphp: A PHP git repository browser
 *  Component: Display - blob
 *
 *  Copyright (C) 2008 Christopher Han <xiphux@gmail.com>
 *  Copyright (C) 2009 Michael Vigovsky <xvmv@mail.ru>
 */

 require_once('glip/lib/glip.php');
 require_once('glip.git_read_head.php');
 require_once('glip.git_get_hash_by_path.php');
 require_once('glip.git_read_commit.php');
 require_once('glip.git_path_trees.php');
 require_once('glip.read_info_ref.php');
 require_once('util.file_mime.php');

function git_blob($projectroot, $project, $hash, $file, $hashbase)
{
	global $gitphp_conf,$tpl;

	$cachekey = sha1($project) . "|" . $hashbase . "|" . $hash . "|" . sha1($file);

	$git = new Git($projectroot . $project);

	$hash = $git->revParse($hash);
	$hashbase = $git->revParse($hashbase);

	if (!$tpl->is_cached('blob.tpl',$cachekey)) {
		$head = git_read_head($git);
		if (!isset($hashbase))
			$hashbase = $head;
		if (!isset($hash) && isset($file))
			$hash = git_get_hash_by_path($git, $git->getObject($hashbase),$file,"blob");
		$catout = $git->getObject($hash)->data;
		$tpl->assign("hash",sha1_hex($hash));
		$tpl->assign("hashbase",sha1_hex($hashbase));
		$tpl->assign("head", sha1_hex($head));
		if ($co = git_read_commit($git, $hashbase)) {
			$tpl->assign("fullnav",TRUE);
			$refs = read_info_ref($git);
			$tpl->assign("tree",$co['tree']);
			$tpl->assign("title",$co['title']);
			if (isset($file))
				$tpl->assign("file",$file);
			if (isset($refs[$hashbase]))
				$tpl->assign("hashbaseref",$refs[$hashbase]);
		}
		$paths = git_path_trees($git, $git->getObject($hashbase), $file);
		$tpl->assign("paths",$paths);

		if ($gitphp_conf['filemimetype']) {
			$mime = file_mime($catout,$file);
			if ($mime)
				$mimetype = strtok($mime, "/");
		}

		if ($mimetype == "image") {
			$tpl->assign("mime", $mime);
			$tpl->assign("data", base64_encode($catout));
		} else {
			$usedgeshi = $gitphp_conf['geshi'];
			if ($usedgeshi) {
				$usedgeshi = FALSE;
				include_once($gitphp_conf['geshiroot'] . "geshi.php");
				if (class_exists("GeSHi")) {
					$geshi = new GeSHi("",'php');
					if ($geshi) {
						$lang = "";
						if (isset($file))
							$lang = $geshi->get_language_name_from_extension(substr(strrchr($file,'.'),1));
						if (isset($lang) && (strlen($lang) > 0)) {
							$geshi->set_source($catout);
							$geshi->set_language($lang);
							$geshi->set_header_type(GESHI_HEADER_DIV);
							$geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS);
							$tpl->assign("geshiout",$geshi->parse_code());
							$usedgeshi = TRUE;
						}
					}
				}
			}

			if (!$usedgeshi) {
				$lines = explode("\n",$catout);
				$tpl->assign("lines",$lines);
			}
		}
	}

	$tpl->display('blob.tpl', $cachekey);
}

?>
