<?PHP
/* (c) 2012 Etienne Bagnoud
   This file is part of saddr project. saddr is under the MIT license.

   See LICENSE file
 */

function ext_saddr_iroorganization_get_fn_list()
{
   return array(
         'getClass'=>'ext_iroorganization_getClass',
         'getAttrs'=>'ext_iroorganization_getAttrs',
         'getTemplates'=>'ext_iroorganization_getTemplates',
         'getRdnAttributes'=>'ext_iroorganization_getRdnAttributes',
         'processAttributes'=>'ext_iroorganization_processAttributes'
         );
}

function ext_iroorganization_getAttrs()
{
   return array(
      'o'=>array('name', 'company'),
      'postaladdress'=>'work_address',
      'postalcode'=>'work_npa',
      'l'=>'work_city',
      'st'=>'work_state',
      'c'=>'work_country',
      'telephonenumber'=>array('work_telephone', '_saddr_res2'),
      'l'=>'work_city',
      'facsimiletelephonenumber'=>'work_fax',
      'mail'=>array('work_email', '_saddr_res3'),
      'labeleduri'=>'url',
      'physicaldeliveryofficename'=>array('branch', '_saddr_res1'),
      'marker'=>'tags',
      'custom1'=>'restricted_tags',
      'custom2'=>'tags',
      'custom3'=>'tags',
      'custom4'=>'tags',
      'description'=>'description'
      );
}

function ext_iroorganization_getRdnAttributes()
{
   return array('principal'=>'o',
         'multi'=>array('mail', 'physicaldeliveryofficename',
            'businesscategory', 'st', 'l', 'c'));
}

function ext_iroorganization_getClass()
{
   return array(
         'search'=>'organization',
         'structural'=>'organization', 
         'auxiliary'=> array('iroorganizationextended', 'contactperson'));
}

function ext_iroorganization_getTemplates()
{
   return array('view'=>'view_edit.tpl',
         'edit'=>'view_edit.tpl',
         'home'=>'home.tpl');
}

function ext_iroorganization_processAttributes($attribute, $values)
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
      default: return FALSE;
   }
   return FALSE;
}

?>
