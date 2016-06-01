<?php
namespace Kayako\Api\Client\Object\Department;

use Kayako\Api\Client\Common\Helper;
use Kayako\Api\Client\Common\ResultSet;
use Kayako\Api\Client\Object\Base\ObjectBase;
use Kayako\Api\Client\Object\Ticket\Ticket;
use Kayako\Api\Client\Object\User\UserGroup;

/**
 * Kayako Department object.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @link http://wiki.kayako.com/display/DEV/REST+-+Department
 * @since Kayako version 4.01.204
 */
class Department extends ObjectBase {

	/**
	 * Module a department can be associated with - Tickets.
	 *
	 * @var string
	 */
	const MODULE_TICKETS = 'tickets';

	/**
	 * Module a department can be associated with - Livechat.
	 *
	 * @var string
	 */
	const MODULE_LIVECHAT = 'livechat';

	/**
	 * Type of department - public.
	 *
	 * @var string
	 */
	const TYPE_PUBLIC = 'public';

	/**
	 * Type of department - private.
	 *
	 * @var string
	 */
	const TYPE_PRIVATE = 'private';

	static protected $controller = '/Base/Department';
	static protected $object_xml_name = 'department';

	/**
	 * Department identifier.
	 * @apiField
	 * @var int
	 */
	protected $id;

	/**
	 * Department title.
	 * @apiField required=true
	 * @var string
	 */
	protected $title;

	/**
	 * Department type.
	 * @apiField required_create=true
	 * @var int
	 */
	protected $type;

	/**
	 * Department module.
	 * @apiField required_create=true
	 * @var int
	 */
	protected $module;

	/**
	 * Department display order.
	 * @apiField
	 * @var int
	 */
	protected $display_order;

	/**
	 * Parent department identifier.
	 * @apiField
	 * @var int
	 */
	protected $parent_department_id;

	/**
	 * If this department is visible to specific user groups only.
	 * @see Department::$user_group_ids
	 * @apiField
	 * @var bool
	 */
	protected $user_visibility_custom = false;

	/**
	 * User group identifiers this department is visible to.
	 * @apiField name=usergroups
	 * @var int[]
	 */
	protected $user_group_ids = array();

	/**
	 * Parent department.
	 * @var Department
	 */
	private $parent_department = null;

	/**
	 * User groups this department is visible to.
	 * @var UserGroup[]
	 */
	private $user_groups = null;

	protected function parseData($data) {
		$this->id = intval($data['id']);
		$this->title = $data['title'];
		$this->type = $data['type'];
		$this->module = $data['module'];
		$this->display_order = intval($data['displayorder']);
		$this->parent_department_id = Helper::assurePositiveInt($data['parentdepartmentid']);
		$this->user_visibility_custom = intval($data['uservisibilitycustom']) === 0 ? false : true;
		if ($this->user_visibility_custom && is_array($data['usergroups'])) {
			$this->user_group_ids = array();
			if (is_string($data['usergroups'][0]['id'])) {
				$this->user_group_ids[] = intval($data['usergroups'][0]['id']);
			} else {
				foreach ($data['usergroups'][0]['id'] as $user_group_id) {
					$this->user_group_ids[] = intval($user_group_id);
				}
			}
		}
	}

	public function buildData($create) {
		$this->checkRequiredAPIFields($create);

		$data = array();

		$data['title'] = $this->title;
		$data['type'] = $this->type;
		$data['module'] = $this->module;

		if (is_numeric($this->display_order))
			$data['displayorder'] = $this->display_order;

		if (is_numeric($this->parent_department_id))
			$data['parentdepartmentid'] = $this->parent_department_id;

		$data['uservisibilitycustom'] = $this->user_visibility_custom ? 1 : 0;

		if ($this->user_visibility_custom) {
			$data['usergroupid'] = $this->user_group_ids;
		}

		return $data;
	}

