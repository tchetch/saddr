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
         'getRdnAttributes'=>'ext_irobank_getRdnAttributes'
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
      'telephonenumber'=>array('work_telephone', '_saddr_res2'),
      'facsimiletelephonenumber'=>'work_fax',
      'mail'=>array('work_email', '_saddr_res3'),
      'labeleduri'=>'url',
      'physicaldeliveryofficename'=>array('branch', '_saddr_res1'),
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
         'edit'=>'view_edit.tpl',
         'home'=>'home.tpl');
}

function ext_irobank_getRdnAttributes() {
   return array('principal'=>'o',
         'multi'=>array('physicaldeliveryofficename', 'mail',
            'iroBankPostalAccount', 'l', 'st', 'c'));
}

?>
