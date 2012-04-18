<?PHP

function saddr_read(&$saddr, $dn, $attrs=array(), $deepness=0)
{
   $ret=FALSE;
   $se=array();

   $module=FALSE;
   $oc=FALSE;
  
   $ldap_attrs=array('*');
   if(!empty($attrs)) {
      $ldap_attrs=saddr_getLdapAttr($saddr, $attrs);
   } 

   $ldap=saddr_getLdap($saddr);
   if($ldap!=NULL && $ldap!=FALSE) {
      $r_res=ldap_read($ldap, $dn, '(objectclass=*)', $ldap_attrs);
      if($r_res) {
         $e=ldap_get_entries($ldap, $r_res);
         if($e && $e['count']==1) {
            $ret=saddr_makeSmartyEntry($saddr, $e[0]);
            if($deepness>0) {
               if(isset($ret['seealso'])) {
                  $deepness--;
                  $resolved_seealso=array();
                  foreach($ret['seealso'] as $see_dn) {
                     $rel_entry=saddr_read($saddr, $see_dn, array(), $deepness);
                     if($rel_entry!==FALSE) {
                        $resolved_seealso[]=$rel_entry;
                     }
                  }
                  $ret['seealso']=$rel_entry;
               }
            }
         }
      }
   }

   return $ret;
}

?>
