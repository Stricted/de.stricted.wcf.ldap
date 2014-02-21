<?php
namespace wcf\system\event\listener;
use wcf\system\event\IEventListener;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * @author      Jan Altensen (Stricted)
 * @copyright   2013-2014 Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     de.stricted.wcf.ldap
 */
class LDAPUserEditListener implements IEventListener {
	/**
	 * @see IEventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (USE_LDAP) {
			if ($className == 'wcf\form\AccountManagementForm') {
				if ($eventName == "validate") {
				}
				else if ($eventName == "saved") {
				}
			}
			else if ($className == 'wcf\acp\form\UserEditForm') {
				if ($eventName == "validate") {
				}
				else if ($eventName == "saved") {
				}
			}
		}
	}
}
