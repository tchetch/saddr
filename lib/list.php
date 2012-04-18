<?PHP
/* (c) 2012 Etienne Bagnoud
   This file is part of saddr project. saddr is under the MIT license.

   See LICENSE file
 */

function saddr_list(&$saddr, $module, $attrs=array())
{
   /* If name is not asked, entry is counted as invalid */
   if(!empty($attrs) && !isset($attrs['name'])) $attrs[]='name';

   if(!saddr_isModuleAvailable($saddr, $module)) return array();
   $ldap_attrs=saddr_getLdapAttr($saddr, $attrs, $module);
   if(empty($ldap_attrs)) return array();

   $oc=saddr_getClass($saddr, $module);
   $ldap_search_filter='(objectclass='.$oc['search'].')';

   $ldap=saddr_getLdap($saddr);
   $s_res=ldap_search($ldap, saddr_getLdapBase($saddr), $ldap_search_filter,
         $ldap_attrs);
   
   $smarty_entries=array();
   if($s_res) {
      $entries=ldap_get_entries($ldap, $s_res);
      for($i=0;$i<$entries['count'];$i++) {
         $_sent=saddr_makeSmartyEntry($saddr, $entries[$i]);
         if(isset($_sent['name'])) {
            $smarty_entries[]=$_sent;
         }
      }
   }

   return $smarty_entries;
}

?>
