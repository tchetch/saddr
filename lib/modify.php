<?PHP
/* (c) 2012 Etienne Bagnoud
   This file is part of saddr project. saddr is under the MIT license.

   See LICENSE file
 */

function saddr_modify(&$saddr, $smarty_entry)
{
   $ret=array('', FALSE);

   if(!empty($smarty_entry) && isset($smarty_entry['module']) &&
            is_string($smarty_entry['module']) && isset($smarty_entry['dn']) &&
            is_string($smarty_entry['dn'])) {
      $ldap_entry=saddr_makeLdapEntry($saddr, $smarty_entry);

      foreach($ldap_entry as $attr=>$value) {
         $v=saddr_processAttributes($saddr, $smarty_entry['module'],
               array($attr, $value));
         if($v!==FALSE) {
            $ldap_entry[$attr]=$v;
         }
      }

      $ret[0]=$smarty_entry['dn'];
      if(!empty($ldap_entry)) {
         
         $dn=$smarty_entry['dn'];
         $dn_parts=explode(',', $dn);
         $rdn_components=array();

         if(strstr($dn_parts[0], '+')) {
            $multi=explode('+', $dn_parts[0]);
            foreach($multi as $m) {
               $x=explode('=', $m);
               $rdn_components[$x[0]]=$x[1];
            }
         } else {
            $x=explode('=', $dn_parts[0]);
            $rdn_components[$x[0]]=$x[1];
         }

         $need_rename=FALSE;
         $skip_rename=FALSE;

         $ldap_attrs=saddr_getAttrs($saddr, $smarty_entry['module']);
         foreach($rdn_components as $attr=>$val) {
            if(!isset($ldap_attrs[$attr])) {
               $skip_rename=TRUE;
               break;
            }
         }
         
         if(!$skip_rename) {
            foreach($rdn_components as $attr=>$val) {
               if(!isset($ldap_entry[$attr])) {
                  $need_rename=TRUE; break;
               } else {
                  $ok=FALSE;
                  foreach($ldap_entry[$attr] as $_v) {
                     if($_v==$val) $ok=TRUE;
                  }
                  if(!$ok) {
                     $need_rename=TRUE;
                     break;
                  }
               }
            }
         }

         if(!$need_rename) {
            $old=saddr_read($saddr, $dn);
            $ldap_old=saddr_makeLdapEntry($saddr, $old);
            
            /* Add new attributes */
            $op_success=TRUE;
            foreach($ldap_entry as $attr=>$val) {
               if(!isset($ldap_old[$attr])) {
                  $op_success=ldap_mod_add(saddr_getLdap($saddr), $dn,
                        array($attr=>$val));
               }
            }

            foreach($ldap_old as $attr=>$val) {
               if(!isset($ldap_entry[$attr])) {
                  $_op_success=ldap_mod_del(saddr_getLdap($saddr), $dn,
                        array($attr=>array()));
                  if(!$_op_success) {
                     $op_success=FALSE;
                  }
               } else {
                  if(count($value)!=count($ldap_entry[$attr])) {
                     $_op_success=ldap_mod_replace(saddr_getLdap($saddr), $dn,
                           array($attr=>$ldap_entry[$attr]));
                     if(!$_op_success) {
                        $op_success=FALSE;
                     }
                  } else {
                     $modify=FALSE;
                     foreach($val as $k=>$v) {
                        if($ldap_entry[$attr][$k]!=$v) {
                           $modify=TRUE;
                           break;
                        }
                     }
                     if($modify) {
                        $_op_success=ldap_mod_replace(saddr_getLdap($saddr), 
                              $dn, array($attr=>$ldap_entry[$attr]));
                        if(!$_op_success) {
                           $op_success=FALSE;
                        }
                     }
                  }
               }
            }
            
            $ret=array($dn, $op_success );
            if(!$ret[1]) {
               $ret[0]=$smarty_entry['dn'];
            }
         } else {
            $ret=saddr_add($saddr, $smarty_entry);
            if($ret[1]) {
               ldap_delete(saddr_getLdap($saddr), $dn);
            } else {
               $ret[0]=$smarty_entry['dn'];
            }
         }
      }
   }

   return $ret;
}

?>