	/**
	 * Fetches all departments from the server.
	 *
	 * @return ResultSet|Department[]
	 */
	static public function getAll() {
		return parent::genericGetAll();
	}

	/**
	 * Fetches department from the server by its identifier.
	 *
	 * @param int $id Department identifier.
	 * @return Department
	 */
	static public function get($id) {
		return parent::genericGet(array($id));
	}

	public function toString() {
		return sprintf("%s (type: %s, module: %s)", $this->getTitle(), $this->getType(), $this->getModule());
	}

	public function getId($complete = false) {
		return $complete ? array($this->id) : $this->id;
	}

	/**
	 * Returns title of the department.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Sets title of the department.
	 *
	 * @param string $title Title of the department.
	 * @return Department
	 */
	public function setTitle($title) {
		$this->title = Helper::assureString($title);
		return $this;
	}

	/**
	 * Return type of the department.
	 *
	 * @see Department::TYPE constants.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Sets type of the department.
	 *
	 * @see Department::TYPE constants.
	 *
	 * @param string $type Type of the department.
	 * @return Department
	 */
	public function setType($type) {
		$this->type = Helper::assureConstant($type, $this, 'TYPE');
		return $this;
	}

	/**
	 * Returns module the department is associated with.
	 *
	 * @see Department::MODULE constants.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getModule() {
		return $this->module;
	}

	/**
	 * Sets module the department will be associated with.
	 *
	 * @see Department::MODULE constants.
	 *
	 * @param string $module Module the department will be associated with.
	 * @return Department
	 */
	public function setModule($module) {
		$this->module = Helper::assureConstant($module, $this, 'MODULE');
		return $this;
	}

	/**
	 * Returns display order of the department.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getDisplayOrder() {
		return $this->display_order;
	}

	/**
	 * Sets display order of the department.
	 *
	 * @param int $display_order A positive integer that the helpdesk will use to sort departments when displaying them (ascending).
	 * @return Department
	 */
	public function setDisplayOrder($display_order) {
		$this->display_order = Helper::assureInt($display_order, 0);
		return $this;
	}

	/**
	 * Returns identifier of parent department for this department.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getParentDepartmentId() {
		return $this->parent_department_id;
	}

	/**
	 * Sets the identifier of parent department for this department.
	 *
	 * @param int $parent_department_id Identifier of department that will be the parent for this department.
	 * @return Department
	 */
	public function setParentDepartmentId($parent_department_id) {
		$this->parent_department_id = Helper::assurePositiveInt($parent_department_id);
		$this->parent_department = null;
		return $this;
	}

	/**
	 * Returns department object that is the parent for this department.
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return Department
	 */
	public function getParentDepartment($reload = false) {
		if ($this->parent_department !== null && !$reload)
			return $this->parent_department;

		if ($this->parent_department_id === null || $this->parent_department_id <= 0)
			return null;

		$this->parent_department = Department::get($this->parent_department_id);
		return $this->parent_department;
	}

	/**
	 * Sets parent department for this department.
	 *
	 * @param Department $parent_department Department object that will be the parent for this department.
	 * @return Department
	 */
	public function setParentDepartment($parent_department) {
		$this->parent_department = Helper::assureObject($parent_department, Department::class);
		$this->parent_department_id = $this->parent_department !== null ? $this->parent_department->getId() : null;
		return $this;
	}

	/**
	 * Returns true to indicate that visibility of this department is restricted to particular user groups.
	 * Use getUserGroupIds to get their identifiers or getUserGroups to get the objects.
	 *
	 * @return bool
	 * @filterBy
	 */
	public function getUserVisibilityCustom() {
		return $this->user_visibility_custom;
	}

	/**
	 * Sets wheter to restrict visibility of this department to particular user groups.
	 * Use setUserGroupIds to set these groups using identifiers or addUserGroup to set them using objects.
	 * Automatically clears user groups when set to false.
	 *
	 * @param bool $user_visibility_custom True to restrict visibility of this department to particular user groups. False otherwise.
	 * @return Department
	 */
	public function setUserVisibilityCustom($user_visibility_custom) {
		$this->user_visibility_custom = Helper::assureBool($user_visibility_custom);
		if ($this->user_visibility_custom === false) {
			$this->user_group_ids = array();
			$this->user_groups = null;
		}
		return $this;
	}

