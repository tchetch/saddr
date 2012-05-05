<?PHP
/* (c) 2012 Etienne Bagnoud
   This file is part of saddr project. saddr is under the MIT license.

   See LICENSE file
 */

/* To have server ctrls, my patch should be accepted into main PHP distribution.
   The patch is available https://bugs.php.net/bug.php?id=61921
 */
define('SADDR_PHP_LDAP_HAS_SERVER_CTRLS', FALSE);
      

function saddr_init()
{
   $saddr=array(
         'ldap'=>array(
            'handle'=>NULL,
            'base'=>array(),
            'host'=>'localhost',
            /* User and pass to NULL => Anonymous auth */
            'user'=>NULL,
            'pass'=>NULL,
            'objectclass'=>array(),
            'rootdse'=>array()
            ),
         'dojo'=>array(
            'path'=>'/dojo/dojo.js',
            'theme'=>'/dijit/themes/nihilo/nihilo.css',
            'theme_name'=>'nihilo'
            ),
         'smarty'=>array(
            'handle'=>NULL
            ),
         'modules'=>array(
            'dir'=>dirname(__FILE__).'/../modules/',
            'default'=>'',
            'names'=>array(),
            'objectclass'=>array(),
            'bases'=>array()
            ),
         'functions'=>array(
            'modules'=>array(),
            'names'=>array(
               /* Second argument to TRUE if the function MUST be available.
                */
               array('getClass', TRUE),
               array('getAttrs', TRUE),
               array('getTemplates', TRUE),
               array('getRdnAttributes', TRUE),
               array('getAttributesCombination', FALSE),
               array('processAttributes', FALSE),
               array('getAttrsGroup', FALSE)
               )
            ),
         'dir'=>array(
            'temp'=>NULL
            ),
         'enc'=>array(),
         'errors'=>array(),
         'user_messages'=>array(),
         'message_registery'=>array(),
         'base_filename'=>'index.php'
         );
   return $saddr;
}

function saddr_setError(&$saddr, $id, $file=-1, $line=-1)
{
   if(is_integer($id)) {
      $saddr['errors'][microtime()]=array('id'=>$id, 'file'=>$file,
            'line'=>$line);
   } else {
      $saddr['errors'][microtime()]=array('id'=>
            SADDR_ERR_UNKNOWN_ERROR, '_id'=>$id, 'file'=>$file, 'line'=>$line);
   }
}

function saddr_setUserMessage(&$saddr, $message, $level=SADDR_MSG_ERROR)
{
   /* This for the user, don't say several time the same thing */
   $msg_cs=md5($message . $level);
   if(!isset($saddr['message_registery'][$msg_cs])) {
      $saddr['user_messages'][]=array('msg'=>$message, 'level'=>$level);
      $saddr['message_registery'][$msg_cs]=1;
   }
}

function saddr_removeModule(&$saddr, $module)
{
   if(isset($saddr['functions']['modules'][$module])) {
      unset($saddr['functions']['modules'][$module]);
      foreach($saddr['modules']['names'] as $k=>$v) {
         if($v==$module) {
            unset($saddr['modules']['names'][$k]);
            break;
         }
      }
      foreach($saddr['modules']['objectclass'] as $k=>$v) {
         if($v==$module) {
            unset($saddr['modules']['objectclass'][$k]);
            break;
         }
      }
      if(isset($saddr['modules']['bases'][$module])) {
         unset($saddr['modules']['bases'][$module]);
      }
   }
   return TRUE;
}

