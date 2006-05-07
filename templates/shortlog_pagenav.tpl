{*
 *  shortlog_page.tpl
 *  gitphp: A PHP git repository browser
 *  Component: Shortlog view page nav template
 *
 *  Copyright (C) 2006 Christopher Han <xiphux@gmail.com>
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Library General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 *}
 {if $headlink}<a href="{$SCRIPT_NAME}?p={$project}&a=shortlog">{/if}HEAD{if $headlink}</a>{/if} &sdot; {if $prevlink}<a href="{$SCRIPT_NAME}?p={$project}&a=shortlog&h={$hash}&pg={$prevpage}" accesskey="p" title="Alt-p">{/if}prev{if $prevlink}</a>{/if} &sdot; {if $nextlink}<a href="{$SCRIPT_NAME}?p={$project}&a=shortlog&h={$hash}&pg={$nextpage}" accesskey="n" title="Alt-n">{/if}next{if $nextlink}</a>{/if}
 <br />
 </div>
 <div>
 <a href="{$SCRIPT_NAME}?p={$project}&a=summary" class="title">&nbsp;</a>
 </div>
 <table cellspacing="0">