<?PHP

function saddr_delete(&$saddr, $dn)
{
   return ldap_delete(saddr_getLdap($saddr), $dn);
}

?>