function saddr_includeModules(&$saddr)
{
   $path=saddr_getModuleDirectory($saddr);

   if(is_dir($path)) {
      $dh=opendir($path);
      if($dh!=FALSE) {
         while(($file=readdir($dh))!==FALSE) {
            if($file!='.' && $file!='..') {
               if(is_dir($path.'/'.$file)) {
                  if(is_file($path.'/'.$file.'/inc.php')) {
                     include($path.'/'.$file.'/inc.php');
                     if(function_exists('ext_saddr_'.$file.'_get_fn_list')) {
                        $fn_list=call_user_func('ext_saddr_'.$file.
                              '_get_fn_list');
                        if(is_array($fn_list) && !empty($fn_list)) {
                           $all_func=TRUE;
                           foreach($saddr['functions']['names'] as $f_name) {
                              if(!isset($fn_list[$f_name[0]]) && 
                                    $f_name[1] &&
                                    !function_exists($fn_list[$f_name[0]])) {
                                 $all_func=FALSE;
                                 break;
                              }
                           }
                           if($all_func) {
                              $oc=call_user_func($fn_list['getClass']);

                              $av_oc=saddr_getLdapObjectClasses($saddr);
                              if(in_array($oc['structural'], $av_oc)) {
                                 $all_class=TRUE;
                                 foreach($oc['auxiliary'] as $aux) {
                                    if(!in_array($aux, $av_oc)) {
                                       $all_class=FALSE;
                                       break;
                                    }
                                 }

                                 if($all_class) {
                                    $saddr['modules']['names'][]=$file;
                                    foreach($fn_list as $k=>$f) {
                                       $saddr['functions']['modules'][$file][$k]=
                                          $f;
                                    }
                                    $saddr['modules']['objectclass'][$oc['search']]=
                                       $file;
                                 }
                              }
                           }
                        }
                     }
                  }
               }
            }
         }
      }
   }

   return TRUE;
}

function saddr_setLdapObjectClasses(&$saddr, $oc)
{
   $saddr['ldap']['objectclass']=$oc;
   return TRUE;
}

function saddr_getLdapObjectClasses(&$saddr)
{
   return $saddr['ldap']['objectclass'];
}

function saddr_setLdapRootDse(&$saddr, $rootdse)
{
   $saddr['ldap']['rootdse']=$rootdse;
   return TRUE;
}

function saddr_getLdapRootDse(&$saddr)
{
   return $saddr['ldap']['rootdse'];
}

function saddr_setUser(&$saddr, $user)
{
   $saddr['ldap']['user']=$user;
   return TRUE;
}

function saddr_getUser(&$saddr)
{
   return $saddr['ldap']['user'];
}

function saddr_setPass(&$saddr, $pass)
{
   $saddr['ldap']['pass']=$pass;
   return TRUE;
}

function saddr_getPass(&$saddr)
{
   return $saddr['ldap']['pass'];
}

function saddr_setLdapHost(&$saddr, $host)
{
   $saddr['ldap']['host']=$host;
   return TRUE;
}

function saddr_getLdapHost(&$saddr)
{
   return $saddr['ldap']['host'];
}

function saddr_setBaseFileName(&$saddr, $filename)
{
   $saddr['base_filename']=$filename;
   return TRUE;
}

function saddr_getBaseFileName(&$saddr)
{
   return $saddr['base_filename'];
}

function saddr_setJsDojoPath(&$saddr, $path)
{
   $saddr['dojo']['path']=$path;
   return TRUE;
}

function saddr_setDijitThemePath(&$saddr, $path)
{
   $saddr['dojo']['theme']=$path;
   return TRUE;
}

function saddr_setDijitThemeName(&$saddr, $name)
{
   $saddr['dojo']['theme_name']=$name;
   return TRUE;
}

function saddr_getJsDojoPath(&$saddr)
{
   return $saddr['dojo']['path'];
}

function saddr_getDijitThemePath(&$saddr)
{
   return $saddr['dojo']['theme'];
}

function saddr_getDijitThemeName(&$saddr)
{
   return $saddr['dojo']['theme_name'];
}

function saddr_getDefaultModuleName(&$saddr)
{
   return $saddr['module']['default'];
}

function saddr_setDefaultModuleName(&$saddr, $name)
{
   $saddr['module']['default']=$name;
   return TRUE;
}

function saddr_getModuleDirectory(&$saddr)
{
   return $saddr['modules']['dir'];
}

