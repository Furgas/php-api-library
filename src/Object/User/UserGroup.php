<?php
namespace Kayako\Api\Client\Object\User;

use Kayako\Api\Client\Common\Helper;
use Kayako\Api\Client\Common\ResultSet;
use Kayako\Api\Client\Object\Base\ObjectBase;

/**
 * Kayako UserGroup object.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @link http://wiki.kayako.com/display/DEV/REST+-+UserGroup
 * @since Kayako version 4.01.204
 */
class UserGroup extends ObjectBase {

	/**
	 * Type of user group - guest.
	 * @var int
	 */
	const TYPE_GUEST = 'guest';

	/**
	 * Type of user group - registered.
	 * @var string
	 */
	const TYPE_REGISTERED = 'registered';

	static protected $controller = '/Base/UserGroup';
	static protected $object_xml_name = 'usergroup';

	/**
	 * User group identifier.
	 * @apiField
	 * @var int
	 */
	protected $id;

	/**
	 * User group title.
	 * @apiField required=true
	 * @var string
	 */
	protected $title;

	/**
	 * User group type.
	 *
	 * @see UserGroup::TYPE constants.
	 *
	 * @apiField required_create=true alias=grouptype
	 * @var string
	 */
	protected $type;

	/**
	 * Whether this user group is master group (built-in).
	 * @apiField
	 * @var bool
	 */
	protected $is_master;

	protected function parseData($data) {
		$this->id = intval($data['id']);
		$this->title = $data['title'];
		$this->type = $data['grouptype'];
		$this->is_master = Helper::assureBool($data['ismaster']);
	}

	public function buildData($create) {
		$this->checkRequiredAPIFields($create);

		$data = array();

		$data['title'] = $this->title;
		$data['grouptype'] = $this->type;

		return $data;
	}

	/**
	 * Fetches all user groups from the server.
	 *
	 * @return ResultSet|UserGroup[]
	 */
	static public function getAll() {
		return parent::genericGetAll();
	}

	/**
	 * Fetches user group from the server by its identifier.
	 *
	 * @param int $id User group identifier.
	 * @return UserGroup
	 */
	static public function get($id) {
		return parent::genericGet(array($id));
	}

	public function toString() {
		return sprintf("%s (type: %s)", $this->getTitle(), $this->getType());
	}

	public function getId($complete = false) {
		return $complete ? array($this->id) : $this->id;
	}

	/**
	 * Returns title of the user group.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Sets title of the user group.
	 *
	 * @param string $title Title of the user group.
	 * @return UserGroup
	 */
	public function setTitle($title) {
		$this->title = Helper::assureString($title);
		return $this;
	}

	/**
	 * Returns type of the user group.
	 *
	 * @see UserGroup::TYPE constants.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Sets type of the user group.
	 *
	 * @see UserGroup::TYPE constants.
	 *
	 * @param string $type Type of the user group.
	 * @return UserGroup
	 */
	public function setType($type) {
		$this->type = Helper::assureConstant($type, $this, 'TYPE');
		return $this;
	}

	/**
	 * Returns whether the user group is master group (built-in).
	 *
	 * @return bool
	 * @filterBy
	 */
	public function getIsMaster() {
		return $this->is_master;
	}

	/**
	 * Creates new user group.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param string $title Title of new user group.
	 * @param string $type Type of new user group - one of UserGroup::TYPE_* constants.
	 * @return UserGroup
	 */
	static public function createNew($title, $type = self::TYPE_REGISTERED) {
		$new_user_group = new UserGroup();
		$new_user_group->setTitle($title);
		$new_user_group->setType($type);
		return $new_user_group;
	}

	/**
	 * Creates new user in this user group.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param string $full_name Full name of new user.
	 * @param string $email E-mail address of new user.
	 * @param string $password Password of new user.
	 * @return User
	 */
	public function newUser($full_name, $email, $password) {
		return User::createNew($full_name, $email, $this, $password);
	}
}