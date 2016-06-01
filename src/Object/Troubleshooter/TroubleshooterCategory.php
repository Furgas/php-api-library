<?php
namespace Kayako\Api\Client\Object\Troubleshooter;

use Kayako\Api\Client\Common\Helper;
use Kayako\Api\Client\Common\ResultSet;
use Kayako\Api\Client\Object\Base\ObjectBase;
use Kayako\Api\Client\Object\Staff\Staff;

/**
 * Kayako TroubleshooterCategory object.
 *
 * @author Saloni Dhall (https://github.com/SaloniDhall)
 * @link http://wiki.kayako.com/display/DEV/REST+-+TroubleshooterCategory
 * @since Kayako version 4.64.1
 */
class TroubleshooterCategory extends ObjectBase {

	/**
	 * TroubleshooterCategory type - Global.
	 * Private TroubleshooterCategory are visible in your end users in the support center and in the staff control panel.
	 *
	 * @var int
	 */
	const CATEGORY_TYPE_GLOBAL = '1';

	/**
	 * TroubleshooterCategory type - Public.
	 * Public TroubleshooterCategory are visible in both the support center.
	 *
	 * @var int
	 */
	const CATEGORY_TYPE_PUBLIC = '2';

	/**
	 * TroubleshooterCategory type - Private.
	 * Private TroubleshooterCategory are visible only in Staff CP.
	 *
	 * @var int
	 */
	const CATEGORY_TYPE_PRIVATE = '3';

	static protected $controller = '/Troubleshooter/Category';
	static protected $object_xml_name = 'troubleshootercategory';

	/**
	 * TroubleshooterCategory identifier.
	 * @apiField
	 * @var int
	 */
	protected $id;

	/**
	 * TroubleshooterCategory title.
	 * @apiField required=true
	 * @var string
	 */
	protected $title;

	/**
	 * TroubleshooterCategory type.
	 * @apiField required=true
	 * @var int
	 */
	protected $category_type;

	/**
	 * TroubleshooterCategory display order
	 * @apiField required=optional
	 * @var int
	 */
	protected $displayorder;

	/**
	 * TroubleshooterCategory description
	 * @apiField required=true
	 * @var string
	 */
	protected $description;

	/**
	 * If this TroubleshooterCategory is visible to specific user groups only
	 * @see TroubleshooterCategory::$user_group_ids
	 * @var bool
	 */
	protected $user_visibility_custom;

	/**
	 * User group identifiers this TroubleshooterCategory is visible to.
	 * @apiField name=usergroupidlist
	 * @var int[]
	 */
	protected $user_group_ids;

	/**
	 * If this TroubleshooterCategory is visible to specific staff groups only.
	 * @see TroubleshooterCategory::$staff_group_ids
	 * @apiField
	 * @var bool
	 */
	protected $staff_visibility_custom;

	/**
	 * User group identifiers this TroubleshooterCategory is visible to.
	 * @apiField name=staffgroupidlist
	 * @var int[]
	 */
	protected $staff_group_ids = array();

	/**
	 * TroubleshooterCategory created by which staff Id
	 * @apiField required=optional
	 * @var int
	 */
	protected $staff_id;

	/**
	 * TroubleshooterCategory created by which staff
	 * @apiField required=optional
	 * @var int
	 */
	protected $staff;

	protected function parseData($data) {
		$this->id = Helper::assurePositiveInt($data['id']);
		$this->title = Helper::assureString($data['title']);
		$this->category_type = Helper::assureString($data['categorytype']);
		$this->description = Helper::assureString($data['description']);
		$this->displayorder = Helper::assurePositiveInt($data['displayorder']);
		if ($this->user_visibility_custom && is_array($data['usergroupidlist'])) {
			$this->user_group_ids = array();
			if (is_string($data['usergroupidlist'][0]['usergroupid'])) {
				$this->user_group_ids[] = intval($data['usergroupidlist'][0]['usergroupid']);
			} else {
				foreach ($data['usergroupidlist'][0]['usergroupid'] as $user_group_id) {
					$this->user_group_ids[] = intval($user_group_id);
				}
			}
		}

		$this->staff_visibility_custom = Helper::assureBool($data['staffvisibilitycustom']);
		$this->staff_group_ids = array();
		if ($this->staff_visibility_custom && is_array($data['staffgroupidlist'])) {
			if (is_string($data['staffgroupidlist'][0]['staffgroupid'])) {
				$this->staff_group_ids[] = Helper::assurePositiveInt($data['staffgroupidlist'][0]['staffgroupid']);
			} else {
				foreach ($data['staffgroupidlist'][0]['staffgroupid'] as $staff_group_id) {
					$this->staff_group_ids[] = Helper::assurePositiveInt($staff_group_id);
				}
			}
		}

		$this->staff_id = Helper::assureInt($data['staffid']);
	}

