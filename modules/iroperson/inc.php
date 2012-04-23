<?PHP
/* (c) 2012 Etienne Bagnoud
   This file is part of saddr project. saddr is under the MIT license.

   See LICENSE file
 */

function ext_saddr_iroperson_get_fn_list()
{
   return array(
         'getClass'=>'ext_iroperson_getClass',
         'getAttrs'=>'ext_iroperson_getAttrs',
         'getTemplates'=>'ext_iroperson_getTemplates',
         'getRdnAttributes'=>'ext_iroperson_getRdnAttributes',
         'getAttributesCombination'=>'ext_iroperson_getAttributesCombination',
         'processAttributes'=>'ext_iroperson_processAttributes'
         );
}

function ext_iroperson_getAttrs()
{
   return array(
      'cn'=>array('name', '_saddr_res0'),
      'displayname'=>'displayname',
      'sn'=>'lastname',
      'givenname'=>'firstname',
      'title'=>'title',
      'o'=>array('company', '_saddr_res1'),
      'postalcode'=>'work_npa',
      'l'=>'work_city',
      'st'=>'work_state',
      'c'=>'work_country',
      'telephonenumber'=>array('work_telephone', '_saddr_res2'),
      'l'=>'work_city',
      'postaladdress'=>'work_address',
      'facsimiletelephonenumber'=>'work_fax',
      'mail'=>array('work_email', '_saddr_res3'),
      'mobile'=>array('home_mobile', '_saddr_res2'),
      'homephone'=>array('home_telephone', '_saddr_res2'),
      'labeleduri'=>'url',
      'mozillahomestate'=>'home_state',
      'mozillahomelocalityname'=>'home_city',
      'mozillahomepostalcode'=>'home_npa',
      'mozillahomecountryname'=>'home_country',
      'homepostaladdress'=>'home_address',
      'marker'=>'tags',
      'custom1'=>'restricted_tags',
      'custom2'=>'tags',
      'custom3'=>'tags',
      'custom4'=>'tags',
      'description'=>'description',
      'irobankaccount'=>'iban',
      'irobankestablishment'=>'bank',
      'birthday'=>'birthday'
      );
}

function ext_iroperson_getClass()
{
   return array(
         'search'=>'contactperson',
         'structural'=>'inetorgperson', 
         'auxiliary'=> array('iroadditionaluserinfo', 'contactperson'));
}

function ext_iroperson_getTemplates()
{
   return array('view'=>'view_edit.tpl',
         'edit'=>'view_edit.tpl');
}

function ext_iroperson_getRdnAttributes()
{
   return array('principal'=>'cn',
         'multi'=>array('mail', 'telephonenumber', 'mobile', 
            'facsimiletelephonenumber', 'o', 'l', 'st', 'c')
         );
}

function ext_iroperson_getAttributesCombination()
{
   return array('cn'=>'$$givenname$$ $$sn$$',
         'displayname'=>'$$givenname$$ $$sn$$');
}

function ext_iroperson_processAttributes($attribute, $values)
{
   switch($attribute) {
      case 'marker':
      case 'custom1':
      case 'custom2':
      case 'custom3':
      case 'custom4':
         $ret=array();
         foreach($values as $_values) {
            $v=explode(',', $_values);
            foreach($v as $_v) {
               $_v=trim($_v);
               if(!empty($_v)) {
                  $ret[]=$_v;
               }
            }
         }
         return $ret;
         break;
      case 'displayname':
         $ret=array();
         foreach($values as $val) {
            $ret[]=iconv('UTF-8', 'ASCII//TRANSLIT', $val);
         }
         return $ret;
         break;
      default: return FALSE;
   }
}

?>
