<?PHP
/* (c) 2012 Etienne Bagnoud
   This file is part of saddr project. saddr is under the MIT license.

   See LICENSE file
 */
define('SADDR__', 1);

include(dirname(__FILE__).'/saddrErrors.php');

/* My own library, must be includable */
$tchetch_path=getenv('SADDR_TCHETCH_PATH');
if(!empty($tchetch_path) && is_readable($tchetch_path)) {
   include($tchetch_path);
} else {
   include('tchetch/tch.php');
}
if(!defined('TCH__')) die('Missing tchetch/tch.php');

/* Extern project */
$smarty_path=getenv('SADDR_SMARTY_PATH');
if(!empty($smarty_path) && is_readable($smarty_path)) {
   include($smarty_path);
} else {
   if(tch_isIncludable('Smarty3/Smarty.class.php'))
      include('Smarty3/Smarty.class.php');
   else if(tch_isIncludable('Smarty/Smarty.class.php'))
      include('Smarty/Smarty.class.php');
   else if(tch_isIncludable('Smarty.class.php'))
      include('/Smarty.class.php');
   else die('Missing Smarty');
}

/* in saddr tree */
include(dirname(__FILE__).'/lib/saddr.php');
include(dirname(__FILE__).'/lib/saddr_smarty.php');

/* Various options */
date_default_timezone_set('UTC');
setlocale(LC_ALL, 'en_US.UTF-8');

/* Code start */
$Saddr=saddr_init();

/* Guess saddr base filename */
$saddr_filename=basename(__FILE__);
saddr_setBaseFileName($Saddr, $saddr_filename);

/* Include local index file */
if(tch_isIncludable('saddr.index.local.php')) {
   include('saddr.index.local.php');
} else {
   $local_ldap_bind_file=getenv('SADDR_LOCAL_INDEX');
   if(!empty($local_ldap_bind_file)) {
      if(tch_isIncludable($local_ldap_bind_file)) {
         include($local_ldap_bind_file);
      }
   }
}

/* INIT LDAP */
$Ldap=NULL;
$ldap_host=saddr_getLdapHost($Saddr);
if(is_string($ldap_host)) {
   $Ldap=ldap_connect($ldap_host);
   if($Ldap) {
      $root_dse=tch_getRootDSE($Ldap);
      if($root_dse) {

         /* Find and set LDAP Version */
         if(($ldap_version=tch_getLdapVersion($Ldap, $root_dse))!==FALSE) {
            if(!ldap_set_option($Ldap, LDAP_OPT_PROTOCOL_VERSION, $ldap_version)) {
               saddr_setError($Saddr, SADDR_ERR_LDAP_VERSION, __FILE__,
                     __LINE__);
               ldap_close($Ldap);
               $Ldap=NULL;
            }
         } else {
            saddr_setError($Saddr, SADDR_ERR_LDAP_VERSION, __FILE__, __LINE__);
            ldap_close($Ldap);
            $Ldap=NULL;
         }

         /* Find directory base, take the first found */
         if($Ldap!=NULL) {
            if(($bases=tch_getLdapBases($Ldap, $root_dse))!==FALSE) {
               saddr_setLdapBase($Saddr, $bases[0]);
            } else {
               saddr_setError($Saddr, SADDR_ERR_LDAP_BASE, __FILE__,
                     __LINE__);
               ldap_close($Ldap);
               $Ldap=NULL;
            }
         }

         /* Get objectclasses available in directory */
         if($Ldap!=NULL) {
            if(($oc=tch_getLdapObjectClasses($Ldap, $root_dse))!=FALSE) {
               saddr_setLdapObjectClasses($Saddr, $oc); 
            } else {
               saddr_setError($Saddr, SADDR_ERR_LDAP_OBJECTCLASS, __FILE__,
                     __LINE__);
               ldap_close($Ldap);
               $Ldap=NULL;
            }
         }

         /* Bind either with provided user/pass or anonymously */
         if($Ldap!=NULL) {
            $user=saddr_getUser($Saddr);
            $pass=saddr_getPass($Saddr);
            if(is_string($user) && is_string($pass)) {
               if(ldap_bind($Ldap, $user, $pass)) {
                  saddr_setLdap($Saddr, $Ldap);
               } else {
                  saddr_setError($Saddr, SADDR_ERR_LDAP_BIND, __FILE__,
                        __LINE__);
                  saddr_setUserMessage($Saddr, 'Authentication failed');
                  ldap_close($Ldap);
                  $Ldap=NULL;
               }
            } else {
               /* Anonymous bind */
               if(ldap_bind($Ldap)) {
                  saddr_setLdap($Saddr, $Ldap);
                  saddr_setUserMessage($Saddr, 'Anonmyous connection',
                        SADDR_MSG_WARNING);
               } else {
                  saddr_setError($Saddr, SADDR_ERR_LDAP_BIND, __FILE__,
                        __LINE__);
                  saddr_setUserMessage($Saddr, 'Authentication failed');
                  ldap_close($Ldap);
                  $Ldap=NULL;
               }
            }
         }
      } else {
         saddr_setError($Saddr, SADDR_ERR_ROOTDSE, __FILE__, __LINE__);
         ldap_close($Ldap);
         $Ldap=NULL;
      }
   } else {
      saddr_setError($Saddr, SADDR_ERR_LDAP_CONNECTION, __FILE__, __LINE__);
      $Ldap=NULL;
   }
} else {
   saddr_setError($Saddr, SADDR_ERR_NOT_STRING, __FILE__, __LINE__);
}