	/**
	 * Returns identifiers of user groups that this department will be visible to.
	 *
	 * @return array
	 * @filterBy name=UserGroupId
	 */
	public function getUserGroupIds() {
		return $this->user_group_ids;
	}

	/**
	 * Sets user groups (using their identifiers) that this department will be visible to.
	 *
	 * @param int[] $user_group_ids Identifiers of user groups that this department will be visible to.
	 * @return Department
	 */
	public function setUserGroupIds($user_group_ids) {
		//normalization to array
		if (!is_array($user_group_ids)) {
			if (is_numeric($user_group_ids)) {
				$user_group_ids = array($user_group_ids);
			} else {
				$user_group_ids = array();
			}
		}

		//normalization to positive integer values
		$this->user_group_ids = array();
		foreach ($user_group_ids as $user_group_id) {
			$user_group_id = Helper::assurePositiveInt($user_group_id);
			if ($user_group_id === null)
				continue;

			$this->user_group_ids[] = $user_group_id;
		}

		return $this;
	}

	/**
	 * Returns user groups that this department will be visible to.
	 * Result is cached until the end of script.
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

		foreach ($this->user_groups as $user_group_id => $user_group) {
			if (!in_array($user_group_id, $this->user_group_ids)) {
				unset($this->user_groups[$user_group_id]);
			}
		}

		return new ResultSet(array_values($this->user_groups));
	}

	/**
	 * Returns whether this department is visible to specified user group.
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
	 * Add user group to the list of groups that this department will be visible to.
	 * Automatically sets custom user visibility flag to True.
	 *
	 * @param UserGroup $user_group User group that this department will be visible to.
	 * @param bool $clear Clear the list before adding.
	 * @return Department
	 */
	public function addUserGroup(UserGroup $user_group, $clear = false) {
		if ($clear) {
			$this->user_groups = array();
			$this->user_group_ids = array();
		}

		if (!in_array($user_group->getId(), $this->user_group_ids)) {
			$this->user_group_ids[] = $user_group->getId();
			$this->user_groups[$user_group->getId()] = $user_group;
			$this->user_visibility_custom = true;
		}

		return $this;
	}

	/**
	 * Creates new department.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param string $title Title of new department.
	 * @param string $type Type of new department - one of Department::TYPE_* constants.
	 * @param string $module Module of new department - one of Department::MODULE_* constants.
	 * @return Department
	 */
	static public function createNew($title, $type = self::TYPE_PUBLIC, $module = self::MODULE_TICKETS) {
		$new_department = new Department();
		$new_department->setTitle($title);
		$new_department->setType($type);
		$new_department->setModule($module);
		return $new_department;
	}

	/**
	 * Creates new subdepartment in this department. Module of new department will be the same as parent department's module.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param string $title Title of new department.
	 * @param string $type Type of new department - one of Department::TYPE_* constants.
	 * @return Department
	 */
	public function newSubdepartment($title, $type = self::TYPE_PUBLIC) {
		$new_department = Department::createNew($title, $type, $this->getModule());
		$new_department->setParentDepartment($this);
		return $new_department;
	}

	/**
	 * Creates new ticket in this department with creator user automatically created by server using provided name and e-mail.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param string $creator_full_name Creator full name.
	 * @param string $creator_email Creator e-mail.
	 * @param string $contents Contents of the first post.
	 * @param string $subject Subject of new ticket.
	 * @return Ticket
	 */
	public function newTicketAuto($creator_full_name, $creator_email, $contents, $subject) {
		return Ticket::createNewAuto($this, $creator_full_name, $creator_email, $contents, $subject);
	}
}