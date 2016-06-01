<?php
namespace Kayako\Api\Client\Object\Ticket;

use Kayako\Api\Client\Common\ResultSet;
use Kayako\Api\Client\Object\Base\CustomFieldGroupBase;

/**
 * Kayako TicketCustomField object.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @link http://wiki.kayako.com/display/DEV/REST+-+TicketCustomField
 * @since Kayako version 4.01.220
 */
class TicketCustomFieldGroup extends CustomFieldGroupBase {

	static protected $controller = '/Tickets/TicketCustomField';

	/**
	 * Ticket identifier.
	 * @var int
	 */
	protected $ticket_id;

	/**
	 * Constructor.
	 *
	 * @param int $ticket_id Ticket identifier.
	 * @param array|null $data Object data from XML response converted into array.
	 */
	function __construct($ticket_id, $data = null) {
		parent::__construct($data);
		$this->type = CustomFieldGroupBase::TYPE_TICKET;
		$this->ticket_id = $ticket_id;
	}

	/**
	 * Fetches ticket custom fields groups from server.
	 *
	 * @param Ticket|int $ticket Ticket object or ticket identifier.
	 * @return ResultSet|TicketCustomFieldGroup[]
	 */
	static public function getAll($ticket) {
		if ($ticket instanceof Ticket) {
			$ticket_id = $ticket->getId();
		} else {
			$ticket_id = $ticket;
		}

		$result = self::getRESTClient()->get(static::$controller, array($ticket_id));
		$objects = array();
		if (array_key_exists(static::$object_xml_name, $result)) {
			foreach ($result[static::$object_xml_name] as $object_data) {
				$objects[] = new static($ticket_id, $object_data);
			}
		}
		return new ResultSet($objects);
	}

	/**
	 * Returns identifier of the ticket that this group is associated with.
	 *
	 * @return int
	 */
	public function getTicketId() {
		return $this->ticket_id;
	}
}