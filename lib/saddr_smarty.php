<?PHP
function s2s_ldapUrlGetUrl($ldap_url)
{
   $ret=explode(' ', $ldap_url, 2);
   return $ret[0];
}
function s2s_ldapUrlGetLabel($ldap_url)
{
   $ret=explode(' ', $ldap_url, 2);
   if(isset($ret[1])) {
      return $ret[1];
   } else {
      return '';
   }
}

function s2s_encUrl($params, $smarty)
{
   if(isset($params['value'])) {
      $saddr=$smarty->getTemplateVars('saddr');
      if(isset($saddr['handle'])) {
         $enc=saddr_urlEncrypt($saddr['handle'], $params['value']);
         if(isset($params['var'])) {
            $smarty->assign($params['var'], $enc);
            return;
         } else {
            return $enc;
         }
      }
   }

   return;
}

function s2s_readEntry($params, $smarty)
{
   $saddr=$smarty->getTemplateVars('saddr');

   if(!isset($params['var'])) return;

   if(isset($params['id'])) {
      $id=$params['id'];
      $dn=saddr_urlDecrypt($saddr['handle'], $id);
      $attributes=array();
      if(isset($params['attributes'])) {
         $attributes=$params['attributes'];
      }
      $entry=saddr_read($saddr['handle'], $dn, $attributes);
      $smarty->assign($params['var'], $entry);
      return;
   }

   $smarty->assign($params['var'], array());
   return;
}

function s2s_listModule($params, $smarty)
{
   if(isset($params['module'])) {
      $module=$params['module'];
      $attributes=array(); /* empty array is all attributes */
      if(isset($params['attributes'])) {
         $attributes=$params['attributes'];
      }
      if(!isset($params['var'])) {
         return;
      }
      $saddr=$smarty->getTemplateVars('saddr');
      if(!isset($saddr['handle'])) {
         $smarty->assign($params['var'], array());
         return;
      }
      $res=saddr_list($saddr['handle'], $module, $attributes);
      $smarty->assign($params['var'], $res);
      return;
   }
   if(isset($params['var'])) {
      $smarty->assign($params['var'], array());
   }
   return;
}

function s2s_whenModuleAvailable($params, $content, $smarty)
{
   if(isset($params['module']) && is_string($params['module'])) {
      $saddr=$smarty->getTemplateVars('saddr');
      if(isset($saddr['handle'])) {
         if(saddr_isModuleAvailable($saddr['handle'], $params['module'])) {
            return $content;
         } 
      }
   }
}

function s2s_classOfMessage($params, $smarty) 
{
   if(isset($params['errno'])) {
      switch($params['errno']) {
         case 0x0000: return 'saddr_msgGood';
         default:
         case 0x0001: return 'saddr_msgError';
         case 0x0002: return 'saddr_msgWarning';
         case 0x0004: return 'saddr_msgInfo';
         case 0x0008: return 'saddr_msgDebug';
      }
   } else {
      return 'saddr_msgError';
   }
}

