<?php
namespace Kayako\Api\Client\Object\Troubleshooter;

use Kayako\Api\Client\Common\Helper;
use Kayako\Api\Client\Common\ResultSet;
use Kayako\Api\Client\Object\Base\ObjectBase;
use Kayako\Api\Client\Object\Department\Department;
use Kayako\Api\Client\Object\Staff\Staff;
use Kayako\Api\Client\Object\Ticket\TicketPriority;
use Kayako\Api\Client\Object\Ticket\TicketType;

/**
 * Kayako TroubleshooterStep object.
 *
 * @author Saloni Dhall (https://github.com/SaloniDhall)
 * known issues SWIFT-4136, SWIFT-4138
 * @link http://wiki.kayako.com/display/DEV/REST+-+TroubleshooterStep
 * @since Kayako version 4.64.1
 */
class TroubleshooterStep extends ObjectBase {

	static protected $controller = '/Troubleshooter/Step';
	static protected $object_xml_name = 'troubleshooterstep';

	/**
	 * Troubleshooterstep status - Draft.
	 *
	 * @var int
	 */
	const STATUS_DRAFT = 1;

	/**
	 * Troubleshooterstep status - Published.
	 *
	 * @var int
	 */
	const STATUS_PUBLISHED = 2;

	/**
	 * Troubleshooterstep item identifier.
	 * @apiField
	 * @var int
	 */
	protected $id;

	/**
	 * Troubleshooterstep category id.
	 * @apiField
	 * @var int
	 */
	protected $category_id;

	/**
	 * Troubleshooterstep category.
	 * @apiField required_create=true
	 * @var int
	 */
	protected $category;

	/**
	 * Creator (staff) identifier.
	 * @apiField required_create=true
	 * @var int
	 */
	protected $staff_id;

	/**
	 * Creator (staff).
	 * @var Staff
	 */
	private $staff;

	/**
	 * Editor (staff) identifier.
	 * @apiField required_update=true
	 * @var int
	 */
	protected $edited_staff_id;

	/**
	 * Editor (staff).
	 * @var Staff
	 */
	private $edited_staff;

	/**
	 * Troubleshooterstep subject.
	 * @apiField required_create=true
	 * @var string
	 */
	protected $subject;

	/**
	 * Troubleshooterstep contents.
	 * @apiField required_create=true
	 * @var string
	 */
	protected $contents;

	/**
	 * Troubleshooterstep displayorder
	 * @apiField
	 * @var int
	 */
	protected $display_order;

	/**
	 * Troubleshooterstep allow comments.
	 * @apiField
	 * @var bool
	 */
	protected $allow_comments;

	/**
	 * Troubleshooterstep hasattachments.
	 * @apiField
	 * @var bool
	 */
	protected $has_attachments;

	/**
	 * Troubleshooterstep enable_ticket_redirection
	 * @apiField
	 * @var bool
	 */
	protected $enable_ticket_redirection;

	/**
	 * Troubleshooterstep redirect_departmentid.
	 * @apiField
	 * @var int
	 */
	protected $redirect_department_id;

	/**
	 * Troubleshooterstep typeid.
	 * @apiField
	 * @var int
	 */
	protected $ticket_type_id;

	/**
	 * Troubleshooterstep priorityid.
	 * @apiField
	 * @var int
	 */
	protected $ticket_priority_id;

	/**
	 * Troubleshooterstep ticketsubject.
	 * @apiField
	 * @var string
	 */
	protected $ticket_subject;

	/**
	 * Troubleshooterstep status.
	 * @apiField
	 * @var int
	 */
	protected $status;

	/**
	 * Troubleshooterstep parentstepidlist.
	 * @apiField name=parentstepidlist
	 * @var int[]
	 */
	protected $parent_step_ids = array();

	/**
	 * Troubleshooterstep item identifier.
	 * @apiField
	 * @var int[]
	 */
	protected $child_step_ids = array();

