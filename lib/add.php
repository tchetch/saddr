<?PHP
/* (c) 2012 Etienne Bagnoud
   This file is part of saddr project. saddr is under the MIT license.

   See LICENSE file
 */

function saddr_add(&$saddr, $smarty_entry)
{
   if(!empty($smarty_entry) && isset($smarty_entry['module']) &&
            is_string($smarty_entry['module'])) {
      $ldap_entry=saddr_makeLdapEntry($saddr, $smarty_entry);

      foreach($ldap_entry as $attr=>$value) {
         $v=saddr_processAttributes($saddr, $smarty_entry['module'],
               array($attr, $value));
         if($v!==FALSE) {
            $ldap_entry[$attr]=$v;
         }
      }

      if(!empty($ldap_entry)) {
         $rdn_attrs=saddr_getRdnAttributes($saddr, $smarty_entry['module']);
         if(isset($rdn_attrs['principal']) &&
               is_string($rdn_attrs['principal'])) {
            if(isset($ldap_entry[$rdn_attrs['principal']])) {
               $rdn=$rdn_attrs['principal'].'='.
                     $ldap_entry[$rdn_attrs['principal']][0];

               $dn='';
               $bases=saddr_getModuleBase($saddr, $smarty_entry['module']);
               $e=@saddr_read($saddr, $rdn.','. $bases[0], array('name'));
               if($e==FALSE) {
                  $dn=$rdn.','.$bases[0];
               } else {
                  foreach($rdn_attrs['multi'] as $m_rdn) {
                     if(isset($ldap_entry[$m_rdn])) {
                        $rdn.='+'.$m_rdn.'='.$ldap_entry[$m_rdn][0];
                        if(!saddr_read($saddr, $rdn.','. $bases[0])){
                           $dn=$rdn.','.$bases[0];
                           break;
                        }
                     }
                  }
               }

               if($dn!='') {
                  $ret=array($dn, ldap_add(saddr_getLdap($saddr), $dn,
                           $ldap_entry));
                  return $ret;
               }
            }
         }
      }
   }

   return array('', FALSE);
}

?>
