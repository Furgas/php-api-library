<?php
namespace Kayako\Api\Client\Object\Staff;

use Kayako\Api\Client\Common\Helper;
use Kayako\Api\Client\Common\ResultSet;
use Kayako\Api\Client\Object\Base\ObjectBase;
use Kayako\Api\Client\Object\Department\Department;
use Kayako\Api\Client\Object\News\NewsItem;
use Kayako\Api\Client\Object\Ticket\Ticket;

/**
 * Kayako Staff object.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @link http://wiki.kayako.com/display/DEV/REST+-+Staff
 * @since Kayako version 4.01.204
 */
class Staff extends ObjectBase {

	static protected $controller = '/Base/Staff';
	static protected $object_xml_name = 'staff';

	/**
	 * Staff identifier.
	 * @apiField
	 * @var int
	 */
	protected $id;

	/**
	 * Staff group identifier.
	 * @apiField required_create=true
	 * @var int
	 */
	protected $staff_group_id;

	/**
	 * Staff first name.
	 * @apiField required=true
	 * @var string
	 */
	protected $first_name;

	/**
	 * Staff last name.
	 * @apiField required=true
	 * @var string
	 */
	protected $last_name;

	/**
	 * Staff full name.
	 * @apiField
	 * @var string
	 */
	protected $full_name;

	/**
	 * Staff username (login).
	 * @apiField required_create=true
	 * @var string
	 */
	protected $user_name;

	/**
	 * Staff e-mail.
	 * @apiField required_create=true
	 * @var string
	 */
	protected $email;

	/**
	 * Staff designation.
	 * @apiField
	 * @var string
	 */
	protected $designation;

	/**
	 * Staff livechat greeting message.
	 * @apiField
	 * @var string
	 */
	protected $greeting;

	/**
	 * Staff signature appended to posts.
	 * @apiField
	 * @var string
	 */
	protected $signature;

	/**
	 * Staff mobile number.
	 * @apiField
	 * @var string
	 */
	protected $mobile_number;

	/**
	 * Is this staff enabled.
	 * @apiField
	 * @var bool
	 */
	protected $is_enabled = true;

	/**
	 * Staff timezone.
	 * @apiField
	 * @var string
	 */
	protected $timezone = 'GMT';

	/**
	 * Is Daylight Saving Time enabled.
	 * @apiField
	 * @var bool
	 */
	protected $enable_dst = false;

	/**
	 * Staff password.
	 * @apiField required_create=true
	 * @var string
	 */
	protected $password;

	/**
	 * Staff group.
	 * @var StaffGroup
	 */
	private $staff_group = null;

	protected function parseData($data) {
		$this->id = intval($data['id']);
		$this->staff_group_id = Helper::assurePositiveInt($data['staffgroupid']);
		$this->first_name = $data['firstname'];
		$this->last_name = $data['lastname'];
		$this->full_name = $data['fullname'];
		$this->user_name = $data['username'];
		$this->email = $data['email'];
		$this->designation = $data['designation'];
		$this->greeting = $data['greeting'];
		$this->mobile_number = $data['mobilenumber'];
		$this->is_enabled = Helper::assureBool($data['isenabled']);
		$this->timezone = $data['timezone'];
		$this->enable_dst = Helper::assureBool($data['enabledst']);
	}

	public function buildData($create) {
		$this->checkRequiredAPIFields($create);

		$data = array();

		$data['staffgroupid'] = $this->staff_group_id;
		$data['firstname'] = $this->first_name;
		$data['lastname'] = $this->last_name;
		$data['username'] = $this->user_name;
		$data['email'] = $this->email;
		$data['designation'] = $this->designation;
		$data['greeting'] = $this->greeting;
		if (strlen($this->signature) > 0)
			$data['staffsignature'] = $this->signature;
		$data['mobilenumber'] = $this->mobile_number;
		$data['isenabled'] = $this->is_enabled ? 1 : 0;
		$data['timezone'] = $this->timezone;
		$data['enabledst'] = $this->enable_dst ? 1 : 0;
		if (strlen($this->password) > 0)
			$data['password'] = $this->password;

		return $data;
	}

