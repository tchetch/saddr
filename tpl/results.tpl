<h1>Search results</h1>
{if isset($saddr.search_results)}
{foreach $saddr.search_results as $e}
<div class="saddr_resultLine {if $e@iteration is odd by 1}saddr_resultLineOdd{/if}">
<span class="saddr_res0 saddr_resField"
  ><a href="{saddr_url op="view" id=$e.id}" title="View {$e.name.0}">
{if isset($e._saddr_res0)}{$e._saddr_res0.0}{else}{$e.name.0}{/if}</a>
</span>
<span class="saddr_res1 saddr_resField"
  >{if isset($e._saddr_res1)}{$e._saddr_res1.0}{/if}</span>
<span class="saddr_res2 saddr_resField"
  >{if isset($e._saddr_res2)}{$e._saddr_res2.0}{/if}</span>
<span class="saddr_res3 saddr_resField"
  >{if isset($e._saddr_res3)}{$e._saddr_res3.0}{/if}</span>
</div>
{/foreach}
</table>
{else}
<p>No results</p>
{/if}
