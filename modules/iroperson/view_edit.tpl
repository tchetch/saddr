{if isset($saddr.search_results.__edit)}
{if isset($saddr.search_results.name)}
<h1>Edit {$saddr.search_results.name.0}</h1> 
{else}
<h1>Add</h1>
{/if}
{else}
{if isset($saddr.search_results.name)}
<h1>{$saddr.search_results.name.0}</h1> 
{/if}
{/if}

{saddr_ifgroup group="names"}
<div id="saddrNames" class="saddr_section saddr_sectionLeft">
{saddr_entry e="title" label="Title"}
{saddr_entry e="firstname" label="Firstname" multi=1 must=1}
{saddr_entry e="lastname" label="Lastname" searchable=1 must=1}
</div>
{/saddr_ifgroup}

{saddr_ifgroup group="work"}
<div id="saddrBusiness" class="saddr_section saddr_sectionLeft">
<h2>Business</h2>
{saddr_entry e="company" label="Company" searchable=1 multi=1}
{saddr_entry e="work_address" label="Address" type="textarea"}
{saddr_entry e="work_npa" label="Postal code"}
{saddr_entry e="work_city" label="City" searchable=1}
{saddr_entry e="work_state" label="State" searchable=1}
{saddr_entry e="work_country" label="Country" }
{saddr_entry e="work_telephone" label="Telephone" labelonview=1 multi=1}
{saddr_entry e="work_fax" label="Fax" labelonview=1 multi=1}
</div>
{/saddr_ifgroup}

{saddr_ifgroup group="private"}
<div id="saddrPersonnal" class="saddr_section saddr_sectionRight">
<h2>Personnal</h2>
{saddr_entry e="home_address" label="Address" type="textarea"}
{saddr_entry e="home_npa" label="Postal code"}
{saddr_entry e="home_city" label="City" searchable=1}
{saddr_entry e="home_state" label="State" searchable=1}
{saddr_entry e="home_country" label="Country"}
{saddr_entry e="home_telephone" label="Telephone" labelonview=1 multi=1}
{saddr_entry e="home_mobile" label="Mobile" labelonview=1 multi=1}
{saddr_entry e="birthday" label="Birthday" labelonview=1 type="date"}
</div>
{/saddr_ifgroup}

{saddr_ifgroup group="internet"}
<div id="saddrInternet" class="saddr_section saddr_sectionLeft">
<h2>Internet</h2>
{saddr_entry e="work_email" label="Professional email" labelonview=1 multi=1}
{saddr_entry e="url" label="URL" labelonview=1 multi=1}
</div>
{/saddr_ifgroup}

{saddr_when_module_available module="irobank"}
{saddr_ifgroup group="bank"}
<div id="saddrBanking" class="saddr_section saddr_sectionRight">
<h2>Banking</h2>
<p>
{saddr_entry e="bank" label="Establishment" 
   type="sselect" module="irobank"
   format="##company## ##, ~branch##"}
{saddr_entry e="iban" label="IBAN" labelonview=1}
</div>
{/saddr_ifgroup}
{/saddr_when_module_available}

{saddr_ifgroup group="other"}
<div id="saddrOthers" class="saddr_section saddr_sectionRight">
<h2>Others</h2>
{saddr_entry e="description" label="Description" type="textarea"}
{saddr_entry e="tags" label="Tags" labelonview=1 type="tag"}
{saddr_entry e="restricted_tags" label="Restricted tags" labelonview=1 type="tag"}
</div>
{/saddr_ifgroup}