	/**
	 * Fetches all staff members from the server.
	 *
	 * @return ResultSet|Staff[]
	 */
	static public function getAll() {
		return parent::genericGetAll();
	}

	/**
	 * Fetches staff from the server by its identifier.
	 *
	 * @param int $id Staff identifier.
	 * @return Staff
	 */
	static public function get($id) {
		return parent::genericGet(array($id));
	}

	public function toString() {
		return sprintf("%s (username: %s, email: %s)", $this->getFullName(), $this->getUserName(), $this->getEmail());
	}

	public function getId($complete = false) {
		return $complete ? array($this->id) : $this->id;
	}

	/**
	 * Returns staff group identifier of the staff user.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getStaffGroupId() {
		return $this->staff_group_id;
	}

	/**
	 * Sets staff group identifier for the staff user.
	 *
	 * @param int $staff_group_id Staff group identifier.
	 * @return Staff
	 */
	public function setStaffGroupId($staff_group_id) {
		$this->staff_group_id = Helper::assurePositiveInt($staff_group_id);
		$this->staff_group = null;
		return $this;
	}

	/**
	 * Returns staff group of the staff user.
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return StaffGroup
	 */
	public function getStaffGroup($reload = false) {
		if ($this->staff_group !== null && !$reload)
			return $this->staff_group;

		if ($this->staff_group_id === null || $this->staff_group_id <= 0)
			return null;

		$this->staff_group = StaffGroup::get($this->staff_group_id);
		return $this->staff_group;
	}

	/**
	 * Sets staff group for the staff user.
	 *
	 * @param StaffGroup $staff_group Staff group object.
	 * @return Staff
	 */
	public function setStaffGroup($staff_group) {
		$this->staff_group = Helper::assureObject($staff_group, StaffGroup::class);
		$this->staff_group_id = $this->staff_group !== null ? $staff_group->getId() : null;
		return $this;
	}

	/**
	 * Returns first name of the staff user.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getFirstName() {
		return $this->first_name;
	}

	/**
	 * Sets first name of the staff user.
	 *
	 * @param string $first_name First name of the staff user.
	 * @return Staff
	 */
	public function setFirstName($first_name) {
		$this->first_name = Helper::assureString($first_name);
		return $this;
	}

	/**
	 * Returns last name of the staff user.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getLastName() {
		return $this->last_name;
	}

	/**
	 * Sets last name of the staff user.
	 *
	 * @param string $last_name Last name of the staff user.
	 * @return Staff
	 */
	public function setLastName($last_name) {
		$this->last_name = Helper::assureString($last_name);
		return $this;
	}

	/**
	 * Returns full name of the staff user.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getFullName() {
		return $this->full_name;
	}

	/**
	 * Returns login username of the staff user.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getUserName() {
		return $this->user_name;
	}

	/**
	 * Sets login username of the staff user.
	 *
	 * @param string $user_name Login username of the staff user.
	 * @return Staff
	 */
	public function setUserName($user_name) {
		$this->user_name = Helper::assureString($user_name);
		return $this;
	}

	/**
	 * Returns e-mail address of the staff user.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * Sets e-mail address of the staff user.
	 *
	 * @param string $email E-mail address of the staff user.
	 * @return Staff
	 */
	public function setEmail($email) {
		$this->email = Helper::assureString($email);
		return $this;
	}

	/**
	 * Returns designation of the staff user.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getDesignation() {
		return $this->designation;
	}

	/**
	 * Sets designation of the staff user.
	 *
	 * @param string $designation Designation of the staff user.
	 * @return Staff
	 */
	public function setDesignation($designation) {
		$this->designation = Helper::assureString($designation);
		return $this;
	}

	/**
	 * Returns default greeting message when the staff user accepts a live chat request.
	 *
	 * @return string
	 * @filterBy
	 */
	public function getGreeting() {
		return $this->greeting;
	}

	/**
	 * Sets default greeting message when the staff user accepts a live chat request.
	 *
	 * @param string $greeting Default greeting message when the staff user accepts a live chat request.
	 * @return Staff
	 */
	public function setGreeting($greeting) {
		$this->greeting = Helper::assureString($greeting);
		return $this;
	}