function saddr_setModuleBase(&$saddr, $module, $base)
{
   /* This function runs before modules are included, so we cannot check for
      module existence.
    */
   if(is_string($module)) {
      if(TRUE) { /* TODO check if base is part of the naming context */
         $saddr['modules']['bases'][$module]=$base;
         return TRUE;
      }
   }

   return FALSE;
}

function saddr_getModuleBase(&$saddr, $module)
{
   if(saddr_isModuleAvailable($saddr, $module)) {
      if(isset($saddr['modules']['bases'][$module])) {
         if(is_string($saddr['modules']['bases'][$module]) &&
               !empty($saddr['modules']['bases'][$module])) {
            /* A bit silly, but we might have more than one base for a module */
            return array($saddr['modules']['bases'][$module]);
         }
      }
   }
   
   return saddr_getLdapBase($saddr);
}

function saddr_getAllLdapBase(&$saddr)
{
   $bases=saddr_getLdapBase($saddr);
   foreach($saddr['modules']['bases'] as $b) {
      $bases[]=$b;
   }
   return $bases;
}

function saddr_getLdap(&$saddr)
{
   return $saddr['ldap']['handle'];
}

function saddr_setLdap(&$saddr, $ldap)
{
   $saddr['ldap']['handle']=$ldap;
   return TRUE;
}

function saddr_getFromFunction(&$saddr, $type, $func, $params=array())
{
   $ret=NULL;
   if(!empty($type) && is_string($type)) {
      $type=strtolower($type);
     
      if(isset($saddr['functions']['modules'][$type]) &&
            isset($saddr['functions']['modules'][$type][$func]) &&
            function_exists($saddr['functions']['modules'][$type][$func])) {
         $ret=call_user_func_array(
               $saddr['functions']['modules'][$type][$func], $params);
      } 
   }

   return $ret;
} 

/* When a new base is added and is similar with an actual one but more
   specific, the most specific is kept (ou=saddr,o=x,dc=y & o=x,dc=y => 
   ou=saddr,o=x,dc=y is kept)

   The last one to come in is used for new entry.
 */
function saddr_setLdapBase(&$saddr, $base)
{
   $do_add=TRUE;

   $e_base=explode(',', $base);
   $count_e_base=count($e_base);
   
   foreach($saddr['ldap']['base'] as $k=>$b) {
      if($b==$base) {
         $do_add=FALSE;
         break;
      }
      
      $e_b=explode(',', $b);   
      $count_e_b=count($e_b);
      $x=$e_base;
      $y=$e_b;
      $biggest='';
      if($count_e_base>$count_e_b) {
         $biggest=$base;
         for($i=0;$i<($count_e_base-$count_e_b);$i++) {
            array_shift($x);
         }
      } else if($count_e_base<$count_e_b) {
         $biggest=$b;
         for($i=0;$i<($count_e_b-$count_e_base);$i++) {
            array_shift($y);
         }
      }
      if(implode(',', $x)==implode(',', $y)) {
         $saddr['ldap']['base'][$k]=$biggest;
         $do_add=FALSE;
         break;
      }
   }
   if($do_add) {
      array_unshift($saddr['ldap']['base'], $base);
   }
   return TRUE;
}

function saddr_getLdapBase(&$saddr)
{
   return $saddr['ldap']['base'];
}

function saddr_getClass(&$saddr, $type)
{
   return saddr_getFromFunction($saddr, $type, 'getClass');
}

function saddr_getAttrs(&$saddr, $type)
{
   return saddr_getFromFunction($saddr, $type, 'getAttrs');
}

function saddr_getAttrsGroup(&$saddr, $type)
{
   return saddr_getFromFunction($saddr, $type, 'getAttrsGroup');
}

function saddr_getTemplates(&$saddr, $type)
{
   return saddr_getFromFunction($saddr, $type, 'getTemplates');
} 

function saddr_getRdnAttributes(&$saddr, $type)
{
   return saddr_getFromFunction($saddr, $type, 'getRdnAttributes');
}

