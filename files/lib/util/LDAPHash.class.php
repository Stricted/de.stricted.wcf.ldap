<?php
namespace wcf\util;

/**
 * @author      Jan Altensen (Stricted)
 * @copyright   2013-2014 Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     de.stricted.wcf.ldap
 */
class LDAPHash {
	/**
	 * compare given ldap hash with given password
	 *
	 * @param	string	$password
	 * @param	string	$hash
	 * @return	boolean
	 */
	public function compare($password, $hash) {
		// replace hash method to lowercase
		$search  = array("SSHA", "SHA256", "SHA384", "SHA512", "SSHA256", "SSHA384", "SSHA512", "MD5", "SMD5", "SHA", "CRYPT"); 
		$replace = array("ssha", "sha256", "sha384", "sha512", "ssha256", "ssha384", "ssha512", "md5", "smd5", "sha", "crypt"); 
		$hash = str_replace($search, $replace, $hash);

		$encrypted_password = '';

		// plain password
		if ($password == $hash) {
			return true;
		}

		preg_match("/^{([a-z0-9]+)}([\s\S]+)/i", $hash, $method);
		if (isset($method[1]) && !empty($method[1]) && isset($method[2]) && !empty($method[2])) {
			switch ($method[1]) {
				case "md5":
					$encrypted_password = '{md5}' . base64_encode(hash("md5", $password, true));
					break;

				case "smd5":
					$salt = substr(base64_decode($method[2]), 16);
					$encrypted_password = '{smd5}' . base64_encode(hash("md5", $password.$salt, true).$salt);
					break;

				case "sha":
					$encrypted_password = '{sha}' . base64_encode(hash("sha1", $password, true));
					break;

				case "ssha":
					$salt = substr(base64_decode($method[2]), 20);
					$encrypted_password = '{ssha}' . base64_encode(hash("sha1", $password.$salt, true).$salt);
					break;

				case "sha256":
					$encrypted_password = "{sha256}".base64_encode(hash("sha256", $password, true));
					break;

				case "ssha256":
					$salt = substr(base64_decode($method[2]), 32);
					$encrypted_password =  "{ssha256}".base64_encode(hash("sha256", $password.$salt, true).$salt);
					break;

				case "sha384":
					$encrypted_password = "{sha384}".base64_encode(hash("sha348", $password, true));
					break;

				case "ssha384":
					$salt = substr(base64_decode($method[2]), 48);
					$encrypted_password =  "{ssha384}".base64_encode(hash("sha384", $password.$salt, true).$salt);
					break;

				case "sha512":
					$encrypted_password = "{sha512}".base64_encode(hash("sha512", $password, true));
					break;

				case "ssha512":
					$salt = substr(base64_decode($method[2]), 64);
					$encrypted_password =  "{sha512}".base64_encode(hash("sha512", $password.$salt, true).$salt);
					break;

				case "crypt":
					$encrypted_password = "{crypt}".crypt($password, $method[2]);
					break;

				default:
					die("Unsupported password hash format");
					break;
			}
		}

		if ($hash == $encrypted_password) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * return supported hash methods
	 *
	 * @return	array
	 */
	public function supportedMethods () {
		return array("ssha", "sha256", "sha384", "sha512", "ssha256", "ssha384", "ssha512", "md5", "smd5", "sha", "crypt", "plain");
	}

	/**
	 * hash given password with given hash method
	 *
	 * @param	string	$password
	 * @param	string	$method
	 * @return	string
	 */
	public function hash($password, $method) {
		$salt = substr(sha1(time()), 0, 4);
		$method = strtolower($method);
		switch ($method) {
			case "ssha":
				$hash = base64_encode(hash("sha1", $password.$salt, true).$salt);
				break;

			case "sha256":
				$hash = base64_encode(hash("sha256", $password, true));
				break;

			case "sha384":
				$hash = base64_encode(hash("sha384", $password, true));
				break;

			case "sha512":
				$hash = base64_encode(hash("sha512", $password, true));
				break;

			case "ssha256":
				$hash = base64_encode(hash("sha256", $password.$salt, true).$salt);
				break;

			case "ssha384":
				$hash = base64_encode(hash("sha384", $password.$salt, true).$salt);
				break;

			case "ssha512":
				$hash = base64_encode(hash("sha512", $password.$salt, true).$salt);
				break;

			case "md5":
				$hash = base64_encode(hash("md5", $password, true));
				break;

			case "smd5":
				$hash = base64_encode(hash("md5", $password.$salt, true).$salt);
				break;

			case "sha":
				$hash = base64_encode(hash("sha1", $password, true));
				break;

			case "crypt":
				$hash = crypt($password, $salt);
				break;

			case "plain":
				$hash = $password;
				break;

			default :
				die("Unsupported hash method");
				break;
		}

		return ($method == "plain" ? "" : "{".$method."}").$hash;
	}
}
