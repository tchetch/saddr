<?PHP
/* (c) 2012 Etienne Bagnoud
   This file is part of saddr project. saddr is under the MIT license.

   See LICENSE file
 */

function saddr_delete(&$saddr, $dn)
{
   return ldap_delete(saddr_getLdap($saddr), $dn);
}

?>