function saddr_getAttributesCombination(&$saddr, $type)
{
   return saddr_getFromFunction($saddr, $type, 'getAttributesCombination');
}

function saddr_processAttributes(&$saddr, $type, $params) 
{
   $ret = FALSE;
   if(is_array($params) && !empty($params) && count($params)==2) {
      $ret=saddr_getFromFunction($saddr, $type, 'processAttributes',
            $params);
      if($ret==NULL) {
         $ret=FALSE;
      }
   }

   return $ret;
}

function saddr_getAllAttrs(&$saddr) 
{
   $all_attrs=array();
   foreach($saddr['modules']['names'] as $module) {
      $attrs=saddr_getAttrs($saddr, $module);
      saddr_arrayMerge($all_attrs, $attrs);
   }

   return $all_attrs;
}

function saddr_getLdapAttr(&$saddr, $attrs=array(), $module=NULL)
{
   $av_attributes=array();
   if($module==NULL) {
      $av_attributes=saddr_getAllAttrs($saddr);
   } else {
      if(saddr_isModuleAvailable($saddr, $module)) {
         $av_attributes=saddr_getAttrs($saddr, $module);
      }
   }
   if(empty($av_attributes)) return array();
   if(empty($attrs)) $attrs=$av_attributes;
   $av_attrs=saddr_arrayFlip($av_attributes);
   $ldap_attrs=array();
   foreach($attrs as $a) {
      if(is_string($a)) {
         if(isset($av_attrs[$a])) {
            if(is_string($av_attrs[$a])) {
               if(!in_array($av_attrs[$a], $ldap_attrs)) {
                  $ldap_attrs[]=$av_attrs[$a];
               }
            } else if(is_array($av_attrs[$a])) {
               foreach($av_attrs[$a] as $_a) {
                  if(!in_array($_a, $ldap_attrs)) {
                     $ldap_attrs[]=$_a;
                  }
               }
            }
         }
      } else if(is_array($a)) {
         foreach($a as $at) { 
            if(isset($av_attrs[$at])) {
               if(is_string($av_attrs[$at])) {
                  if(!in_array($av_attrs[$at], $ldap_attrs)) {
                     $ldap_attrs[]=$av_attrs[$at];
                  }
               } else if(is_array($av_attrs[$at])) {
                  foreach($av_attrs[$at] as $_a) {
                     if(!in_array($_a, $ldap_attrs)) {
                        $ldap_attrs[]=$_a;
                     }
                  }
               }
            }
         }
      }
   }
   
   $ldap_attrs[]='dn';
   $ldap_attrs[]='objectclass';
   $ldap_attrs[]='seealso';
   
   return $ldap_attrs;
}

function saddr_setSmarty(&$saddr, &$smarty)
{
   $saddr['smarty']['handle']=$smarty;
   return TRUE;
}

function saddr_getSmarty(&$saddr)
{
   return $saddr['smarty']['handle'];
}

function saddr_getModuleByObjectClass(&$saddr, $oc)
{
   $oc=strtolower($oc);
   if(isset($saddr['modules']['objectclass'][$oc]) &&
         !empty($saddr['modules']['objectclass'][$oc])) {
      return $saddr['modules']['objectclass'][$oc];
   }
   return FALSE;
}

function saddr_doAttributesCombination(&$saddr, &$ldap_entry, $module)
{
   $combination=saddr_getAttributesCombination($saddr, $module);

   if($combination==NULL) return;

   foreach($combination as $attr=>$rule) {
      if(!isset($ldap_entry[$attr])) {
         $new_attribute=saddr_ldapPrintf($saddr, $rule, $ldap_entry);
         if($new_attribute!=$rule) {
            $ldap_entry[$attr]=array($new_attribute);
         }
      }
   }
}