	/**
	 * Returns signature that will be appended to each reply made by the staff user.
	 * The value is not available when the object was fetched from the server.
	 *
	 * @return string
	 */
	public function getSignature() {
		return $this->signature;
	}

	/**
	 * Sets signature that wil be appended to each reply made by the staff user.
	 *
	 * @param string $signature Signature that will be appended to each reply made by the staff user.
	 * @return Staff
	 */
	public function setSignature($signature) {
		$this->signature = Helper::assureString($signature);
		return $this;
	}

	/**
	 * Returns mobile number of the staff user.
	 *
	 * @return string
	 * @filterBy
	 */
	public function getMobileNumber() {
		return $this->mobile_number;
	}

	/**
	 * Sets mobile number of the staff user.
	 *
	 * @param string $mobile_number Mobile number of the staff user.
	 * @return Staff
	 */
	public function setMobileNumber($mobile_number) {
		$this->mobile_number = Helper::assureString($mobile_number);
		return $this;
	}

	/**
	 * Returns whether the staff user is enabled.
	 *
	 * @return bool
	 * @filterBy
	 * @orderBy
	 */
	public function getIsEnabled() {
		return $this->is_enabled;
	}

	/**
	 * Sets whether the staff user is enabled.
	 * True is the default value when creating new staff user.
	 *
	 * @param bool $is_enabled True to enable the staff user. False to disable.
	 * @return Staff
	 */
	public function setIsEnabled($is_enabled) {
		$this->is_enabled = Helper::assureBool($is_enabled);
		return $this;
	}

	/**
	 * Returns timezone of the staff user.
	 *
	 * @return string
	 */
	public function getTimezone() {
		return $this->timezone;
	}

	/**
	 * Sets timezone of the staff user.
	 *
	 * @param string $timezone Timezone of the staff user.
	 * @return Staff
	 * @filterBy
	 */
	public function setTimezone($timezone) {
		$this->timezone = Helper::assureString($timezone);
		return $this;
	}

	/**
	 * Returns whether Daylight Saving Time is enabled for the staff user.
	 *
	 * @return bool
	 * @filterBy
	 */
	public function getEnableDST() {
		return $this->enable_dst;
	}

	/**
	 * Sets whether Daylight Saving Time is enabled for the staff user.
	 * True is the default value when creating new staff user.
	 *
	 * @param bool $enable_dst True to enable Daylight Saving Time for the staff user. False to disable.
	 * @return Staff
	 */
	public function setEnableDST($enable_dst) {
		$this->enable_dst = Helper::assureBool($enable_dst);
		return $this;
	}

	/**
	 * Sets password for the staff user.
	 *
	 * @param string $password Password for the staff user.
	 * @return Staff
	 */
	public function setPassword($password) {
		$this->password = Helper::assureString($password);
		return $this;
	}

	/**
	 * Creates new staff user.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param string $first_name First name of new staff user.
	 * @param string $last_name Last name of new staff user.
	 * @param string $user_name Login username of new staff user.
	 * @param string $email E-mail address of new staff user.
	 * @param StaffGroup $staff_group Staff group of new staff user.
	 * @param string $password Password for new staff user.
	 * @return Staff
	 */
	static public function createNew($first_name, $last_name, $user_name, $email, StaffGroup $staff_group, $password) {
		$new_staff = new Staff();
		$new_staff->setFirstName($first_name);
		$new_staff->setLastName($last_name);
		$new_staff->setUserName($user_name);
		$new_staff->setEmail($email);
		$new_staff->setStaffGroup($staff_group);
		$new_staff->setPassword($password);
		return $new_staff;
	}

	/**
	 * Creates new ticket with this staff user as the author.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param Department $department Department where the ticket will be created.
	 * @param string $contents Contents of the first post.
	 * @param string $subject Subject of the ticket.
	 * @return Ticket
	 */
	public function newTicket(Department $department, $contents, $subject) {
		return Ticket::createNew($department, $this, $contents, $subject);
	}

	/**
	 * Creates a news item with this staff user as the author.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param string $subject Subject of news item.
	 * @param string $contents Contents of news item.
	 * @return NewsItem
	 */
	public function newNewsItem($subject, $contents) {
		return NewsItem::createNew($subject, $contents, $this);
	}
}