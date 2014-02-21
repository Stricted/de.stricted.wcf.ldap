<?php
namespace wcf\system\user\authentication;
use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\data\user\UserEditor;
use wcf\data\user\UserProfileAction;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\util\HeaderUtil;
use wcf\util\LDAP;
use wcf\util\PasswordUtil;
use wcf\util\UserUtil;
use wcf\system\WCF;

/**
 * @author      Jan Altensen (Stricted)
 * @copyright   2013-2014 Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     de.stricted.wcf.ldap
 */
class LDAPUserAuthentication extends DefaultUserAuthentication {
	protected $email = '';
	protected $username = '';

	/**
	 * Checks the given user data.
	 *
	 * @param	string		$username
	 * @param 	string		$password
	 * @return	boolean
	 */
	protected function checkWCFUser($username, $password) {
		if (UserUtil::isValidEmail($username)) {
			$user = User::getUserByEmail($username);
		}
		else {
			$user = User::getUserByUsername($username);
		}
		
		if ($user->userID != 0) {
			if ($user->checkPassword($password)) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Checks the given user data.
	 *
	 * @param	string		$username
	 * @param 	string		$password
	 * @return	boolean
	 */
	protected function checkLDAPUser($username, $password) {
		$ldap = new LDAP();
		// connect
		$connect = $ldap->connect(LDAP_SERVER_HOST, LDAP_SERVER_PORT, LDAP_SERVER_DN);
		if ($connect) {
			// find user
			if ($ldap->bind($username, $password)) {
				// try to find user email
				if (($search = $ldap->search('uid='.$username))) {
					$results = $ldap->get_entries($search);
					if (isset($results[0]['mail'][0])) {
						$this->email = $results[0]['mail'][0];
					}
				}
				
				$ldap->close();
				return true;
			}
			else if (UserUtil::isValidEmail($username) && ($search = $ldap->search('mail='.$username))) {
				$results = $ldap->get_entries($search);
				if(isset($results[0]['uid'][0])) {
					$this->username = $results[0]['uid'][0];
					$ldap->close($connect);
					
					return $this->checkLDAPUser($this->ldapusername, $password);
				}
			}
		}
		// no ldap user or connection -> check user from wcf
		$ldap->close($connect);
		if(LDAP_CHECK_WCF) {
			return $this->checkWCFUser($username, $password);
		}
		
		return false;
	}

	/**
	 * @see IUserAuthentication::loginManually()
	 */
	public function loginManually($username, $password, $userClassname = 'wcf\data\user\User') {
		if (!$this->checkLDAPUser($username, $password)) {
			throw new UserInputException('password', 'false');
		}

		if (!empty($this->username)) {
			$username = $this->username;
		}
		
		if (UserUtil::isValidEmail($username)) {
			$user = User::getUserByEmail($username);
		}
		else {
			$user = User::getUserByUsername($username);
		}
		
		if ($user->userID == 0) {
			// create user
			if (!empty($this->email) && isset($this->email)) {
				$groupIDs = UserGroup::getGroupIDsByType(array(UserGroup::EVERYONE, UserGroup::GUESTS, UserGroup::USERS));
				$languageID = array(LanguageFactory::getInstance()->getDefaultLanguageID());
				$addDefaultGroups = true;
				$saveOptions = array();
				$additionalFields = array();
				$additionalFields['languageID'] = WCF::getLanguage()->languageID;
				$additionalFields['registrationIpAddress'] = WCF::getSession()->ipAddress;
				$data = array(
					'data' => array_merge($additionalFields, array(
						'username' => $username,
						'email' => $this->email,
						'password' => $password,
					)),
					'groups' => $groupIDs,
					'languages' => $languageID,
					'options' => $saveOptions,
					'addDefaultGroups' => $addDefaultGroups
				);
				
				$objectAction = new UserAction(array(), 'create', $data);
				$result = $objectAction->executeAction();
				$user = $result['returnValues'];
				$userEditor = new UserEditor($user);

				// update user rank
				if (MODULE_USER_RANK) {
					$action = new UserProfileAction(array($userEditor), 'updateUserRank');
					$action->executeAction();
				}
				// update user online marking
				$action = new UserProfileAction(array($userEditor), 'updateUserOnlineMarking');
				$action->executeAction();

			}
			else {
				throw new UserInputException('password', 'false');
			}
		}
		
		return $user;
	}
}