function saddr_makeLdapEntry(&$saddr, $smarty_entry)
{
   $ret=array();
   $attrs=saddr_getAttrs($saddr, $smarty_entry['module']);

   if(!empty($attrs)) {
      $ldap_entry=array();
      foreach($attrs as $attr=>$s_attrs) {
         if(is_array($s_attrs)) {
            foreach($s_attrs as $s_attr) {
               if(substr_compare($s_attr, '_saddr', 0, 6)==0) continue;
               if(isset($smarty_entry[$s_attr])) {
                  if(!isset($ldap_entry[$attr])) $ldap_entry[$attr]=array();
                  if(is_array($smarty_entry[$s_attr])) {
                     foreach($smarty_entry[$s_attr] as $value) {
                        $ldap_entry[$attr][]=$value;
                     }
                  } else {
                     $ldap_entry[$attr][]=$smarty_entry[$s_attr];
                  }
               }
            }
         } else {
            if(substr_compare($s_attrs, '_saddr', 0, 6)==0) continue;
            if(isset($smarty_entry[$s_attrs])) {
               if(!isset($ldap_entry[$attr])) $ldap_entry[$attr]=array();
               if(is_array($smarty_entry[$s_attrs])) {
                  foreach($smarty_entry[$s_attrs] as $value) {
                     $ldap_entry[$attr][]=$value;
                  }
               } else {
                  $ldap_entry[$attr][]=$smarty_entry[$s_attrs];
               }
            }
         }
      }
  
      saddr_doAttributesCombination($saddr, $ldap_entry,
            $smarty_entry['module']);

      $classes=saddr_getClass($saddr, $smarty_entry['module']);
      if(!empty($classes)) {
         if(isset($classes['structural']) && is_string($classes['structural'])) {
            $ldap_entry['objectclass']=array($classes['structural']);
            if(isset($classes['auxiliary'])) {
               if(is_array($classes['auxiliary'])) {
                  foreach($classes['auxiliary'] as $aux) {
                     $ldap_entry['objectclass'][]=$aux;
                  }
               } else {
                  $ldap_entry['objectclass'][]=$classes['auxiliary'];
               }
            }
            $ret=$ldap_entry;
         }
      }
   }

   return $ret;
}

