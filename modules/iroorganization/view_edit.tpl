<h1>{$saddr.search_results.company.0}</h1>

<div id="saddrBusiness" class="saddr_section saddr_sectionLeft">
<h2>Business</h2>
{saddr_entry e="company" label="Company" searchable=1}
{saddr_entry e="branch" label="Branch"}
{saddr_entry e="work_address" label="Address" type="textarea"}
{saddr_entry e="work_npa" label="Postal code"}
{saddr_entry e="work_city" label="City" searchable=1}
{saddr_entry e="work_state" label="State" searchable=1}
{saddr_entry e="work_country" label="Country"}
{saddr_entry e="work_telephone" label="Telephone" labelonview=1 multi=1}
{saddr_entry e="work_fax" label="Fax" labelonview=1 multi=1}
</div>

<div id="saddrOthers" class="saddr_section saddr_sectionRight">
<h2>Others</h2>
{saddr_entry e="description" label="Description" type="textarea"}
</div>

<div id="saddrInternet" class="saddr_section saddr_sectionLeft">
<h2>Internet</h2>
{saddr_entry e="work_email" label="Professional email" labelonview=1 multi=1}
{saddr_entry e="url" label="URL" labelonview=1 multi=1}
</div>
