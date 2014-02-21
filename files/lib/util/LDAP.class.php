<?php
namespace wcf\util;

/**
 * @author      Jan Altensen (Stricted)
 * @copyright   2013-2014 Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     de.stricted.wcf.ldap
 */

class LDAP {
	/**
	 * LDAP resource id
	 * @var	object
	 */
	protected $ldap = null;	
	
	/**
	 * LDAP DN
	 * @var	string
	 */
	protected $dn = '';
	
	/**
	 * Constructs a new instance of LDAP class.
	 */
	public function __construct () {
		if (!extension_loaded("ldap")) {
			throw new Exception("Can not find LDAP extension.");
		}
	}
	
	/**
	 * connect to a ldap server
	 *
	 * @param	string	$server
	 * @param	integer	$port
	 * @param	string	$dn
	 * @return	bool	true/false
	 */
	public function connect ($server, $port, $dn) {
		$this->ldap = ldap_connect($server, $port);
		$this->dn = $dn;
		
		if ($this->ldap) {
			ldap_set_option($this->ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($this->ldap, LDAP_OPT_REFERRALS, 0);
			return true;
		}
		else {
			throw new Exception("Cant connect to ldap server.");
		}
		
		return false;
	}
	
	/**
	 * add a user to ldap server
	 *
	 * @param	array	$user
	 * @return	boolean
	 */
	public function addUser (Array $user) {
		/*
		Eampel:
		$user = array();
		$user['dn'] = 'uid=testuser,ou=Users,dc=ldap,dc=test,dc=server,dc=com';
		$user['objectClass'] = array('inetOrgPerson', 'organizationalPerson', 'posixAccount');
		$user['cn'] = 'Test User';
		$user['gidNumber'] = '5001';
		$user['homeDirectory'] = '/home/testuser';
		$user['sn'] = 'User';
		$user['uid'] = 'testuser';
		$user['uidNumber'] = '5001';
		$user['loginShell'] = '/bin/bash';
		$user['mail'] = 'test@user.com';
		$user['userPassword'] = ''; // use ldap hash class to generate a password
		or see here: http://techiesf1.blogspot.de/2012/03/add-ldap-user-from-php.html
		*/
		if (is_array($user) && !empty($user)) {
			if (ldap_add($this->ldap, "uid=".$user['uid'].",".$this->dn, $user) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * delete ldap user
	 *
	 * @param	string	$user
	 * @return	boolean
	 */
	public function delUser ($user) {
		if (ldap_delete ($this->ldap, $user.",".$this->dn)) {
			return true;
		}
		
		return false;
	}
	
	/**
	 *	returns ldap user array
	 *
	 *	@param	string	$user
	 *	@param	string	$password
	 *	@return	array
	 */
	public function bind ($user, $password) {
		return ldap_bind($this->ldap, $user.",".$this->dn, $password);
	}
	
	/**
	 *	search user on ldap server
	 *
	 * @param	string	$search
	 * @return	resource
	 */
	public function search ($search) {
		return ldap_search($this->ldap, $this->dn, $search);
	}
	
	/**
	 * get entries from search resource
	 *
	 * @param	resource	$resource
	 * @return	array
	 */
	public function get_entries ($resource) {
		return ldap_get_entries($this->ldap, $resource);
	}
	
	/**
	 * close ldap connection
	 */
	public function close () {
		ldap_close($this->ldap);
	}
}
