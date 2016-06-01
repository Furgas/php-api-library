<?php
namespace Kayako\Api\Client\Object\Ticket;

use Kayako\Api\Client\Common\Helper;
use Kayako\Api\Client\Common\ResultSet;
use Kayako\Api\Client\Object\Base\ObjectBase;
use Kayako\Api\Client\Object\Department\Department;
use Kayako\Api\Client\Object\User\UserGroup;

/**
 * Kayako TicketType object.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @link http://wiki.kayako.com/display/DEV/REST+-+TicketType
 * @since Kayako version 4.01.240
 */
class TicketType extends ObjectBase {

	const TYPE_PUBLIC = 'public';
	const TYPE_PRIVATE = 'private';

	static protected $controller = '/Tickets/TicketType';
	static protected $object_xml_name = 'tickettype';
	protected $read_only = true;

	/**
	 * Ticket type identifier.
	 * @apiField
	 * @var int
	 */
	protected $id;

	/**
	 * Ticket type title.
	 * @apiField
	 * @var string
	 */
	protected $title;

	/**
	 * Ticket type display order.
	 * @apiField
	 * @var int
	 */
	protected $display_order;

	/**
	 * Linked department identifier.
	 *
	 * If a ticket type is linked to a department, it will be visible only under the linked department.
	 *
	 * @apiField
	 * @var int
	 */
	protected $department_id;

	/**
	 * Path to icon displayed in GUI for this ticket type.
	 * @apiField
	 * @var string
	 */
	protected $display_icon;

	/**
	 * Type of this ticket type.
	 *
	 * @see TicketType::TYPE constants.
	 *
	 * @apiField
	 * @var string
	 */
	protected $type;

	/**
	 * If this ticket type is visible to specific user groups only.
	 * @apiField
	 * @var bool
	 */
	protected $user_visibility_custom;

	/**
	 * Identifier of user group this ticket type is visible to.
	 * @apiField name=usergroupid
	 * @var int[]
	 */
	protected $user_group_ids = array();

	/**
	 * Linked department.
	 * @var Department
	 */
	private $department = null;

	/**
	 * User groups this ticket type is visible to.
	 * @var UserGroup[]
	 */
	private $user_groups = null;

	protected function parseData($data) {
		$this->id = intval($data['id']);
		$this->title = $data['title'];
		$this->display_order = intval($data['displayorder']);
		$this->department_id = Helper::assurePositiveInt($data['departmentid']);
		$this->display_icon = $data['displayicon'];
		$this->type = $data['type'];
		$this->user_visibility_custom = Helper::assureBool($data['uservisibilitycustom']);
		if ($this->user_visibility_custom && is_array($data['usergroupid'])) {
			foreach ($data['usergroupid'] as $user_group_id) {
				$this->user_group_ids[] = intval($user_group_id);
			}
		}
	}

	/**
	 * Fetches all ticket types from the server.
	 *
	 * @return ResultSet|TicketType[]
	 */
	static public function getAll() {
		return parent::genericGetAll();
	}

	/**
	 * Fetches ticket type from the server by its identifier.
	 *
	 * @param int $id Ticket type identifier.
	 * @return TicketType
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
	 * Returns ticket type title.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Returns ticket type display order.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getDisplayOrder() {
		return $this->display_order;
	}

	/**
	 * Returns linked department identifier.
	 *
	 * If a ticket type is linked to a department, it will be visible only under the linked department.
	 *
	 * @return int
	 * @filterBy
	 */
	public function getDepartmentId() {
		return $this->department_id;
	}

	/**
	 * Returns linked department.
	 *
	 * If a ticket type is linked to a department, it will be visible only under the linked department.
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return Department
	 */
	public function getDepartment($reload = false) {
		if ($this->department !== null && !$reload)
			return $this->department;

		if ($this->department_id === null || $this->department_id <= 0)
			return null;

		$this->department = Department::get($this->department_id);
		return $this->department;
	}

	/**
	 * Returns path to icon displayed in GUI for this ticket type.
	 *
	 * @return string
	 */
	public function getDisplayIcon() {
		return $this->display_icon;
	}

	/**
	 * Returns type of this ticket type.
	 *
	 * @see TicketType::TYPE constants.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Returns whether this ticket type is visible to specific user groups only.
	 *
	 * @return bool
	 * @filterBy
	 */
	public function getUserVisibilityCustom() {
		return $this->user_visibility_custom;
	}

	/**
	 * Returns identifiers of user groups that this ticket type is visible to.
	 *
	 * @return int[]
	 * @filterBy name=UserGroupId
	 * @orderBy name=UserGroupId
	 */
	public function getUserGroupIds() {
		return $this->user_group_ids;
	}

	/**
	 * Returns user groups that this ticket type is visible to.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return ResultSet
	 */
	public function getUserGroups($reload = false) {
		foreach ($this->user_group_ids as $user_group_id) {
			if (!is_array($this->user_groups) || !array_key_exists($user_group_id, $this->user_groups) || $reload) {
				$this->user_groups[$user_group_id] = UserGroup::get($user_group_id);
			}
		}
		return new ResultSet(array_values($this->user_groups));
	}

	/**
	 * Returns whether this ticket type is visible to specified user group.
	 *
	 * @param UserGroup|int $user_group User group or its identifier.
	 * @return bool
	 * @filterBy
	 */
	public function isVisibleToUserGroup($user_group) {
		if ($this->type !== self::TYPE_PUBLIC)
			return false;

		if ($this->user_visibility_custom === false)
			return true;

		if ($user_group instanceof UserGroup) {
			$user_group_id = $user_group->getId();
		} else {
			$user_group_id = intval($user_group);
		}

		return in_array($user_group_id, $this->user_group_ids);
	}

	/**
	 * Returns whether this ticket type is visible under specified department.
	 *
	 * @param Department|int $department Department or its identifier.
	 * @return bool
	 * @filterBy
	 */
	public function isAvailableInDepartment($department) {
		if ($this->department_id == null)
			return true;

		if ($department instanceof Department) {
			$department_id = $department->getId();
		} else {
			$department_id = intval($department);
		}

		return $this->department_id === $department_id;
	}
}