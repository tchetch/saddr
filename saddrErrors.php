<?PHP
/* (c) 2012 Etienne Bagnoud
   This file is part of saddr project. saddr is under the MIT license.

   See LICENSE file
 */

define('SADDR_ERR_LDAP_CONNECTION',       0x0001);
define('SADDR_ERR_LDAP_ROOTDSE',          0x0002);
define('SADDR_ERR_LDAP_VERSION',          0x0003);
define('SADDR_ERR_LDAP_BASE',             0x0004);
define('SADDR_ERR_LDAP_BIND',             0x0005);
define('SADDR_ERR_LDAP_ANONYMOUS_BIND',   0x0006);

define('SADDR_ERR_NOT_STRING',            0x0100);

define('SADDR_ERR_UNKNOWN_ERROR',         0xFFFF);


define('SADDR_MSG_GOOD',                  0x0000);
define('SADDR_MSG_ERROR',                 0x0001);
define('SADDR_MSG_WARNING',               0x0002);
define('SADDR_MSG_INFO',                  0x0004);
define('SADDR_MSG_DEBUG',                 0x0008);
?>
