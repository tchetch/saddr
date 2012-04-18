<?PHP
/* (c) 2012 Etienne Bagnoud
   This file is part of saddr project. saddr is under the MIT license.

   See LICENSE file
 */

function ext_saddr_irobank_get_fn_list()
{
   return array(
         'getClass'=>'ext_irobank_getClass',
         'getAttrs'=>'ext_irobank_getAttrs',
         'getTemplates'=>'ext_irobank_getTemplates',
         'getRdnAttributes'=>'ext_irobank_getRdnAttributes',
         'getAttributesCombination'=>'ext_irobank_getAttributesCombination',
         'processAttributes'=>'ext_irobank_processAttributes' 
         );
}

function ext_irobank_getAttrs()
{
   return array(
      'o'=>array('name', 'company'),
      'postalcode'=>'work_npa',
      'l'=>'work_city',
      'st'=>'work_state',
      'c'=>'work_country',
      'telephonenumber'=>'work_telephone',
      'facsimiletelephonenumber'=>'work_fax',
      'mail'=>'work_email',
      'labeleduri'=>'url',
      'physicaldeliveryofficename'=>'branch',
      'irobankclearing'=>'clearing',
      'irobankpostalaccount'=>'postalaccount',
      'irobankswift'=>'swift'
      );
}

function ext_irobank_getClass()
{
   return array(
         'search'=>'irobank',
         'structural'=>'organization', 
         'auxiliary'=> array('irobank', 'iroorganizationextended'));
}

function ext_irobank_getTemplates()
{
   return array('view'=>'view_edit.tpl',
         'edit'=>'view_edit.tpl');
}

function ext_irobank_getRdnAttributes() {
   return array('principal'=>'o',
         'multi'=>array('physicaldeliveryofficename', 'mail',
            'iroBankPostalAccount', 'l', 'st', 'c'));
}

function ext_irobank_getAttributesCombination() {
   return array();
}

function ext_irobank_processAttributes($attribute, $values) {
   return FALSE;
}
?>