	public function buildData($create) {
		$data = array();

		$this->buildDataString($data, 'title', $this->title);
		$this->buildDataString($data, 'categorytype', $this->category_type);
		$this->buildDataString($data, 'description', $this->description);
		$this->buildDataNumeric($data, 'displayorder', $this->displayorder);
		$this->buildDataBool($data, 'uservisibilitycustom', $this->user_visibility_custom);
		if ($this->user_visibility_custom) {
			$this->buildDataList($data, 'usergroupidlist', $this->user_group_ids);
		}

		$this->buildDataBool($data, 'staffvisibilitycustom', $this->staff_visibility_custom);
		if ($this->staff_visibility_custom) {
			$this->buildDataList($data, 'staffgroupidlist', $this->staff_group_ids);
		}
		$this->buildDataNumeric($data, 'staffid', $this->staff_id);

		return $data;
	}

	/**
	 * Fetches all troubleshooter categories from the server.
	 *
	 * @return ResultSet|TroubleshooterCategory[]
	 */
	static public function getAll() {
		return parent::genericGetAll();
	}

	/**
	 * Fetches troubleshooter category from the server by its identifier.
	 *
	 * @param int $id Troubleshooter category identifier.
	 * @return TroubleshooterCategory
	 */
	static public function get($id) {
		return parent::genericGet(array($id));
	}

	public function toString() {
		return sprintf("%s (contents : %s) (category type: %s)", $this->getTitle(), $this->getDescription(), $this->getCategoryType());
	}

	public function getId($complete = false) {
		return $complete ? array($this->id) : $this->id;
	}

	/**
	 * Return category type of the TroubleshooterCategory.
	 *
	 * @see TroubleshooterCategory::CATEGORY_TYPE constants.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getCategoryType() {
		return $this->category_type;
	}

	/**
	 * Sets category type of the TroubleshooterCategory.
	 *
	 * @see TroubleshooterCategory::CATEGORY_TYPE constants.
	 *
	 * @param int $category_type Category type of the TroubleshooterCategory.
	 * @return TroubleshooterCategory
	 */
	public function setCategoryType($category_type) {
		$this->category_type = Helper::assureConstant($category_type, $this, 'CATEGORY_TYPE');
		return $this;
	}

	/**
	 * Returns title of the TroubleshooterCategory.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Sets title of the TroubleshooterCategory.
	 *
	 * @param string $title Title of the TroubleshooterCategory.
	 * @return TroubleshooterCategory
	 */
	public function setTitle($title) {
		$this->title = Helper::assureString($title);
		return $this;
	}

	/**
	 * Returns description of the TroubleshooterCategory.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Sets description of the TroubleshooterCategory.
	 *
	 * @param string $description Description of the TroubleshooterCategory.
	 * @return TroubleshooterCategory
	 */
	public function setDescription($description) {
		$this->description = Helper::assureString($description);
		return $this;
	}

	/**
	 * Returns displayorder of the TroubleshooterCategory.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getDisplayOrder() {
		return $this->displayorder;
	}

	/**
	 * Sets displayorder of the TroubleshooterCategory.
	 *
	 * @param int $display_order of the TroubleshooterCategory.
	 * @return TroubleshooterCategory
	 */
	public function setDisplayOrder($display_order) {
		$this->displayorder = Helper::assureInt($display_order, 0);
		return $this;
	}

	/**
	 * Gets the staff user, the creator of this TroubleshooterCategory.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return Staff
	 */
	public function getStaff($reload = false) {
		if ($this->staff !== null && !$reload)
			return $this->staff;

		if ($this->staff_id === null)
			return null;

		$this->staff = Staff::get($this->staff_id);
		return $this->staff;
	}

	/**
	 * Sets staff user, the creator of this TroubleshooterCategory.
	 *
	 * @param Staff $staff Staff user.
	 * @return TroubleshooterCategory
	 */
	public function setStaff($staff) {
		$this->staff = Helper::assureObject($staff, Staff::class);
		$this->staff_id = $this->staff !== null ? $this->staff->getId() : null;
		return $this;
	}


	/**
	 * Returns true to indicate that visibility of this TroubleshooterCategory is restricted to particular user groups.
	 * Use getUserGroupIds to get their identifiers or getUserGroups to get the objects.
	 *
	 * @return bool
	 * @filterBy
	 */
	public function getUserVisibilityCustom() {
		return $this->user_visibility_custom;
	}

