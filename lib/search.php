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
            'work_mobile', 'home_mobile', 'branch');
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
      
      /* Paged result or not */ 
      $use_paged_result=FALSE;
      /* Paged results are said to work on php>=5.4.0, but it doesn't */
      if(version_compare(PHP_VERSION, '5.4.0', '>=') && FALSE) {
         $use_paged_result=TRUE;
      }
      $max_size=0;
      if(!ldap_get_option($ldap, LDAP_OPT_SIZELIMIT, $max_size)) {
         $max_size=100;
      }
      if(intval($max_size)<=0) $max_size=100;

      foreach($bases as $base) {
         $cookie='';
         do {
            if($use_paged_result) {
               if(!ldap_control_paged_result($ldap, $max_size, FALSE, 
                        $cookie)) {
                  $use_paged_result=FALSE;
                  $cookie='';
                  ldap_set_option($ldap, LDAP_OPT_SIZELIMIT, 0);
               }
            } else {
               ldap_set_option($ldap, LDAP_OPT_SIZELIMIT, 0);
               $cookie='';
            }
            
            $s_res=@ldap_search($ldap, $base, $ldap_search_filter,
                  $ldap_attrs);
            if($s_res) {
               $entries=ldap_get_entries($ldap, $s_res);
               for($i=0;$i<$entries['count'];$i++) {
                  $_sent=saddr_makeSmartyEntry($saddr, $entries[$i]);
                  if(isset($_sent['name'])) {
                     $smarty_entries[]=$_sent;
                  }
               }
               if($use_paged_result) {
                  if(!ldap_control_paged_result_response($ldap, $s_res,
                           $cookie)) {
                     $cookie='';
                  }
               }
            } else {
               $cookie='';
            }
         } while($cookie!==NULL && $cookie!='');
      }
   }

   return $smarty_entries;
}

?>
