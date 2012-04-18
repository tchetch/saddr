<h1>{$saddr.search_results.name.0}</h1>

<div id="saddrNames" class="saddr_section saddr_sectionLeft">
{saddr_entry e="title" label="Title"}
{saddr_entry e="firstname" label="Firstname" multi=1}
{saddr_entry e="lastname" label="Lastname" searchable=1}
</div>

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

<div id="saddrInternet" class="saddr_section saddr_sectionLeft">
<h2>Internet</h2>
{saddr_entry e="work_email" label="Professional email" labelonview=1 multi=1}
{saddr_entry e="url" label="URL" labelonview=1 multi=1}
</div>

{saddr_when_module_available module="irobank"}
<div id="saddrBanking" class="saddr_section saddr_sectionRight">
<h2>Banking</h2>
<p>
{saddr_entry e="bank" label="Establishment" 
   type="sselect" module="irobank"
   attributes=array('company', 'branch') format="%s, %s"}
{saddr_entry e="iban" label="IBAN" labelonview=1}
</div>
{/saddr_when_module_available}

<div id="saddrOthers" class="saddr_section saddr_sectionRight">
<h2>Others</h2>
{saddr_entry e="description" label="Description" type="textarea"}
{saddr_entry e="tags" label="Tags" labelonview=1 type="tag"}
{saddr_entry e="restricted_tags" label="Restricted tags" labelonview=1 type="tag"}
</div>