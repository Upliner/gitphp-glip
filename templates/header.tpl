{*
 *  header.tpl
 *  gitphp: A PHP git repository browser
 *  Component: Page header template
 *
 *  Copyright (C) 2006 Christopher Han <xiphux@gmail.com>
 *}
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
  <!-- gitphp-glip web interface {$version}, (C) 2006 Christopher Han <xiphux@gmail.com>, (C) 2010 Modified by Michael Vigovsky -->
  <head>
    <title>{$pagetitle}{if $project && $validproject} :: {$project}{if $action && $validaction}/{$action}{/if}{/if}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    {if $validproject}
      <link rel="alternate" title="{$project} log" href="{$SCRIPT_NAME}?p={$project}&a=rss" type="application/rss+xml" />
    {/if}
    <link rel="stylesheet" href="{$stylesheet}" type="text/css" />
    {$smarty.capture.header}
  </head>
  <body>
    <div class="page_header">
      <a href="http://www.kernel.org/pub/software/scm/git/docs/" title="git documentation">
        <img src="git-logo.png" width="72" height="27" alt="git" class="logo" />
      </a>
      <a href="index.php">{$maintitle}</a> / 
      {if $project && $validproject}
        <a href="{$SCRIPT_NAME}?p={$project}&a=summary">{$project}</a>
        {if $action && $validaction}
           / {$action}
        {/if}
      {/if}
    </div>
