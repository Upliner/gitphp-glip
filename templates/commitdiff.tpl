{*
 *  commitdiff.tpl
 *  gitphp: A PHP git repository browser
 *  Component: Commitdiff view template
 *
 *  Copyright (C) 2009 Christopher Han <xiphux@gmail.com>
 *}

 {include file='header.tpl'}

 {* Nav *}
 <div class="page_nav">
   <a href="{$SCRIPT_NAME}?p={$project}&a=summary">summary</a> | <a href="{$SCRIPT_NAME}?p={$project}&a=shortlog&h={$hash}">shortlog</a> | <a href="{$SCRIPT_NAME}?p={$project}&a=log&h={$hash}">log</a> | <a href="{$SCRIPT_NAME}?p={$project}&a=commit&h={$hash}">commit</a> | commitdiff | <a href="{$SCRIPT_NAME}?p={$project}&a=tree&h={$tree}&hb={$hash}">tree</a><br /><a href="{$SCRIPT_NAME}?p={$project}&a=commitdiff_plain&h={$hash}&hp={$hashparent}">plain</a>
 </div>


 <div class="header">
   <a href="{$SCRIPT_NAME}?p={$project}&a=commit&h={$hash}" class="title">{$title}{if $commitref} <span class="tag">{$commitref}</span>{/if}</a>
 </div>
 
 <div class="author_date">
    {$commiter} [{$rfc2822}]
 </div>
 
 <div class="page_body">
   <div class="list_head"></div>
   
   <table class="diff_tree">
    <tbody>
        {foreach from=$difftreelines item=diff}
        <tr class="dark">
            <td><a href="{$SCRIPT_NAME}?p={$project}&a=blob&h={$hash}&f={$difff.file}">{$diff.file}</a></td>
            <td></td>
            <td class="link">  <a href="#patch{$diff.md5}">patch</a> 
                        | <a href="{$SCRIPT_NAME}?p={$project}&a=blob&h={$hash}&f={$difff.file}">blob</a>
                       <!--  | <a href={$SCRIPT_NAME}?p={$project}&a=history&h={$hash}&f={$difff.file}">history</a> --></td>
        </tr>
        {/foreach}
    </tbody></table>
   
   
   
   {* Diff each file changed *}
   {section name=difftree loop=$difftreelines}
    <div class="header"> diff --git
     {if $difftreelines[difftree].status == "A"}
        <a href="{$SCRIPT_NAME}?p={$project}&a=blob&h={$difftreelines[difftree].to_id}&hb={$hash}&f={$difftreelines[difftree].file}">{if $difftreelines[difftree].file}{$difftreelines[difftree].file}{else}{$difftreelines[difftree].to_id}{/if}</a>(new)
     {elseif $difftreelines[difftree].status == "D"}
         <a href="{$SCRIPT_NAME}?p={$project}&a=blob&h={$difftreelines[difftree].from_id}&hb={$hash}&f={$difftreelines[difftree].file}">{if $difftreelines[difftree].file}{$difftreelines[difftree].file}{else}{$difftreelines[difftree].from_id}{/if}</a>(deleted)
     {elseif $difftreelines[difftree].status == "M"}
       {if $difftreelines[difftree].from_id != $difftreelines[difftree].to_id}
	   <a href="{$SCRIPT_NAME}?p={$project}&a=blob&h={$difftreelines[difftree].from_id}&hb={$hash}&f={$difftreelines[difftree].file}">{if $difftreelines[difftree].file}a/{$difftreelines[difftree].file}{else}{$difftreelines[difftree].from_id}{/if}</a>  <a href="{$SCRIPT_NAME}?p={$project}&a=blob&h={$difftreelines[difftree].to_id}&hb={$hash}&f={$difftreelines[difftree].file}">{if $difftreelines[difftree].file}b/{$difftreelines[difftree].file}{else}{$difftreelines[difftree].to_id}{/if}</a>
       {/if}
     {/if}
     </div>
     
     {include file='filediff.tpl' diff=$difftreelines[difftree].diffout}
     
   {/section}
 </div>

 {include file='footer.tpl'}

