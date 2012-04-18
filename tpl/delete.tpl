{if isset($saddr.search_results)}
{assign var=e value=$saddr.search_results}
<h1 class="dangerous">You're about to delete</h1>
<p>Your about to delete <a href="{saddr_url op="view" id=$e.id}" 
title="View {$e.name.0}">{if isset($e.displayname)}{$e.displayname.0}{else}
{$e.name.0}{/if}</a>. This operation cannot be undone.</p>

<p>Are you certain that this entry is useless enough to be permanently deleted ?</p>

<p><a href="{saddr_url op="doDelete" timed_id=$saddr.__delete}" 
  title="Delete {$e.name.0}">Yes </a>
<a href="{saddr_url op="view" id=$e.id}" title="View {$e.name.0}">No</a></p>
{/if}
