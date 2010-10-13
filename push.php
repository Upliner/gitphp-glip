<?php
/*
 *  push.php
 *  Push through HTTP smart transfer (Git >= 1.6.6-rc0 only)
 *  Copyright (C) 2010 Michael Vigovsky <xvmv@mail.ru>
 */

/* Install exception error handler.
 * It allows to catch errors and report it properly through GIT protocol.
 * However, this doesn't catch fatal errors (such as memory exhaustion),
 * so this kind of errors needs to be debugged differently.
 */
function exception_error_handler($errno, $errstr, $errfile, $errline ) {
	if (error_reporting() === 0) return true;
	throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler("exception_error_handler");

// Read GIT protocol pkt-line
function read_pkt_line($f)
{
	$s = fread($f,4);
	list($count) = sscanf($s,"%04x");
	if ($count === 0) return null;
	if (!$count) throw new Exception("invalid pkt-line $count $s " . fread($f,32));
	$s =  fread($f,$count-4);
	return $s;
}
function die404($str)
{
	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
	header("Content-type: text/plain");
	die($str);
}

require_once('include/version.php');
require_once('include/defs.constants.php');
require_once('config/gitphp.conf.php');
require_once('glip/lib/glip.php');
require_once('glip/lib/git_index_pack.php');

$method = $_SERVER['REQUEST_METHOD'];

/* request url can be formed by following ways:
 * http://$host/$gitphp_path/push.php?path=$repo_name/$vfile
 * http://$host/$gitphp_path/push.php/$repo_name/$vfile
 */
if (isset($_GET["path"]))
{
	if (!preg_match('#([^/]*)/(.*)#',$_GET["path"],$parts)) die404("Invalid path");
} else
{
	if (!preg_match('#.*push.php/([^/]*)/(.*)#',$_SERVER["REQUEST_URI"],$parts)) die404("Invalid query address");
}

$repo_name = $parts[1]; // repo name
$vpath     = $parts[2]; // file name in virtual file system

$repo_dir = $gitphp_conf['projectroot'] . $repo_name . '/';

/* HTTP smart transfer uses only following queries:
 * (*) GET  .../info/refs?service=git-receive-pack
 * (*) POST .../git-receive-pack
 *
 * If you get errors like "cannot read info/refs" or somthing about
 * PROPGET, update your Git. You need version 1.6.6-rc0 or newer to be
 * able to push into GitPHP repos.
 */
if ($method=="HEAD") $method = "GET";
if ($method=="GET" && substr($vpath,0,9) == "info/refs")
{
	//////////////////////////////////////////////
	// List all references in pkt-line format
	echo "001f# service=git-receive-pack\n0000";
	$flag = true;
	$refcache = new GitRefCache($repo_dir);
	$refs = $refcache->refs;
	ksort($refs);
	if (count($refs) === 0) $refs["capabilities^{}"] = str_repeat(chr(0),20);
	foreach ($refs as $ref => $hash)
	{
		$line = sha1_hex($hash) . " $ref";
		if ($flag)
		{
			$line .= "\0report-status ofs-delta delete-refs";
			$flag = false;
		}
		$line .= "\n";
		echo sprintf("%04x%s",strlen($line)+4,$line);
	}
	echo "0000";
	return;
}
if ($method == "POST" && $vpath = "git-receive-pack")
{
	//////////////////////////////////////////////
	// Receive the pack
	try
	{
		header("Content-Type: application/x-git-receive-pack-result");
		$refs = array();
		$inp = fopen("php://input","rb");
		// read references list from stream
		while (($s = read_pkt_line($inp)) !== null)
		$refs[] = $s;

		if (!feof($inp))
		{
			// create temporary packfile
			$rands = str_pad(mt_rand(0,999999999),9,"0",STR_PAD_LEFT);
			$tmpname = $repo_dir . "objects/tmp-$rands.pack";
			$tmpf = fopen($tmpname,"xb");
			flock($tmpf,LOCK_EX);
			while (!feof($inp))
			{
				$s = fread($inp,0x10000);
				fwrite($tmpf,$s);
			}
			fclose($tmpf);

			// index the received pack
			$git = new Git(rtrim($repo_dir,"\\"));
			$indexer = new GitIndexPack($git);
			$indexer->indexPack($tmpname);
	    		$line = "unpack ok\n";
			echo sprintf("%04x%s",strlen($line)+4,$line);
		}
	} catch (Exception $e)
	{
		@unlink($tmpname);
		$line = "unpack " . $e->__toString();
		echo sprintf("%04x%s",strlen($line)+4,$line);
		echo "0000";
		return;
	}

	try
	{
		// update references
		foreach ($refs as $refline)
		{
			// trim out everything after first null
			$nullpos = strpos($refline,"\0");
			if ($nullpos !== false)
				$refline = substr($refline,0,$nullpos);

			list($oldhash, $newhash, $uref) = explode(" ", $refline);

			//check refline for correctness
			if (is_null($uref))
				throw new Exception("Invalid refline $refline");
			if (!is_valid_sha1($oldhash) || !is_valid_sha1($newhash))
			{
				$line = "ng $uref invalid sha1 hash\n";
				echo sprintf("%04x%s",strlen($line)+4,$line);
				continue;
			}
			if (substr($uref,0,5) !== "refs/" || strpos($uref,"..") !== false)
			{
				$line = "ng $uref invalid ref\n";
				echo sprintf("%04x%s",strlen($line)+4,$line);
				continue;
			}

			// check the oldhash
			if ($oldhash !== str_repeat('0',40))
			{
				$rfile = fopen($repo_dir . $uref,"r+");
				flock($rfile,LOCK_EX);
				$oldhash_check = trim(stream_get_contents($rfile));
				if ($oldhash !== $oldhash_check)
				{
					$line = "ng $uref oldhash mismatch\n";
					echo sprintf("%04x%s",strlen($line)+4,$line);
					fclose($rfile);
					continue;
				}
			} else
			{
				$rfile = fopen($repo_dir . $uref,"w+");
				flock($rfile,LOCK_EX);
			}

			// write a record into reflog
			$rlog = fopen($repo_dir . "logs/" . $uref, "a");
			fwrite($rlog,"$oldhash $newhash gitphp-glip " . time() . " +0000 push");
			fclose($rlog);

			// update ref
			if ($newhash === str_repeat('0',40))
			{
				fclose($rfile);
				unlink($repo_dir . $uref);
			} else
			{
				fseek($rfile,0);
				ftruncate($rfile,0);
				fwrite($rfile,$newhash . "\n");
				fclose($rfile);
			}

			$line = "ok $uref\n";
			echo sprintf("%04x%s",strlen($line)+4,$line);
			unset($uref);
		}
		//update info/refs
		$f = fopen($repo_dir . "info/refs","w");
		$refcache = new GitRefCache($repo_dir);
		$refs = $refcache->refs;
		ksort($refs);
		foreach ($refs as $ref => $hash)
			fwrite($f,sha1_hex($hash) . "\t$ref\n");
		fclose($f);

		// update objects/info/packs
		$f = fopen($repo_dir . "objects/info/packs","w");
		foreach (glob($repo_dir . "objects/pack/pack-*.pack") as $pack)
		{
			fwrite($f, sprintf("P %s\n", basename($pack)));
		}
		fwrite($f,"\n");
		fclose($f);
		echo "0000";
	} catch (Exception $e)
	{
		$line = $e->__toString();
		echo sprintf("%04x%s",strlen($line)+4,$line);
		echo "0000";
		return;
	}
	return;
}
die404("Invalid query address");