function saddr_makeSmartyEntry(&$saddr, $ldap_entry)
{
   $smarty_entry=array();
   
   if(isset($ldap_entry['objectclass'])) {
      $modules=array();
      $r_oc=array();
      for($i=0;$i<$ldap_entry['objectclass']['count'];$i++) {
         $oc[]=strtolower($ldap_entry['objectclass'][$i]);
         $x=saddr_getModuleByObjectClass($saddr, 
               $ldap_entry['objectclass'][$i]);
         if($x) $modules[]=$x;
      }
      
      $module='';
      if(count($modules)==1) {
         $module=$modules[0];
      } else {
         $last_same_class_count=-1;
         foreach($modules as $m) {
            $classes=saddr_getClass($saddr, $m);
            $flat_classes=array($classes['structural']);
            foreach($classes['auxiliary'] as $c) $flat_classes[]=$c;

            $same_classes=0;
            foreach($oc as $c) {
               if(in_array($c, $flat_classes)) $same_classes++;
            }
            if($same_classes>$last_same_class_count) {
               $last_same_class_count=$same_classes;
               $module=$m;
            }
         }

      }

      if($module) {
         $has_groups=FALSE;
         $attrs=saddr_getAttrs($saddr, $module);
         $attrs_group=saddr_getAttrsGroup($saddr, $module);
         if($attrs_group!=NULL) $has_group=TRUE;

         if(isset($ldap_entry['dn'])) {
            $smarty_entry['id']=saddr_urlEncrypt($saddr, $ldap_entry['dn']);
            $smarty_entry['module']=$module;
            $smarty_entry['dn']=$ldap_entry['dn'];
            if($has_group) {
               $smarty_entry['__group']=array();
               foreach($attrs_group as $k=>$d) {
                  $smarty_entry['__group'][$k]=array('nonempty'=>FALSE,
                        'count'=>0);
               }
            }

            if(isset($ldap_entry['seealso'])) {
               $smarty_entry['seealso']=array();
               for($i=0;$i<$ldap_entry['seealso']['count'];$i++) {
                  $smarty_entry['seealso'][$i]=$ldap_entry['seealso'][$i];
               }
            }

            foreach($attrs as $ldap_attr=>$attr) {
               if(isset($ldap_entry[$ldap_attr]) &&
                     $ldap_entry[$ldap_attr]['count']>0) {
                  if(is_array($attr)) {
                     foreach($attr as $att) {
                        if($has_group) {
                           foreach($attrs_group as $k=>$ag) {
                              if(in_array($att, $ag)) {
                                 $smarty_entry['__group'][$k]['nonempty']=TRUE;
                                 $smarty_entry['__group'][$k]['count']++;
                                 break;
                              }
                           }
                        }
                        if(!isset($smarty_entry[$att]) ||
                              !is_array($smarty_entry[$att])) {
                           $smarty_entry[$att]=array();
                        }
                        for($i=0;$i<$ldap_entry[$ldap_attr]['count'];$i++) {
                           if(!in_array($ldap_entry[$ldap_attr][$i],
                                    $smarty_entry[$att])) {
                              $smarty_entry[$att][]=$ldap_entry[$ldap_attr][$i];
                           }
                        }
                     }
                  } else if(is_string($attr)) {
                     if($has_group) {
                        foreach($attrs_group as $k=>$ag) {
                           if(in_array($attr, $ag)) {
                              $smarty_entry['__group'][$k]['nonempty']=TRUE;
                              $smarty_entry['__group'][$k]['count']++;
                              break;
                           }
                        }
                     }
                     if(!isset($smarty_entry[$attr]) || 
                           !is_array($smarty_entry[$attr])) {
                        $smarty_entry[$attr]=array();
                     }
                     for($i=0;$i<$ldap_entry[$ldap_attr]['count'];$i++) {
                        if(!in_array($ldap_entry[$ldap_attr][$i],
                                 $smarty_entry[$attr])) {
                              $smarty_entry[$attr][]=
                                 $ldap_entry[$ldap_attr][$i];
                           }
                     }
                  }
               }
            }
         }
      }      
   }
   
   return $smarty_entry; 
}

function saddr_isModuleAvailable(&$saddr, $module)
{
   if(in_array($module, $saddr['modules']['names'])) return TRUE;
   return FALSE;
}

function saddr_getTempDir(&$saddr)
{
   if($saddr['dir']['temp']==NULL) {
      $tmp=sys_get_temp_dir();
      if(empty($tmp)) {
         $tmp='/tmp/';
      }
      if(!@mkdir($tmp.'/saddr-tmp/')) {
         $tmp=$tmp.'/saddr-tmp/';
      }

      $saddr['dir']['temp']=realpath($tmp);
   }

   return $saddr['dir']['temp'];
}

