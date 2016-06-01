<?php
namespace Kayako\Api\Client\Object\Troubleshooter;

use Kayako\Api\Client\Common\Helper;
use Kayako\Api\Client\Common\ResultSet;
use Kayako\Api\Client\Object\Base\CommentBase;
use Kayako\Api\Client\Object\Staff\Staff;
use Kayako\Api\Client\Object\User\User;

/**
 * Kayako TroubleshooterComment object.
 *
 * @author Saloni Dhall (https://github.com/SaloniDhall)
 * @link http://wiki.kayako.com/display/DEV/REST+-+TroubleshooterComment
 * @since Kayako version 4.64.1
 */
class TroubleshooterComment extends CommentBase {

	static protected $controller = '/Troubleshooter/Comment';
	static protected $object_xml_name = 'troubleshooterstepcomment';

	/**
	 * TroubleshooterStep object identifier.
	 * @apiField required_create=true
	 * @var int
	 */
	protected $troubleshooter_step_id;

	/**
	 * TroubleshooterStep object.
	 * @var TroubleshooterStep
	 */
	protected $troubleshooter_step;

	protected function parseData($data) {
		parent::parseData($data);
		$this->troubleshooter_step_id = Helper::assurePositiveInt($data['troubleshooterstepid']);
	}

	public function buildData($create) {
		$data = parent::buildData($create);

		$this->buildDataNumeric($data, 'troubleshooterstepid', $this->troubleshooter_step_id);

		return $data;
	}

	/**
	 * Fetches all comments of troubleshooter step from the server.
	 *
	 * @param TroubleshooterStep|int $troubleshooter_step Troubleshooter step object or troubleshooter step identifier.
	 * @return ResultSet|TroubleshooterComment[]
	 */
	static public function getAll($troubleshooter_step) {
		if ($troubleshooter_step instanceof TroubleshooterStep) {
			$troubleshooter_step_id = $troubleshooter_step->getId();
		} else {
			$troubleshooter_step_id = $troubleshooter_step;
		}

		$search_parameters = array('ListAll');

		$search_parameters[] = $troubleshooter_step_id;

		return parent::genericGetAll($search_parameters);
	}

	/**
	 * Return TroubleshooterStep object identifier.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getTroubelshooterStepId() {
		return $this->troubleshooter_step_id;
	}

	/**
	 * Sets the troubleshooterstep Id.
	 *
	 * @param int $troubleshooter_step_id TroubleshooterStep identifier
	 *
	 * @return $this
	 */
	public function setTroubelshooterStepId($troubleshooter_step_id) {
		$this->troubleshooter_step_id = Helper::assurePositiveInt($troubleshooter_step_id);
		$this->troubleshooter_step = null;
		return $this;
	}

	/**
	 * Return troubleshooter object.
	 *
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return TroubleshooterStep
	 */
	public function getTroubleshooterStep($reload = false) {
		if ($this->troubleshooter_step !== null && !$reload)
			return $this->troubleshooter_step;

		if ($this->troubleshooter_step_id === null)
			return null;

		$this->troubleshooter_step = TroubleshooterStep::get($this->troubleshooter_step_id);
		return $this->troubleshooter_step;
	}

	/**
	 * Sets the TroubleshooterStep object.
	 *
	 * @param TroubleshooterStep $troubleshooter_step TroubleshooterStep object
	 * @return TroubleshooterStep
	 */
	public function setTroubleshooterStep($troubleshooter_step) {
		$this->troubleshooter_step = Helper::assureObject($troubleshooter_step, TroubleshooterStep::class);
		$this->troubleshooter_step_id = $this->troubleshooter_step !== null ? $this->troubleshooter_step->getId() : null;
		return $this;
	}

	/**
	 * Creates a new troubelshooterstep comment.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param TroubleshooterStep $troubleshooter_step TroubleshooterStep object.
	 * @param User|Staff|string $creator Creator (staff object, user object or user fullname) of this comment.
	 * @param string $contents Contents of this comment.
	 * @return TroubleshooterComment
	 */
	static public function createNew($troubleshooter_step, $creator, $contents) {
		/** @var $new_comment TroubleshooterComment */
		$new_comment = parent::createNew($creator, $contents);
		$new_comment->setTroubleshooterStep($troubleshooter_step);
		return $new_comment;
	}
}