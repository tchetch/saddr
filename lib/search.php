<?PHP
/* (c) 2012 Etienne Bagnoud
   This file is part of saddr project. saddr is under the MIT license.

   See LICENSE file
 */

function saddr_search(&$saddr, $search, $search_on=array(), $attrs=array())
{
   $smarty_entries=array();

   if(empty($attrs)) {
      $attrs=array('name', 'displayname', 'work_telephone', 'work_email', 
            'home_telephone', 'home_email', 'work_fax', 'home_fax',
            'work_mobile', 'home_mobile');
   }
   if(empty($search_on)) {
      $search_on=array('name', 'company', 'displayname', 'lastname', 'firstname', 
            'tags', 'home_email', 'work_email');
   }

   /* Attribute we search for added to attributes asked for */
   foreach($search_on as $sattr) {
      if(!in_array($sattr, $attrs)) {
         $attrs[]=$sattr;
      }
   }
   
   $ldap_attrs=saddr_getLdapAttr($saddr, $attrs);
   if(empty($ldap_attrs)) return array();

   $ldap_search_filter=saddr_getSearchFilter($saddr, $search_on, $search);
   if(!empty($ldap_search_filter)) {
      
      $ldap=saddr_getLdap($saddr);
      $bases=saddr_getLdapBase($saddr);
      
      foreach($bases as $base) {
         $s_res=ldap_search($ldap, $base, $ldap_search_filter, $ldap_attrs);
      
         if($s_res) {
            $entries=ldap_get_entries($ldap, $s_res);
            for($i=0;$i<$entries['count'];$i++) {
               $_sent=saddr_makeSmartyEntry($saddr, $entries[$i]);
               if(isset($_sent['name'])) {
                  $smarty_entries[]=$_sent;
               }
            }
         }
      }
   }

   return $smarty_entries;
}

?>
