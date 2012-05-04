<?PHP
/* (c) 2012 Etienne Bagnoud
   This file is part of saddr project. saddr is under the MIT license.

   See LICENSE file
 */

function saddr_search(&$saddr, $search, $search_on=array(), $attrs=array(),
      $search_op='=')
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

   $ldap_search_filter=saddr_getSearchFilter($saddr, $search_on, $search, 
         array(), $search_op);
   if(!empty($ldap_search_filter)) {
      
      $ldap=saddr_getLdap($saddr);
      $bases=saddr_getLdapBase($saddr);
      
      foreach($bases as $base) {
         $cookie=NULL;
         $estimated=0;
         $pagesize=50;
         $serverctrls=array();
         do {
            if(SADDR_USE_PAGED_RESULT) {
               tch_ldapCreatePagedResultControl($cookie, $pagesize, FALSE,
                  $serverctrls);
               ldap_set_option($ldap, LDAP_OPT_SERVER_CONTROLS, $serverctrls);
            }
            $s_res=@ldap_search($ldap, $base, $ldap_search_filter,
                  $ldap_attrs);
            if($s_res) {

               $errcode=LDAP_SUCCESS;    
               /* Need patch https://bugs.php.net/bug.php?id=61921 */ 
               if(SADDR_USE_PAGED_RESULT) {
                  if(ldap_parse_result($ldap, $s_res, $errcode, $matchdn, $errmsg,
                     $ref, $serverctrls)) {
                     if(!tch_ldapParsePagedResultControl($cookie, $estimated,
                           $serverctrls)) {
                        $cookie=NULL;
                     }
                  }
               } else {
                  ldap_parse_result($ldap, $s_res, $errcode, $matchdn, $errmsg,
                     $ref);
               }
               $process_results=FALSE;
               if($errcode != LDAP_SUCCESS) {
                  if($errcode == LDAP_SIZELIMIT_EXCEEDED ||
                        $errcode == LDAP_TIMELIMIT_EXCEEDED ||
                        $errcode == LDAP_PARTIAL_RESULTS) {
                     $process_results=TRUE;
                     saddr_setUserMessage($saddr, 'Partial result set', 
                           SADDR_MSG_WARNING);
                  }
               } else {
                  $process_results=TRUE;
               }

               if($process_results) {
                  $entries=ldap_get_entries($ldap, $s_res);
                  for($i=0;$i<$entries['count'];$i++) {
                     $_sent=saddr_makeSmartyEntry($saddr, $entries[$i]);
                     if(isset($_sent['name'])) {
                        $smarty_entries[]=$_sent;
                     }
                  }
               }
               ldap_free_result($s_res);
            } else {
               $cookie=NULL;
            }
         } while($cookie!==NULL && $cookie!='');
      }
   }

   return $smarty_entries;
}

?>