	/**
	 * Ticket type.
	 * @var TicketType
	 */
	private $ticket_type;

	/**
	 * Ticket priority.
	 * @var TicketPriority
	 */
	private $ticket_priority;

	/**
	 * Redirect department.
	 * @var Department
	 */
	private $redirect_department;

	/**
	 * List of attachments.
	 * @var TroubleshooterAttachment[]
	 */
	private $attachments = null;

	protected function parseData($data) {
		$this->id = Helper::assurePositiveInt($data['id']);
		$this->category_id = Helper::assurePositiveInt($data['categoryid']);
		$this->staff_id = Helper::assurePositiveInt($data['staffid']);
		$this->subject = Helper::assureString($data['subject']);
		$this->display_order = Helper::assureInt($data['displayorder']);
		$this->allow_comments = Helper::assureBool($data['allowcomments']);
		$this->has_attachments = Helper::assureBool($data['hasattachments']);
		$this->enable_ticket_redirection = Helper::assureBool($data['redirecttickets']);
		$this->redirect_department_id = Helper::assurePositiveInt($data['redirectdepartmentid']);
		$this->ticket_type_id = Helper::assurePositiveInt($data['tickettypeid']);
		$this->ticket_priority_id = Helper::assureInt($data['priorityid']);
		$this->contents = Helper::assureString($data['contents']);

		$this->parent_step_ids = array();
		$this->child_step_ids = array();

		if (is_array($data['parentsteps'])) {
			if (is_string($data['parentsteps'][0]['id'])) {
				$this->parent_step_ids[] = Helper::assurePositiveInt($data['parentsteps'][0]['id']);
			} else {
				foreach ($data['parentsteps'][0]['id'] as $stepid_list) {
					$this->parent_step_ids[] = Helper::assurePositiveInt($stepid_list);
				}
			}
		}

		if (is_array($data['childsteps'])) {
			if (is_string($data['childsteps'][0]['id'])) {
				$this->child_step_ids[] = Helper::assurePositiveInt($data['childsteps'][0]['id']);
			} else {
				foreach ($data['childsteps'][0]['id'] as $childsteps) {
					$this->child_step_ids[] = Helper::assurePositiveInt($childsteps);
				}
			}
		}

	}

	public function buildData($create) {
		$this->checkRequiredAPIFields($create);

		$data = array();

		$this->buildDataNumeric($data, 'categoryid', $this->category_id);
		$this->buildDataString($data, 'subject', $this->subject);
		$this->buildDataString($data, 'contents', $this->contents);

		if ($create) {
			$this->buildDataNumeric($data, 'staffid', $this->staff_id);
		} else {
			$this->buildDataNumeric($data, 'editedstaffid', $this->edited_staff_id);
		}

		$this->buildDataNumeric($data, 'displayorder', $this->display_order);
		$this->buildDataNumeric($data, 'stepstatus', $this->status);
		$this->buildDataBool($data, 'allowcomments', $this->allow_comments);
		$this->buildDataBool($data, 'enableticketredirection', $this->enable_ticket_redirection);

		if ($this->enable_ticket_redirection) {
			$this->buildDataNumeric($data, 'redirectdepartmentid', $this->redirect_department_id);
			$this->buildDataNumeric($data, 'tickettypeid', $this->ticket_type_id);
			$this->buildDataNumeric($data, 'ticketpriorityid', $this->ticket_priority_id);
			$this->buildDataString($data, 'ticketsubject', $this->ticket_subject);
		}

		$this->buildDataList($data, 'parentstepidlist', $this->parent_step_ids);

		return $data;
	}

	/**
	 * Fetches all troubleshooter steps from the server.
	 *
	 * @return ResultSet|TroubleshooterStep[]
	 */
	static public function getAll() {
		return parent::genericGetAll();
	}

	/**
	 * Fetches Troubleshooter step from the server by its identifier.
	 *
	 * @param int $id Troubleshooter step identifier.
	 * @return TroubleshooterStep
	 */
	static public function get($id) {
		return parent::genericGet(array($id));
	}