/* TODO Rewritte in a more structured way */
function s2s_displaySmartyEntry($params, $smarty)
{
   $saddr=$smarty->getTemplateVars('saddr');
   $entry=$saddr['search_results'];

   if(!isset($params['e'])) { return; }

   $type=array('dijitTextBox', 'dijit.form.TextBox');
   if(isset($params['type'])) {
      switch(strtolower($params['type'])) {
         default:
         case 'textbox': $type=array('dijitTextBox', 'dijit.form.TextBox'); 
                         break;
         case 'textarea': $type=array('dijitTextArea', 'dijit.form.Textarea');
                          break;
         case 'date': $type=array('dijitDateTextBox', 'dijit.form.DateTextBox');
                      break;
         case 'tag': $type=array('saddrTagsArea', 'dijit.form.Textarea'); break;
         case 'sselect': $type=array('saddrSelect',
                               'dijit.form.FilteringSelect'); break;
      }
   }

   if(isset($params['label']) && is_string($params['label'])) {
      $label=$params['label'];
   }

   if(isset($params['module']) && is_string($params['module'])) {
      $module=$params['module'];
   }

   $display_label_on_view=FALSE;
   if(isset($params['labelonview'])) $display_label_on_view=TRUE;

   $multi=FALSE;
   if(isset($params['multi'])) $multi=TRUE;

   $html='<div class="saddr_groupOfValue">';
   if(isset($entry['__edit'])) {
      $name=$params['e'];
      if($multi) $name=$name.'[]';

      if(isset($entry[$params['e']])) {
         $v_entry=$entry[$params['e']];
      } else {
         $v_entry=array();
      }
      $v_entry[]='';
      
      $html.='<label for="'.
         $name.'" class="saddr_label">'.$label.'</label>';
      switch($type[0]) {
         case 'dijitTextBox':
         case 'dijitDateTextBox':
            foreach($v_entry as $v) {
               $html.='<input type="textbox" name="'.$name.'" '.
                  'value="'.$v.'" '.
                  'class="saddr_value saddr_textbox" '.
                  'data-dojo-type="'.$type[1].'" />';
               if(!$multi) break;
            }
            break;
         case 'dijitTextArea':
            foreach($v_entry as $v) {
               $html.='<textarea name="'.$name.'" '.
                  'class="saddr_value saddr_textarea" '.
                  'data-dojo-type="'.$type[1].'">'.
                  $v.'</textarea>';
               if(!$multi) break;
            }
            break;
         case 'saddrTagsArea':
            $html.='<textarea name="'.$name.'" '.
               'class="saddr_value saddr_textarea" '.
               'data-dojo-type="'.$type[1].'">';
            foreach($v_entry as $v) {
               $html.=$v;
               if($v!='') $html.=', ';
            }
            $html.='</textarea>';
            break;
         case 'saddrSelect':
            if(!isset($module)) return;
            foreach($v_entry as $v) {
               if(isset($params['attributes'])) {
                  if(isset($params['format']) &&
                        is_string($params['format'])) {
                     $attributes=array();
                     if(is_array($params['attributes'])) {
                        foreach($params['attributes'] as $a) {
                           $attributes[]=$a;
                        }
                     } else {
                        $attributes[]=$a;
                     }
                     $res=saddr_list($saddr['handle'], $module, $attributes);
                     $html.='<select name="'.$name.'"'.
                        ' class="saddr_value saddr_select"'.
                        ' data-dojo-type="'.$type[1].'">';
                     $html.='<option value=""></option>';
                     foreach($res as $select) {
                        $svalue=$select['dn'];
                        $html.='<option value="'.$svalue.'"';
                        if($svalue==$v) $html.=' selected="1"';
                        $html.=' >';
                        $x=array();
                        foreach($attributes as $a) { 
                           if(!empty($select[$a][0])) {
                              $x[]=$select[$a][0]; 
                           }
                        }
                        $html.=vsprintf($params['format'], $x);
                        $html.='</option>';
                     }
                     $html.='</select>';
                  }
               }   
               if(!$multi) break;
            }
            break;
      }
   } else {
      if(!isset($entry[$params['e']])) return;
      
      if($display_label_on_view) {
         if(isset($label)) {
            $html.='<div class="saddr_label">'.$label.'</div>';
            $with_label=TRUE;
         }
      }

      $v_entry=$entry[$params['e']];
      $v_entry[]='';

      switch($type[0]) {
         default:
            foreach($entry[$params['e']] as $v) {
               if($type[0]=='dijitTextArea') $v=nl2br($v);
               $html.='<div class="saddr_value saddr_'.$type[0];
               if(!isset($with_label)) {
                  $html.=' saddr_valueNoLabel';
               }
               $html.='">';
               if(isset($params['searchable'])) {
                  $html.='<a href="index.php?op=doSearchByAttribute&attribute=';
                  $html.=s2s_encUrl(array('value'=>$params['e']), $smarty);
                  $html.='&search=';
                  $html.=s2s_encUrl(array('value'=>$v), $smarty);
                  $html.='" title="Search for '.$v.'">';

               }
               $html.=$v;
               if(isset($params['searchable'])) $html.='</a>';

               $html.='&nbsp;</div>';
               if(!$multi) break;
            }
            break;
         case 'saddrTagsArea':
            $html.='<div class="saddr_value saddr_'.$type[0];
            if(!isset($with_label)) {
               $html.=' saddr_valueNoLabel';
            }
            $html.='">';
            foreach($entry[$params['e']] as $v) {
               $html.='<a href="index.php?op=doSearchByAttribute&attribute=';
               $html.=s2s_encUrl(array('value'=>$params['e']), $smarty);
               $html.='&search=';
               $html.=s2s_encUrl(array('value'=>$v), $smarty);
               $html.='" title="Search for '.$v.'">';
               $html.=$v;
               $html.='&nbsp;</a>';
            }
            $html.='</div>';
            break;
         case 'saddrSelect':
            if(isset($params['attributes'])) {
               $html.='<div class="saddr_value saddr_'.$type[0];
               if(!isset($with_label)) {
                  $html.=' saddr_valueNoLabel';
               }
               $html.='">';

               foreach($entry[$params['e']] as $v) {
                  $e=saddr_read($saddr['handle'], $v, $params['attributes']);
                  $x=array();
                  if(is_array($params['attributes'])) {
                     foreach($params['attributes'] as $a) {
                        if(isset($e[$a])) {
                           $x[]=$e[$a][0];
                        }
                     }
                  } else {
                     $x[]=$e[$params['attributes']][0];
                  }
                  $html.='<a href="index.php?op=view&id='.$e['id'].'">';
                  $html.=vsprintf($params['format'], $x);
                  $html.='</a>';
                  $html.='</div>';
                  if(!$multi) break;
               }
            }
      }
   }

   $html.='</div>';

   return $html;
}

?>