function saddr_getSearchFilter(&$saddr, $attrs, $search_val, $modules=array(),
      $search_operator='=')
{
   /* No module specified is all modules selected */
   if(empty($modules)) {
      $modules=$saddr['modules']['names'];
   }
   if(is_string($attrs)) {
      $attrs=array($attrs);
   }
   switch($search_operator) {
      case '=':
      default:
         $sop='=';  break;
      case '<':
      case '<=':
         $sop='<='; break;
      case '>':
      case '>=':
         $sop='>='; break;
      case '~':
      case '~=':
         $sop='~='; break;
   }

   $all_oc=array();
   $all_attrs=array();
   foreach($modules as $m) {
      $oc=saddr_getClass($saddr, $m);
      if(!empty($oc) && isset($oc['search']) && is_string($oc['search'])) {
         $all_oc[]=$oc['search'];
      }

      $m_attrs=saddr_getAttrs($saddr, $m);
      if(!empty($m_attrs) && is_array($m_attrs)) {
         $r_attrs=saddr_arrayFlip($m_attrs);
         foreach($attrs as $attr) {
            if(isset($r_attrs[$attr])) {
               if(is_string($r_attrs[$attr])) {
                 if(!in_array($r_attrs[$attr], $all_attrs)) {
                     $all_attrs[]=$r_attrs[$attr];
                  }
               } else if(is_array($r_attrs[$attr])) {
                  foreach($r_attrs[$attr] as $_a) {
                     if(!in_array($_a, $all_attrs)) {
                        $all_attrs[]=$_a;
                     }
                  }
               }
            }
         }
      }
   }

   /* search filter will look like : (&(|(objectclass=)()())(|(attr=)()())) */
   $oc_search='';
   if(count($all_oc)<1) {
      $oc_search='(objectclass=*)';
   } else if(count($all_oc)==1) {
      $oc_search='(objectclass='.$all_oc[0].')';
   } else {
      foreach($all_oc as $oc) {
         $oc_search.="(objectclass=$oc)";
      }
      $oc_search='(|'.$oc_search.')';
   }

   $attr_search='';
   if(count($all_attrs)==1) {
      $attr_search='(|('.$all_attrs[0].$sop.$search_val.')';
      $attr_search.='('.$all_attrs[0].$sop.strtolower($search_val).')';
      $attr_search.='('.$all_attrs[0].$sop.strtoupper($search_val).'))';
   } else {
      foreach($all_attrs as $a) {
         $attr_search.='('.$a.$sop.$search_val.')';
         $attr_search.='('.$a.$sop.strtolower($search_val).')';
         $attr_search.='('.$a.$sop.strtoupper($search_val).')';
      }
      $attr_search='(|'.$attr_search.')';
   }

   return '(&'.$oc_search.$attr_search.')';
}

function saddr_arrayFlip($array)
{
   $new=array();

   foreach($array as $k=>$v) {
      if(is_array($v)) {
         foreach($v as $sub_v) {
            if(is_string($sub_v) || is_integer($sub_v)) {
               if(isset($new[$sub_v])) {
                  if(is_array($new[$sub_v])) {
                     if(!in_array($k, $new[$sub_v])) {
                        $new[$sub_v][]=$k;
                     }
                  } else {
                     if($new[$sub_v]!=$k) {
                        $new[$sub_v]=array($new[$sub_v], $k);
                     }
                  }
               } else {
                  $new[$sub_v]=$k;
               }

            }
         }
      } else if(is_string($v) || is_integer($v)) {
         if(isset($new[$v])) {
            if(is_array($new[$v])) {
               if(!in_array($k, $new[$v])) {
                  $new[$v][]=$k;
               }
            } else {
               if($new[$v]!=$k) {
                  $new[$v]=array($new[$v], $k);
               }
            }
         } else {
            $new[$v]=$k;
         }
      }
   }
   return $new;
}

function saddr_arrayMerge(&$dest, $src)
{
   foreach($src as $k=>$v) {
      if(isset($dest[$k])) {
         if(is_array($dest[$k])) {
            if(is_array($v)) {
               foreach($v as $_v) {
                  if(!in_array($_v, $dest[$k])) {
                     $dest[$k][]=$_v;
                  }
               }
            } else {
               $dest[$k][]=$v;
            }
         } else {
            if(is_array($v)) {
               $dest[$k]=array($dest[$k]);
               foreach($v as $_v) {
                  if(!in_array($_v, $dest[$k])) {
                     $dest[$k][]=$_v;
                  }
               }
            } else {
               if($dest[$k]!=$v) {
                  $dest[$k]=array($dest[$k], $v);
               }
            }
         }
      } else {
         if(is_array($v)) {
            $dest[$k]=array();
            foreach($v as $_v) {
               if(!in_array($_v, $dest[$k])) {
                  $dest[$k][]=$_v;
               }
            }
         } else {
            if(is_array($v)) {
               $dest[$k]=array();
               foreach($v as $_v) {
                  if(!in_array($_v, $dest[$k])) {
                     $dest[$k][]=$_v;
                  }
               }
            } else {
               $dest[$k]=$v;
            }
         }
      }
   }
}

