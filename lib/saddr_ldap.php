<?PHP

function saddr_prepareLdapConnection(&$saddr)
{
   $ldap_handel=NULL;

   $ldap_host=saddr_getLdapHost($saddr);
   if(is_string($ldap_host)) {
      $ldap_handle=ldap_connect($ldap_host);
      if($ldap_handle) {
         $root_dse=tch_getRootDSE($ldap_handle);
         if($root_dse) {

            /* Find and set LDAP Version */
            if(($ldap_version=
                     tch_getLdapVersion($ldap_handle, $root_dse))!==FALSE) {
               if(!ldap_set_option($ldap_handle, 
                        LDAP_OPT_PROTOCOL_VERSION, $ldap_version)) {
                  saddr_setError($saddr, SADDR_ERR_LDAP_VERSION, __FILE__,
                        __LINE__);
                  ldap_close($ldap_handle);
                  $ldap_handle=NULL;
               }
            } else {
               saddr_setError($saddr, SADDR_ERR_LDAP_VERSION, __FILE__, __LINE__);
               ldap_close($ldap_handle);
               $ldap_handle=NULL;
            }

            /* Find directory base, take the first found */
            if($ldap_handle!=NULL) {
               if(($bases=tch_getLdapBases($ldap_handle, $root_dse))!==FALSE) {
                  foreach($bases as $b) {
                     saddr_setLdapBase($saddr, $b);
                  }
               } else {
                  saddr_setError($saddr, SADDR_ERR_LDAP_BASE, __FILE__,
                        __LINE__);
                  ldap_close($ldap_handle);
                  $ldap_handle=NULL;
               }
            }

            /* Get objectclasses available in directory */
            if($ldap_handle!=NULL) {
               if(($oc=tch_getLdapObjectClasses($ldap_handle, 
                           $root_dse))!=FALSE) {
                  saddr_setLdapObjectClasses($saddr, $oc); 
               } else {
                  saddr_setError($saddr, SADDR_ERR_LDAP_OBJECTCLASS, __FILE__,
                        __LINE__);
                  ldap_close($ldap_handle);
                  $ldap_handle=NULL;
               }
            }

            /* Bind either with provided user/pass or anonymously */
            if($ldap_handle!=NULL) {
               $user=saddr_getUser($saddr);
               $pass=saddr_getPass($saddr);
               if(is_string($user) && is_string($pass)) {
                  if(ldap_bind($ldap_handle, $user, $pass)) {
                     saddr_setLdap($saddr, $ldap_handle);
                  } else {
                     saddr_setError($saddr, SADDR_ERR_LDAP_BIND, __FILE__,
                           __LINE__);
                     saddr_setUserMessage($saddr, 'Authentication failed');
                     ldap_close($ldap_handle);
                     $ldap_handle=NULL;
                  }
               } else {
                  /* Anonymous bind */
                  if(ldap_bind($ldap_handle)) {
                     saddr_setLdap($saddr, $ldap_handle);
                     saddr_setUserMessage($saddr, 'Anonmyous connection',
                           SADDR_MSG_WARNING);
                  } else {
                     saddr_setError($saddr, SADDR_ERR_LDAP_BIND, __FILE__,
                           __LINE__);
                     saddr_setUserMessage($saddr, 'Authentication failed');
                     ldap_close($ldap_handle);
                     $ldap_handle=NULL;
                  }
               }
            }
         } else {
            saddr_setError($saddr, SADDR_ERR_ROOTDSE, __FILE__, __LINE__);
            ldap_close($ldap_handle);
            $ldap_handle=NULL;
         }
      } else {
         saddr_setError($saddr, SADDR_ERR_LDAP_CONNECTION, __FILE__, __LINE__);
         $ldap_handle=NULL;
      }
   } else {
      saddr_setError($saddr, SADDR_ERR_NOT_STRING, __FILE__, __LINE__);
   }

   return $ldap_handle;
}

?>
