<h1>saddr</h1>
<h2>Add new person</h2>
<form method="post" action="index.php?op=preAdd">
<input type="hidden" name="module" value="iroperson" />
<label for="firstname">Firstname : </label><input type="text" value="" name="firstname" /> <label for="lastname">Lastname : </label><input type="text" value="" name="lastname" /><br />
<input type="submit" name="goToAdd" value="Next" />
</form>

<h2>Add new bank</h2>
<form method="post" action="index.php?op=preAdd">
<input type="hidden" name="module" value="irobank" />
<label for="Company">Bank : </label><input type="text" value="" name="company" /><br />
<input type="submit" name="goToAdd" value="Next" />
</form>

<h2>Add new company</h2>
<form method="post" action="index.php?op=preAdd">
<input type="hidden" name="module" value="iroorganization" />
<label for="Company">Company : </label><input type="text" value="" name="company" /><br />
<input type="submit" name="goToAdd" value="Next" />
</form>