	public function toString() {
		return sprintf("%s (Contents : %s)", $this->getSubject(), substr($this->getContents(), 0, 50) . (strlen($this->getContents()) > 50 ? '...' : ''));
	}

	public function getId($complete = false) {
		return $complete ? array($this->id) : $this->id;
	}

	/**
	 * Returns subject of the Troubleshooterstep item.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getSubject() {
		return $this->subject;
	}

	/**
	 * Sets subject of the Troubleshooterstep item.
	 *
	 * @param string $subject Subject of the Troubleshooterstep item.
	 * @return TroubleshooterStep
	 */
	public function setSubject($subject) {
		$this->subject = Helper::assureString($subject);
		return $this;
	}

	/**
	 * Returns status of the Troubleshooterstep item.
	 *
	 * @see TroubleshooterStep::STATUS constants.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * Returns whether this Troubleshooterstep has attachments.
	 *
	 * @return bool
	 * @filterBy
	 * @orderBy
	 */
	public function getHasAttachments() {
		return $this->has_attachments;
	}

	/**
	 * Returns list of attachments.
	 *
	 * Result is cached till the end of script.
	 *
	 * @param bool $reload True to reload attachments from server.
	 * @return TroubleshooterAttachment[]
	 */
	public function getAttachments($reload = false) {
		if ($this->attachments === null || $reload) {
			$this->attachments = TroubleshooterAttachment::getAll($this->id)->getRawArray();
		}
		return new ResultSet($this->attachments);
	}

	/**
	 * Sets status of the Troubleshooterstep item.
	 *
	 * @see TroubleshooterStep::STATUS constants.
	 *
	 * @param int $status Status of the Troubleshooterstep item.
	 * @return TroubleshooterStep
	 */
	public function setStatus($status) {
		$this->status = Helper::assureConstant($status, $this, 'STATUS');
		return $this;
	}

	/**
	 * Returns contents of the Troubleshooterstep item.
	 *
	 * @return string
	 * @filterBy
	 */
	public function getContents() {
		return $this->contents;
	}

	/**
	 * Sets contents of the Troubleshooterstep item. Can contain HTML tags.
	 *
	 * @param string $contents Contents of the Troubleshooterstep item.
	 * @return TroubleshooterStep
	 */
	public function setContents($contents) {
		$this->contents = Helper::assureString($contents);
		return $this;
	}

	/**
	 * Returns display order of this Troubleshooterstep item.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getDisplayOrder() {
		return $this->display_order;
	}

	/**
	 * Sets the displayorder of this Troubleshooterstep item.
	 *
	 * @param int $displayorder
	 * @return $this
	 */
	public function setDisplayOrder($displayorder) {
		$this->display_order = Helper::assureInt($displayorder);
		return $this;
	}

	/**
	 * Returns whether clients are permitted to comment on this Troubleshooterstep item.
	 *
	 * @return bool
	 * @filterBy
	 * @orderBy
	 */
	public function getAllowComments() {
		return $this->allow_comments;
	}

	/**
	 * Sets whether clients are permitted to comment on this Troubleshooterstep item.
	 *
	 * @param bool $allow_comments True to allow clients to comment on this Troubleshooterstep item.
	 * @return TroubleshooterStep
	 */
	public function setAllowComments($allow_comments) {
		$this->allow_comments = Helper::assureBool($allow_comments);
		return $this;
	}

	/**
	 * Returns ticket type identifier.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getTicketTypeId() {
		return $this->ticket_type_id;
	}

	/**
	 * Sets ticket type identifier.
	 *
	 * @param int $ticket_type_id
	 * @return $this
	 */
	public function setTicketTypeId($ticket_type_id) {
		$this->ticket_type_id = Helper::assureInt($ticket_type_id);
		$this->ticket_type = null;
		return $this;
	}