if($Ldap==NULL) {
   saddr_setUserMessage($Saddr, 'Failed to connect to server');
}


/* Search proxy to generate encrypted search query */
if(isset($_POST['saddrGoSearch'])) {
   if(isset($_POST['saddrGlobalSearch']) &&
         !empty($_POST['saddrGlobalSearch'])) {
      header('Location: '.saddr_getBaseFileName($Saddr).'?op=doGlobalSearch&search='.
            saddr_urlEncrypt($Saddr, $_POST['saddrGlobalSearch']));
      exit(0);
   } else if(isset($_POST['saddrTagSearch']) &&
         !empty($_POST['saddrTagSearch'])) {
      header('Location: '.saddr_getBaseFileName($Saddr).'?op=doTagSearch&search='.
            saddr_urlEncrypt($Saddr, $_POST['saddrTagSearch']));
      exit(0);
   } else {
      header('Location: ' . $_SERVER['HTTP_REFERER']);
      exit(0);
   }
}

/* INIT SMARTY */
$Smarty=new Smarty();

$tpl_dir=getenv('SADDR_SMARTY_TPL_DIR');
if(is_string($tpl_dir) && is_dir($tpl_dir) && is_readable($tpl_dir)) {
   $Smarty->setTemplateDir($tpl_dir);
} else {
   $Smarty->setTemplateDir(dirname(__FILE__).'/tpl/');
}
unset($tpl_dir);

$compile_dir=getenv('SADDR_SMARTY_COMPILE_DIR');
if(is_string($compile_dir) && is_dir($compile_dir) &&
      is_readable($compile_dir)) {
   $Smarty->setCompileDir($compile_dir);
} else {
   $tmp=saddr_getTempDir($Saddr);
   if(@mkdir($tmp.'/smarty-compile/')) {
      $Smarty->setCompileDir($tmp.'/smarty-compile/');
   } else {
      $Smarty->setCompileDir('/tmp/');
   }
}

/* Register saddr plugin */
$Smarty->registerPlugin('modifier', 'ldap_url_get_url', 's2s_ldapUrlGetUrl');
$Smarty->registerPlugin('modifier', 'ldap_url_get_label','s2s_ldapUrlGetLabel');
$Smarty->registerPlugin('function', 'saddr_encrypt', 's2s_encUrl');
$Smarty->registerPlugin('function', 'saddr_list_module', 's2s_listModule');
$Smarty->registerPlugin('function', 'saddr_read_entry', 's2s_readEntry');
$Smarty->registerPlugin('function', 'saddr_class_message', 's2s_classOfMessage');
$Smarty->registerPlugin('function', 'saddr_entry', 's2s_displaySmartyEntry');
$Smarty->registerPlugin('block', 'saddr_when_module_available', 
      's2s_whenModuleAvailable');
