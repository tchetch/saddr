<?xml version="1.0" encoding="utf-8" ?>
<!DOCTYPE html
	PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" 
	lang="en">
<head>
<script src="{saddr_dojo}" data-dojo-config="parseOnLoad: true" ></script>
<script>
dojo.require('dijit.form.Form');
dojo.require('dijit.form.TextBox');
dojo.require('dijit.form.NumberTextBox');
dojo.require('dijit.form.Textarea');
dojo.require('dijit.form.DateTextBox');
dojo.require('dijit.form.FilteringSelect');
dojo.require('dijit.Tooltip');
</script>
<title>saddr</title>
<link rel="stylesheet" href="css/default/default.css" type="text/css" />
<link rel="stylesheet" href="{saddr_dijit_theme_path}" type="text/css" />
</head>
<body class="{saddr_dijit_theme_name}">
{include file="header.tpl"}

<div id="saddr_content">
{if isset($saddr) && isset($saddr.display)}
	{if isset($saddr) && isset($saddr.search_results) && 
	  isset($saddr.search_results.__edit)}
     <div id="edit">
		<form method="post" action="{saddr_url op="doAddOrEdit"}" 
			data-dojo-type="dijit.form.Form" 
			onsubmit="if(this.validate()) { return true; } else { return false; }">
		{if isset($saddr.search_results.id)}<input type="hidden" name="id" 
			value="{$saddr.search_results.id}" />{/if}
		{if isset($saddr.search_results.module)}<input type="hidden" 
			name="module" value="{$saddr.search_results.module}" />{/if}
		{include file=$saddr.display}
		<div id="saddr_buttonsContainer">
		<input type="submit" value="Ok" name="saddr_goAddEdit"/>
		</div>
		</form>
      </div>
	{else}
		{include file=$saddr.display}
	{/if}
{else}
	{include file="home.tpl"}
{/if}
</div>

{include file="footer.tpl"}
</body>
</html>
