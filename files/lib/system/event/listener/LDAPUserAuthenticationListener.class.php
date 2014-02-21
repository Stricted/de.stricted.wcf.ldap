<?php
namespace wcf\system\event\listener;
use wcf\system\event\IEventListener;

/**
 * @author      Jan Altensen (Stricted)
 * @copyright   2013-2014 Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     de.stricted.wcf.ldap
 */
class LDAPUserAuthenticationListener implements IEventListener {
	/**
	 * @see IEventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
			$eventObj->className = 'wcf\system\user\authentication\LDAPUserAuthentication';
	}
}