function saddr_urlEncrypt(&$saddr, $url)
{
   $cihper=saddr_getEncCipher($saddr);
   $mode=saddr_getEncMode($saddr);
   $pass=saddr_getEncPass($saddr);

   $enc_val=@mcrypt_encrypt($cihper, $pass, $url, $mode);
   return rawurlencode(base64_encode($enc_val));
}

function saddr_urlDecrypt(&$saddr, $url)
{
   $cihper=saddr_getEncCipher($saddr);
   $mode=saddr_getEncMode($saddr);
   $pass=saddr_getEncPass($saddr);

   $enc_val=base64_decode(rawurldecode($url));
   return trim(@mcrypt_decrypt($cihper, $pass, $enc_val, $mode));
}

function saddr_setEncCipher(&$saddr, $cipher)
{
   $saddr['enc']['cipher']=$cipher;
   return TRUE;
}

function saddr_getEncCipher(&$saddr)
{
   if(isset($saddr['enc']['cipher'])) {
      return $saddr['enc']['cihper'];
   } else {
      return MCRYPT_RIJNDAEL_256;
   }
}

function saddr_setEncMode(&$saddr, $mode)
{
   $saddr['enc']['mode']=$mode;
   return TRUE;
}

function saddr_getEncMode(&$saddr)
{
   if(isset($saddr['enc']['mode'])) {
      return $saddr['enc']['mode'];
   } else {
      return MCRYPT_MODE_ECB;
   }
}

function saddr_setEncPass(&$saddr, $pass)
{
   $saddr['enc']['pass']=$pass;
   return TRUE;
}

function saddr_getEncPass(&$saddr)
{
   if(isset($saddr['enc']['pass'])) {
      return $saddr['enc']['pass'];
   } else {
      /* If no pass set */
      $pass=getenv('SADDR_URL_ENCRYPTION_PASS');
      if(!empty($pass)) {
         return $pass;
      } else {
         return __FILE__ . $_SERVER['SERVER_NAME'] . $_SERVER['SERVER_ADDR'];
      }
   }
}

function saddr_ldapPrintf(&$saddr, $format, $entry)
{
   if(preg_match_all('/'.
            '(?<!\\\\)[\$|#]{2,2}'.
            '(?:'.
               '([^~\$#)]*)'.
               '~'.
            '){0,1}'.
            '([[:alnum:]]+)'.
            '(?:'.
               '~'.
               '([^~\$#)]*)'.
            '){0,1}'.
            '(?<!\\\\)[\$|#]{2,2}'.
            '/', $format, $matches))
   {
      $replace=$matches[0];
      $pre=$matches[1];
      $attribute=$matches[2];
      $post=$matches[3];
      if(count($replace)>0) {
         for($i=0;$i<count($attribute);$i++) {
            if(!empty($attribute[$i])) {
               if(isset($entry[$attribute[$i]]) &&
                     count($entry[$attribute[$i]])>0) {
                  $new_val='';
                  if(!empty($pre[$i])) $new_val.=$pre[$i];
                  $new_val.=$entry[$attribute[$i]][0];
                  if(!empty($post[$i])) $new_val.=$post[$i];
                  $format=str_replace($replace[$i], $new_val, $format);
               } else {
                  $format=str_replace($replace[$i], '', $format);
               }
            } else {
               $format=str_replace($replace[$i], '', $format);
            }
         }
      }
   }
   return $format;
}

include(dirname(__FILE__).'/read.php');
include(dirname(__FILE__).'/search.php');
include(dirname(__FILE__).'/list.php');
include(dirname(__FILE__).'/add.php');
include(dirname(__FILE__).'/modify.php');
include(dirname(__FILE__).'/delete.php');
include(dirname(__FILE__).'/saddr_smarty.php');
include(dirname(__FILE__).'/saddr_ldap.php');

?>
