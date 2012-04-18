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
         'getAttributesCombination'=>
            'ext_iroorganization_getAttributesCombination',
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
      'telephonenumber'=>'work_telephone',
      'l'=>'work_city',
      'facsimiletelephonenumber'=>'work_fax',
      'mail'=>'work_email',
      'labeleduri'=>'url',
      'physicaldeliveryofficename'=>'branch',
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
         'auxiliary'=> array('iroorganizationextended'));
}

function ext_iroorganization_getTemplates()
{
   return array('view'=>'view_edit.tpl',
         'edit'=>'view_edit.tpl');
}

function ext_iroorganization_getAttributesCombination()
{
   return array();
}

function ext_iroorganization_processAttributes($attributes, $values)
{
   return FALSE;
}

?>