$Smarty->registerPlugin('function', 'saddr_dojo', 's2s_dojoPath');
$Smarty->registerPlugin('function', 'saddr_dijit_theme_path', 's2s_dijitThemePath');
$Smarty->registerPlugin('function', 'saddr_dijit_theme_name', 's2s_dijitThemeName');
$Smarty->registerPlugin('function', 'saddr_url', 's2s_generateUrl');

saddr_setSmarty($Saddr, $Smarty);

/* Eventually set saddr option */

saddr_includeModules($Saddr);

$saddr_results=array(
      'display'=>'home.tpl',
      'handle'=>&$Saddr
      );

/* Operations */
if(isset($_GET['op'])) {
   switch($_GET['op']) {
      case 'doTagSearch':
         if(isset($_GET['search'])) {
            $search_string=saddr_urlDecrypt($Saddr, $_GET['search']);
            if(is_string($search_string)) {
               $search=saddr_search($Saddr, '*'.$search_string.'*',
                     array('tags', 'restricted_tags'));
               $saddr_results['op']=$_GET['op'];
               /* Reencrypt or you experience some troubles with the browser */
               $saddr_results['search']=saddr_urlEncrypt($saddr, 
                     $search_string);
               $saddr_results['display']='results.tpl';
               if(!empty($search)) {
                  $saddr_results['search_results']=$search;
               }
            }
         }
         break;
      case 'doGlobalSearch':
         if(isset($_GET['search'])) {
            $search_string=saddr_urlDecrypt($Saddr, $_GET['search']);
            if(is_string($search_string)) {
               $search=saddr_search($Saddr, '*'.$search_string.'*');
               $saddr_results['op']=$_GET['op'];
               /* Reencrypt or you experience some troubles with the browser */
               $saddr_results['search']=saddr_urlEncrypt($saddr, 
                     $search_string);
               $saddr_results['display']='results.tpl';
               if(!empty($search)) {
                  $saddr_results['search_results']=$search;
               }
            }
         }
         break;
      case 'doSearchForCompany':
      case 'doSearchByAttribute':
         $attribute='company';
         if(isset($_GET['attribute'])) {
            $attribute=saddr_urlDecrypt($Saddr, $_GET['attribute']);
         }
         if(isset($_GET['search'])) {
            $search_string=saddr_urlDecrypt($Saddr, $_GET['search']);
            if(is_string($search_string)) {
               $search=saddr_search($Saddr, $search_string, array($attribute));
               $saddr_results['op']=$_GET['op'];
               /* Reencrypt or you experience some troubles with the browser */
               $saddr_results['search']=saddr_urlEncrypt($saddr, 
                     $search_string);
               $saddr_results['display']='results.tpl';
               if(!empty($search)) {
                  $saddr_results['search_results']=$search;
               }
            }
         }
         break;
      case 'doAddOrEdit':
         $smarty_entry=array();
         foreach($_POST as $k=>$v) {
            if(!empty($v) && is_string($v) && $v!='') {
               $smarty_entry[$k]=$v;
            } else if(is_array($v) && !empty($v)) {
               $smarty_entry[$k]=array();
               foreach($v as $_v) {
                  if(is_string($_v) && !empty($_v)) {
                     $smarty_entry[$k][]=$_v;
                  }
               }
               if(empty($smarty_entry[$k])) unset($smarty_entry[$k]);
            }
         }

         if(isset($smarty_entry['id']) && is_string($smarty_entry['id'])) {
            $dn=saddr_urlDecrypt($saddr, $smarty_entry['id']);
            $smarty_entry['dn']=$dn;    
            $ret=saddr_modify($Saddr, $smarty_entry);
            if($ret[1]) {
               saddr_setUserMessage($Saddr, 'Modification successful', 
                     SADDR_MSG_GOOD);
            } else {
               saddr_setUserMessage($Saddr, 'Modification failed');
            }
         } else {
            $ret=saddr_add($Saddr, $smarty_entry);
            if($ret[1]) {
               saddr_setUserMessage($Saddr, 'Addition successful', 
                     SADDR_MSG_GOOD);
            } else {
               saddr_setUserMessage($Saddr, 'Addition failed');
            }
         }

         if(!empty($ret[0])) {
            $dn=$ret[0];
            $entry=saddr_read($Saddr, $dn);
             if(!empty($entry)) {
               $tpl=saddr_getTemplates($Saddr, $entry['module']);
               $saddr_results['display']=saddr_getModuleDirectory($Saddr).'/'.
                     $entry['module'].'/'.$tpl['view'];
               $saddr_results['search_results']=$entry;
               $saddr_results['__delete']=saddr_urlEncrypt($saddr, 
                     time() . " $dn");
            }
         }
         break;
      case 'view':
         $dn=saddr_urlDecrypt($Saddr, $_GET['id']);
         $entry=saddr_read($Saddr, $dn);
         if(!empty($entry)) {
            $tpl=saddr_getTemplates($Saddr, $entry['module']);
            $saddr_results['display']=saddr_getModuleDirectory($Saddr).'/'.
                  $entry['module'].'/'.$tpl['view'];
            $saddr_results['search_results']=$entry;
            $saddr_results['__delete']=saddr_urlEncrypt($saddr, 
                  time() . " $dn");
         }  
         break;
      case 'preAdd':
         $preadd_entry=array();
         foreach($_REQUEST as $name=>$value) {
            if($name!='id' && $name!='module') {
               if(!is_array($value)) {
                  $preadd_entry[$name][]=$value;
               } else {
                  foreach($value as $v) {
                     $preadd_entry[$name][]=$v;
                  }
               }
            }
         }
      case 'addOrEdit':
         $entry=array();
         if(isset($_GET['id'])) {
            /* we edit, not just add */
            $dn=saddr_urlDecrypt($Saddr, $_GET['id']);
            $entry=saddr_read($Saddr, $dn);
            $entry['__edit']=TRUE;
            if(!empty($entry)) {
               $saddr_results['search_results']=$entry;
               $saddr_results['__delete']=saddr_urlEncrypt($saddr, 
                     time() . " $dn");
            }
         } else {
            if(!empty($preadd_entry)) $entry=$preadd_entry;
            $entry['__edit']=TRUE;
            $module=saddr_getDefaultModuleName($Saddr);
            if(isset($_GET['module'])) $module=$_GET['module'];
            else if(isset($_POST['module'])) $module=$_POST['module'];
            if(!saddr_isModuleAvailable($Saddr, $module)) {
               $entry['module']=saddr_getDefaultModuleName($Saddr);
            } else {
               $entry['module']=$module;
            }
            $saddr_results['search_results']=$entry;
         }
         
         $tpl=saddr_getTemplates($Saddr, $entry['module']);
         $saddr_results['display']=saddr_getModuleDirectory($Saddr).'/'.
               $entry['module'].'/'.$tpl['edit'];
         break;

      case 'doDelete':
         $delete_confirmed=TRUE;
         /* no break */
      case 'delete':
         if(isset($_GET['timed_id'])) {
            $tid=saddr_urlDecrypt($saddr, $_GET['timed_id']);
            if(is_string($tid)) {
               $time_id=explode(' ', $tid, 2);
               if(is_numeric($time_id[0])) {
                  if(isset($time_id[1]) && is_string($time_id[1])) {
                     if(time()-$time_id[0]<=300) {
                        if(!isset($delete_confirmed)) {
                           $entry=saddr_read($Saddr, $time_id[1]);
                           if(!empty($entry)) {
                              $saddr_results['__delete']=saddr_urlEncrypt(
                                    $saddr, time() . " ". $time_id[1]);
                              $saddr_results['search_results']=$entry;
                              $saddr_results['display']='delete.tpl';
                           }
                        } else {
                           if(saddr_delete($Saddr, $time_id[1])) {
                              saddr_setUserMessage($Saddr, 
                                    'Deletion successful', 
                                    SADDR_MSG_GOOD);
                           } else {
                              saddr_setUserMessage($Saddr, 
                                    'Deletion failed'); 
                           }
                           $saddr_results['display']='home.tpl';
                        }
                     }
                  }
               }
            }
         }
         break;
      }
}

saddr_getSmarty($Saddr)->assign('saddr', $saddr_results);

/* DISPLAY */
saddr_getSmarty($Saddr)->display('index.tpl');
?>