	/**
	 * Returns this ticket type.
	 *
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return TicketType
	 */
	public function getTicketType($reload = false) {
		if ($this->ticket_type !== null && !$reload)
			return $this->ticket_type;

		if ($this->ticket_type_id === null)
			return null;

		$this->ticket_type = TicketType::get($this->ticket_type_id);
		return $this->ticket_type;
	}

	/**
	 * Sets this ticket type.
	 *
	 * @param TicketType $ticket_type Ticket type.
	 * @return TroubleshooterStep
	 */
	public function setTicketType($ticket_type) {
		$this->ticket_type = Helper::assureObject($ticket_type, TicketType::class);
		$this->ticket_type_id = $this->ticket_type !== null ? $this->ticket_type->getId() : null;
		return $this;
	}

	/**
	 * Returns ticket priority identifier.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getTicketPriorityId() {
		return $this->ticket_priority_id;
	}

	/**
	 * Sets ticket priority identifier.
	 *
	 * @param int $ticket_priority_id Ticket priority identifier.
	 * @return TroubleshooterStep
	 */
	public function setTicketPriorityId($ticket_priority_id) {
		$this->ticket_priority_id = Helper::assureInt($ticket_priority_id);
		return $this;
	}

	/**
	 * Returns ticket priority.
	 *
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return TicketPriority
	 */
	public function getTicketPriority($reload = false) {
		if ($this->ticket_priority !== null && !$reload)
			return $this->ticket_priority;

		if ($this->ticket_priority_id === null)
			return null;

		$this->ticket_priority = TicketPriority::get($this->ticket_priority_id);
		return $this->ticket_priority;
	}

	/**
	 * Sets ticket priority.
	 *
	 * @param TicketPriority $ticket_priority
	 * @return TroubleshooterStep
	 */
	public function setTicketPriority($ticket_priority) {
		$this->ticket_priority = Helper::assureObject($ticket_priority, TicketPriority::class);
		$this->ticket_priority_id = $this->ticket_priority !== null ? $this->ticket_priority->getId() : null;
		return $this;
	}

	/**
	 * Returns ticket subject.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getTicketSubject() {
		return $this->ticket_subject;
	}

	/**
	 * Sets ticket subject.
	 *
	 * @param string $ticket_subject Subject of the ticket.
	 * @return TroubleshooterStep
	 */
	public function setTicketSubject($ticket_subject) {
		$this->ticket_subject = Helper::assureString($ticket_subject);
		return $this;
	}

	/**
	 * Returns the staff user, the creator of this Troubleshooterstep item.
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
	 * Sets staff user, the creator of this Troubleshooterstep item.
	 *
	 * @param Staff $staff Staff user.
	 * @return TroubleshooterStep
	 */
	public function setStaff($staff) {
		$this->staff = Helper::assureObject($staff, Staff::class);
		$this->staff_id = $this->staff !== null ? $this->staff->getId() : null;
		return $this;
	}

	/**
	 * Sets the category of the Troubleshooterstep
	 *
	 * @param TroubleshooterCategory $categoryid category.
	 * @return TroubleshooterCategory
	 */
	public function setCategory($categoryid) {
		$this->category = Helper::assureObject($categoryid, TroubleshooterCategory::class);
		$this->category_id = $this->category !== null ? $this->category->getId() : null;
		return $this;
	}

	/**
	 * Returns whether to allow ticket redirection.
	 * @return bool
	 */
	public function isEnableTicketRedirection() {
		return $this->enable_ticket_redirection;
	}

	/**
	 * Sets whether to allow ticket redirection.
	 *
	 * @param bool $enable_ticket_redirection True to allow ticket redirection.
	 * @return TroubleshooterStep
	 */
	public function setEnableTicketRedirection($enable_ticket_redirection) {
		$this->enable_ticket_redirection = Helper::assureBool($enable_ticket_redirection);
		return $this;
	}

