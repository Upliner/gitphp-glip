{*
 *  projectlist.tpl
 *  gitphp: A PHP git repository browser
 *  Component: Project list template
 *
 *  Copyright (C) 2009 Christopher Han <xiphux@gmail.com>
 *}

{include file='header.tpl'}



{if $message}
  {* Something is wrong; display an error message instead of trying to list *}
  {include file='message.tpl'}
{else}
  <table cellspacing="0" class="project_list">
    {* Header *}
    <tr>
      {if $order == "project"}
        <th>Project</th>
      {else}
        <th><a class="header" href="{$SCRIPT_NAME}?o=project">Project</a></th>
      {/if}
      {if $order == "descr"}
        <th>Description</th>
      {else}
        <th><a class="header" href="{$SCRIPT_NAME}?o=descr">Description</a></th>
      {/if}
      {if $order == "owner"}
        <th>Owner</th>
      {else}
        <th><a class="header" href="{$SCRIPT_NAME}?o=owner">Owner</a></th>
      {/if}
      {if $order == "age"}
        <th>Last Change</th>
      {else}
        <th><a class="header" href="{$SCRIPT_NAME}?o=age">Last Change</a></th>
      {/if}
      <th>Actions</th>
    </tr>

    {if $categorizedprojects}
      {* Show categorized; categorized project lists nested associatively in the project
         list by category key *}
      {foreach from=$categorizedprojects key=categ item=plist}
        {if $categ != "none"}
          <tr>
            <th>{$categ}</th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
          </tr>
	{/if}
        {section name=proj loop=$plist}
          <tr class="{cycle values="light,dark"}">
            <td>
              <a href="{$SCRIPT_NAME}?p={$plist[proj].project}&a=summary" class="list {if $categ != "none"}indent{/if}">{$plist[proj].project}</a>
            </td>
            <td><a href="{$SCRIPT_NAME}?p={$plist[proj].project}&a=summary" class="list">{$plist[proj].descr}</a></td>
            <td><i>{$plist[proj].owner}</i></td>
            <td class="{$plist[proj].age_class}">
            {if $plist[proj].age == false}
               No commits
            {else}
                {$projects[proj].age_string} 
            {/if}
           
            </td>
            <td class="link"><a href="{$SCRIPT_NAME}?p={$plist[proj].project}&a=summary">summary</a> | <a href="{$SCRIPT_NAME}?p={$plist[proj].project}&a=shortlog">shortlog</a> | <a href="{$SCRIPT_NAME}?p={$plist[proj].project}&a=log">log</a> | <a href="{$SCRIPT_NAME}?p={$plist[proj].project}&a=tree">tree</a> | <a href="{$SCRIPT_NAME}?p={$plist[proj].project}&a=snapshot&h=HEAD">snapshot</a></td>
          </tr>
        {/section}
      {/foreach}

    {else}
      
      {* Show flat uncategorized project array *}
      {section name=proj loop=$projects}
        <tr class="{cycle values="light,dark"}">
          <td>
            {if $projects[proj].age == false}
               {$projects[proj].project}
            {else}
                <a href="{$SCRIPT_NAME}?p={$projects[proj].project}&a=summary" class="list">{$projects[proj].project}</a>
            {/if}
          </td>
          <td>
            {if $projects[proj].age == false}
              {$projects[proj].descr}
            {else}
              <a href="{$SCRIPT_NAME}?p={$projects[proj].project}&a=summary" class="list">{$projects[proj].descr}</a>
            {/if}
          </td>
          <td><em>{$projects[proj].owner}</em></td>
          <td class="{$projects[proj].age_class}">
            {if $projects[proj].age == false}
               No commits
            {else}
                {$projects[proj].age_string} 
            {/if}
          </td>
          <td class="link">
            {if $projects[proj].age == false}
             <em>No actions</em>
            {else}
            <a href="{$SCRIPT_NAME}?p={$projects[proj].project}&a=summary">summary</a> | 
            <a href="{$SCRIPT_NAME}?p={$projects[proj].project}&a=shortlog">shortlog</a> | 
            <a href="{$SCRIPT_NAME}?p={$projects[proj].project}&a=log">log</a> | 
            <a href="{$SCRIPT_NAME}?p={$projects[proj].project}&a=tree">tree</a> | 
            <a href="{$SCRIPT_NAME}?p={$projects[proj].project}&a=snapshot&h=HEAD">snapshot</a>
            {/if}
         </td>
        </tr>
      {/section}

    {/if}

  </table>
{/if}

{include file='footer.tpl'}

