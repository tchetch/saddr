<?PHP
/* (c) 2012 Etienne Bagnoud
   This file is part of saddr project. saddr is under the MIT license.

   See LICENSE file
 */

function saddr_init()
{
   $saddr=array(
         'ldap'=>array(
            'handle'=>NULL,
            'base'=>'',
            'host'=>'localhost',
            /* User and pass to NULL => Anonymous auth */
            'user'=>NULL,
            'pass'=>NULL,
            'objectclass'=>array()
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
            'objectclass'=>array()
            ),
         'functions'=>array(
            'modules'=>array(),
            'names'=>array(
               'getClass',
               'getAttrs',
               'getTemplates',
               'getRdnAttributes',
               'getAttributesCombination',
               'processAttributes'
               )
            ),
         'dir'=>array(
            'temp'=>NULL
            ),
         'enc'=>array(),
         'errors'=>array(),
         'user_messages'=>array(),
         'base_filename'=>'index.php'
         );
   return $saddr;
}

function saddr_setError(&$saddr, $id, $file=__FILE__, $line=__LINE__)
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
   $saddr['user_messages'][]=array('msg'=>$message, 'level'=>$level);
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
                              if(!isset($fn_list[$f_name])) {
                                 $all_func=FALSE;
                                 break;
                              }
                              if(!function_exists($fn_list[$f_name])) {
                                 $all_func=FALSE;
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
                                    foreach($saddr['functions']['names'] as $f) {
                                       $saddr['functions']['modules'][$file][$f]=
                                          $fn_list[$f];
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
      if(function_exists($saddr['functions']['modules'][$type][$func])) {
         $ret=call_user_func_array(
               $saddr['functions']['modules'][$type][$func], $params);
      }
   }

   return $ret;
} 

function saddr_setLdapBase(&$saddr, $base)
{
   $saddr['ldap']['base']=$base;
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
   if(is_array($params) && !empty($params) && count($params)==2) {
      return saddr_getFromFunction($saddr, $type, 'processAttributes',
            $params);
   } else {
      return FALSE;
   }
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

   foreach($combination as $attr=>$rule) {
      if(!isset($ldap_entry[$attr])) {
         if(preg_match_all('/\$\$([[:alnum:]]+)\$\$/', $rule, $matches)) {
            $new_attribute=$rule;
            foreach($matches[1] as $match) {
               if(isset($ldap_entry[$match])) {
                  $new_attribute=preg_replace('/\$\$'.$match.'\$\$/', 
                        $ldap_entry[$match][0], $new_attribute);
               } else {
                  $new_attribute=preg_replace('/\$\$'.$match.'\$\$/', 
                        '', $new_attribute);
               }
            }
            $new_attribute=trim($new_attribute);
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
         foreach($modules as $m) {
            $classes=saddr_getClass($saddr, $m);
            $flat_classes=array($classes['structural']);
            foreach($classes['auxiliary'] as $c) $flat_classes[]=$c;
            $found=TRUE;
            foreach($oc as $c) {
               if(!in_array($c, $flat_classes)) $found=FALSE;
            }
            if($found) {
               $module=$m; 
               break;
            }
         }

      }

      if($module) {
         $attrs=saddr_getAttrs($saddr, $module);
         if(isset($ldap_entry['dn'])) {
            $smarty_entry['id']=saddr_urlEncrypt($saddr, $ldap_entry['dn']);
            $smarty_entry['module']=$module;
            $smarty_entry['dn']=$ldap_entry['dn'];

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

function saddr_getSearchFilter(&$saddr, $attrs, $search_val, $modules=array())
{
   /* No module specified is all modules selected */
   if(empty($modules)) {
      $modules=$saddr['modules']['names'];
   }
   if(is_string($attrs)) {
      $attrs=array($attrs);
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
      $attr_search='('.$all_attrs[0].'='.$search_val.')';
   } else {
      foreach($all_attrs as $a) {
         $attr_search.='('.$a.'='.$search_val.')';
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

include(dirname(__FILE__).'/read.php');
include(dirname(__FILE__).'/search.php');
include(dirname(__FILE__).'/list.php');
include(dirname(__FILE__).'/add.php');
include(dirname(__FILE__).'/modify.php');
include(dirname(__FILE__).'/delete.php');

?>
