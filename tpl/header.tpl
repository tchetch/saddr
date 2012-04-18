<!-- HEADER -->
<div id="saddr_searchBoxes">
<form method="POST" action="index.php" name="saddr_search">
<span id="saddr_title">saddr</span>
<span id="saddr_toolbox">
<span><a href="index.php" title="Back home">Home</a></span>
{if isset($saddr.op) && 
	isset($saddr.search)}
<span><a href="index.php?op={$saddr.op}&search={$saddr.search}"
	title="Search again">Search again</a></span>
{/if}
{if isset($saddr.search_results) &&
   isset($saddr.search_results.id)}
<span>
{if !isset($saddr.search_results.__edit)}
   <a href="index.php?op=addOrEdit&id={$saddr.search_results.id}"
   title="Edit {$saddr.search_results.name.0}">Edit</a>
{else}
   <a href="index.php?op=view&id={$saddr.search_results.id}"
   title="View {$saddr.search_results.name.0}">View</a>
{/if}
</span>
{/if}

{if isset($saddr.__delete)}
<span class="dangerous"><a href="index.php?op=delete&timed_id={$saddr.__delete}"
    title="Delete entry" class="dangerous">Delete</a></span>
{/if}

</span>

Search : <input type="text" name="saddrGlobalSearch" />
Tag : <input type="text" name="saddrTagSearch" />
<input type="submit" value="Search" name="saddrGoSearch" />
</form>
</div>
{if isset($saddr.handle.user_messages)}
<div id="saddr_messageContainer">
{foreach $saddr.handle.user_messages as $msg}
<div class="saddr_message {saddr_class_message errno=$msg.level}">{$msg.msg}</div>
{/foreach}
</div>
{/if}