	/**
	 * Sets whether to restrict visibility of this TroubleshooterCategory to particular user groups.
	 * Use setUserGroupIds to set these groups using identifiers set them using objects.
	 * Automatically clears user groups when set to false.
	 *
	 * @param bool $user_visibility_custom True to restrict visibility of this TroubleshooterCategory to particular user groups. False otherwise.
	 * @return TroubleshooterCategory
	 */
	public function setUserVisibilityCustom($user_visibility_custom) {
		$this->user_visibility_custom = Helper::assureBool($user_visibility_custom);
		if ($this->user_visibility_custom === false) {
			$this->user_group_ids = array();
		}
		return $this;
	}

	/**
	 * Returns identifiers of user groups that this TroubleshooterCategory will be visible to.
	 *
	 * @return array
	 * @filterBy name=UserGroupId
	 */
	public function getUserGroupIds() {
		return $this->user_group_ids;
	}

	/**
	 * Sets user groups (using their identifiers) that this TroubleshooterCategory will be visible to.
	 *
	 * @param int[] $user_group_ids Identifiers of user groups that this TroubleshooterCategory will be visible to.
	 * @return TroubleshooterCategory
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
	 * Returns true to indicate that visibility of this TroubleshooterCategory is restricted to particular staff groups.
	 * Use getStaffGroupIds to get their identifiers or getStaffGroups to get the objects.
	 *
	 * @return bool
	 * @filterBy
	 */
	public function getStaffVisibilityCustom() {
		return $this->staff_visibility_custom;
	}

	/**
	 * Sets whether to restrict visibility of this TroubleshooterCategory item to particular staff groups.
	 * Use setStaffGroupIds to set these groups using identifiers to set them using objects.
	 * Automatically clears staff groups when set to false.
	 *
	 * @param bool $staff_visibility_custom True to restrict visibility of this TroubleshooterCategory to particular staff groups. False otherwise.
	 * @return TroubleshooterCategory
	 */
	public function setStaffVisibilityCustom($staff_visibility_custom) {
		$this->staff_visibility_custom = Helper::assureBool($staff_visibility_custom);
		if ($this->staff_visibility_custom === false) {
			$this->staff_group_ids = array();
		}
		return $this;
	}

	/**
	 * Returns identifiers of staff groups that this TroubleshooterCategory item will be visible to.
	 *
	 * @return array
	 * @filterBy name=StaffGroupId
	 */
	public function getStaffGroupIds() {
		return $this->staff_group_ids;
	}

	/**
	 * Sets staff groups (using their identifiers) that this TroubleshooterCategory item will be visible to.
	 *
	 * @param int[] $staff_group_ids Identifiers of staff groups that this TroubleshooterCategory item will be visible to.
	 * @return TroubleshooterCategory
	 */
	public function setStaffGroupIds($staff_group_ids) {
		//normalization to array
		if (!is_array($staff_group_ids)) {
			if (is_numeric($staff_group_ids)) {
				$staff_group_ids = array($staff_group_ids);
			} else {
				$staff_group_ids = array();
			}
		}

		//normalization to positive integer values
		$this->staff_group_ids = array();
		foreach ($staff_group_ids as $staff_group_id) {
			$staff_group_id = Helper::assurePositiveInt($staff_group_id);
			if ($staff_group_id === null)
				continue;

			$this->staff_group_ids[] = $staff_group_id;
		}

		return $this;
	}

	/**
	 * Returns staff Id of the TroubleshooterCategory.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getStaffId() {
		return $this->staff_id;
	}

	/**
	 * Sets staff Id of the TroubleshooterCategory.
	 *
	 * @param string $staff_id of the knoledgebase category.
	 * @return TroubleshooterCategory
	 */
	public function setStaffId($staff_id) {
		$this->staff_id = Helper::assurePositiveInt($staff_id);
		return $this;
	}

	/**
	 * Creates a TroubleshooterCategory.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @see TroubleshooterCategory::CATEGORY_TYPE constants.
	 *
	 * @param string $title Title of TroubleshooterCategory.
	 * @param int $category_type Category type of troubleshooter item.
	 * @param Staff $staff Staff user.
	 * @return TroubleshooterCategory
	 */
	static public function createNew($title, $category_type, Staff $staff) {
		$new_troubleshooter_category = new TroubleshooterCategory();
		$new_troubleshooter_category->setTitle($title);
		$new_troubleshooter_category->setCategoryType($category_type);
		$new_troubleshooter_category->setStaff($staff);
		return $new_troubleshooter_category;
	}
}