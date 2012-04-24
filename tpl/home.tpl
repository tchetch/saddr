<div class="saddr_section saddr_sectionLeft">
{foreach $saddr.__home_display as $home}
<form method="post" action="{saddr_url op="preAdd"}">
<input type="hidden" name="module" value="{$home.1}" />
{include file=$home.0}
<input type="submit" name="goToAdd" value="Next" />
</form>
{/foreach}
</div>

<div class="saddr_section saddr_sectionRight">
<h1>List</h1>
<ul>
{foreach $saddr.__home_display as $home}
<li><a href="{saddr_url op="list" module=$home.1 encrypt=1}">All {$home.1}</a></li>
{/foreach}
</ul>
</div>