	/**
	 * Returns redirect department identifier.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getRedirectDepartmentId() {
		return $this->redirect_department_id;
	}

	/**
	 * Sets redirect department identifier.
	 *
	 * @param int $redirect_department_id Redirect department identifier.
	 * @return TroubleshooterStep
	 */
	public function setRedirectDepartmentId($redirect_department_id) {
		$this->redirect_department_id = Helper::assureString($redirect_department_id);
		return $this;
	}

	/**
	 * Returns redirect department.
	 *
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return Department
	 */
	public function getRedirectDepartment($reload = false) {
		if ($this->redirect_department !== null && !$reload)
			return $this->redirect_department;

		if ($this->redirect_department_id === null)
			return null;

		$this->redirect_department = Department::get($this->redirect_department_id);
		return $this->redirect_department;
	}

	/**
	 * Sets redirect department.
	 *
	 * @param Department $redirect_department Redirect department.
	 * @return TroubleshooterStep
	 */
	public function setRedirectDepartment($redirect_department) {
		$this->redirect_department = Helper::assureObject($redirect_department, Department::class);
		$this->redirect_department_id = $this->redirect_department !== null ? $this->redirect_department->getId() : null;
		return $this;
	}

	/**
	 * Returns parent stepid list which are linked to this troubleshooterstep item.
	 *
	 * @return array
	 * @filterBy
	 */
	public function getParentStepIds() {
		return $this->parent_step_ids;
	}

	/**
	 * Sets parentstepidlist (using their identifiers) that this troubleshooterstep item will be visible to.
	 *
	 * @param int[] $parent_stepids Identifiers of parent_stepids that this troubleshooterstep item will be visible to.
	 * @return TroubleshooterStep
	 */
	public function setParentStepIds($parent_stepids) {
		//normalization to array
		if (!is_array($parent_stepids)) {
			if (is_numeric($parent_stepids)) {
				$parent_stepids = array($parent_stepids);
			} else {
				$parent_stepids = array();
			}
		}

		//normalization to positive integer values
		$this->parent_step_ids = array();
		foreach ($parent_stepids as $parent_step_id) {
			$parent_step_id = Helper::assurePositiveInt($parent_step_id);
			if ($parent_step_id === null)
				continue;

			$this->parent_step_ids[] = $parent_step_id;
		}

		return $this;
	}

	/**
	 * Returns child step ids which are linked to this troubleshooterstep item.
	 *
	 * @return int[]
	 */
	public function getChildStepIds() {
		return $this->child_step_ids;
	}

	/**
	 * Sets identifier of staff user, the editor of this troubleshooterstep item update.
	 *
	 * @param int $staff_id Staff user identifier.
	 * @return TroubleshooterStep
	 */
	public function setEditedStaffId($staff_id) {
		$this->edited_staff_id = Helper::assurePositiveInt($staff_id);
		$this->edited_staff = null;
		return $this;
	}

	/**
	 * Sets staff user, the editor of this troubleshooterstep item update.
	 *
	 * @param Staff $staff Staff user.
	 * @return TroubleshooterStep
	 */
	public function setEditedStaff($staff) {
		$this->edited_staff = Helper::assureObject($staff, Staff::class);
		$this->edited_staff_id = $this->edited_staff !== null ? $this->edited_staff->getId() : null;
		return $this;
	}

	/**
	 * Creates a troubleshooterstep item.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param TroubleshooterCategory $category Category of troubleshooterstep item.
	 * @param string $subject Subject of troubleshooterstep item.
	 * @param string $contents Contents of troubleshooterstep item.
	 * @param Staff $staff Author (staff) of troubleshooterstep item.
	 *
	 * @return TroubleshooterStep
	 */
	static public function createNew($category, $subject, $contents, Staff $staff) {
		$new_troubleshooterstep_item = new TroubleshooterStep();
		$new_troubleshooterstep_item->setCategory($category);
		$new_troubleshooterstep_item->setSubject($subject);
		$new_troubleshooterstep_item->setContents($contents);
		$new_troubleshooterstep_item->setStaff($staff);
		return $new_troubleshooterstep_item;
	}
}