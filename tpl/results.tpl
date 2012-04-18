<h1>Search results</h1>
{if isset($saddr.search_results)}
<table class="saddrSearchResults">
<tr>
	<th>Name</th>
	<th>Mail</th>
	<th>Telephone</th>
	<th>Mobile</th>
</tr>
{foreach $saddr.search_results as $res}
<tr class="{if $res@index % 2 == 0}odd{else}even{/if}">
	<td class="name"><a href="index.php?op=view&id={$res.id}" title="View details">{if isset($res.displayname)}{$res.displayname.0}
		{else}{$res.name.0}{/if}</a>
		</td>
	<td class="mail">{if isset($res.work_email)}<a href="mailto:{$res.work_email.0}">{$res.work_email.0}</a>
		{elseif isset($res.home_email)}<a href="mailto:{$res.home_email.0}">{$res.home_email.0}</a>
		{else}{/if}</td>
	<td>{if isset($res.work_telephone)}<a href="callto:{$res.work_telephone.0}">{$res.work_telephone.0}</a>
		{elseif isset($res.home_telephone)}<a href="callto:{$res.home_telephone.0}">{$res.home_telephone.0}</a>
		{else}{/if}</td>
	<td>{if isset($res.work_mobile)}<a href="callto:{$res.work_mobile.0}">{$res.work_mobile.0}</a>
		{elseif isset($res.home_mobile)}<a href="callto:{$res.home_mobile.0}">{$res.home_mobile.0}</a>
		{else}{/if}</td>
</tr>
{if isset($res.company)}
<tr class="{if $res@index % 2 == 0}odd{else}even{/if}">
<td class="last"></td>
<td colspan="3" class="last">
<a href="index.php?op=doSearchForCompany&search={saddr_encrypt value=$res.company.0}"
 title="Search for {$res.company.0}">{$res.company.0}</a>
</td>
</tr>
{/if}
{/foreach}
</table>
{else}
<p>No results</p>
{/if}
