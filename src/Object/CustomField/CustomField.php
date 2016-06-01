<?php
namespace Kayako\Api\Client\Object\CustomField;

use Kayako\Api\Client\Common\Helper;
use Kayako\Api\Client\Exception\BadMethodCallException;
use Kayako\Api\Client\Exception\GeneralException;
use Kayako\Api\Client\Object\Base\CustomFieldGroupBase;
use Kayako\Api\Client\Object\Base\ObjectBase;

/**
 * Class for custom field with text value and base class for other types of custom fields.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @since Kayako version 4.40.1079
 */
class CustomField extends ObjectBase {

	/**
	 * Field identifier.
	 * @apiField
	 * @var int
	 */
	protected $id;

	/**
	 * Field type.
	 * @apiField
	 * @var int
	 */
	protected $type;

	/**
	 * Field name.
	 * @apiField
	 * @var string
	 */
	protected $name;

	/**
	 * Field title.
	 * @apiField
	 * @var string
	 */
	protected $title;

	/**
	 * Field value.
	 * @apiField name=value getter=getRawValue setter=setValue
	 * @var string
	 */
	protected $raw_value;

	/**
	 * Custom field group this field belongs to.
	 * @var CustomFieldGroupBase
	 */
	protected $custom_field_group;

	/**
	 * Cache for field definition.
	 * @var CustomFieldDefinition
	 */
	protected $definition = null;

	/**
	 * Default constructor.
	 *
	 * @param CustomFieldGroupBase $custom_field_group Custom field group this field belongs to.
	 * @param array $data Object data from XML response converted into array.
	 */
	function __construct($custom_field_group, $data = null) {
		parent::__construct($data);
		$this->custom_field_group = $custom_field_group;
	}

	/**
	 * Creates proper class based on type of custom field.
	 *
	 * @param CustomFieldGroupBase $custom_field_group Custom field group this field belongs to.
	 * @param array $data Object data from XML response.
	 * @return CustomField
	 * @throws GeneralException
	 */
	static public function createByType($custom_field_group, $data) {
		switch ($data['_attributes']['type']) {
			case CustomFieldDefinition::TYPE_TEXT:
			case CustomFieldDefinition::TYPE_TEXTAREA:
			case CustomFieldDefinition::TYPE_PASSWORD:
			case CustomFieldDefinition::TYPE_CUSTOM:
				return new CustomField($custom_field_group, $data);
			case CustomFieldDefinition::TYPE_RADIO:
			case CustomFieldDefinition::TYPE_SELECT:
				return new CustomFieldSelect($custom_field_group, $data);
			case CustomFieldDefinition::TYPE_LINKED_SELECT:
				return new CustomFieldLinkedSelect($custom_field_group, $data);
			case CustomFieldDefinition::TYPE_CHECKBOX:
			case CustomFieldDefinition::TYPE_MULTI_SELECT:
				return new CustomFieldMultiSelect($custom_field_group, $data);
			case CustomFieldDefinition::TYPE_DATE:
				return new CustomFieldDate($custom_field_group, $data);
			case CustomFieldDefinition::TYPE_FILE:
				return new CustomFieldFile($custom_field_group, $data);
		}
		throw new GeneralException("Unknown custom field type.");
	}

	protected function parseData($data) {
		$this->id = intval($data['_attributes']['id']);
		$this->name = $data['_attributes']['name'];
		$this->type = intval($data['_attributes']['type']);
		$this->title = $data['_attributes']['title'];
		$this->raw_value = $data['_contents'];
	}

	public function buildData($create) {
		$this->checkRequiredAPIFields($create);

		$data[$this->name] = $this->raw_value;

		return $data;
	}

	static public function getAll() {
		throw new BadMethodCallException(sprintf("You can't get all objects of type %s this way. Use CustomFieldGroupBase extending classes getAll method instead or relevant methods of objects extending ObjectWithCustomFieldsBase class.", get_called_class()));
	}

	static public function get() {
		throw new BadMethodCallException(sprintf("You can't get single object of type %s.", get_called_class()));
	}

	public function create() {
		throw new BadMethodCallException(sprintf("You can't create objects of type %s.", get_called_class()));
	}

	public function update() {
		throw new BadMethodCallException(sprintf("You can't update single custom fields of type %s. Use updateCustomFields method of objects extending ObjectWithCustomFieldsBase class.", get_called_class()));
	}

	public function delete() {
		throw new BadMethodCallException(sprintf("You can't delete objects of type %s.", get_called_class()));
	}

	public function refresh() {
		throw new BadMethodCallException(sprintf("You can't refresh objects of type %s.", get_called_class()));
	}

	public function __toString() {
		return sprintf("%s (id: %s, name: %s): %s\n", get_class($this), implode(', ', $this->getId(true)), $this->getName(), $this->toString());
	}

	public function toString() {
		return sprintf("%s = %s", $this->getTitle(), $this->getRawValue());
	}

	public function getId($complete = false) {
		return $complete ? array($this->id) : $this->id;
	}

	/**
	 * Returns type of this custom field - one of CustomFieldBase::TYPE constants.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Returns name of this custom field.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Returns title of this custom field.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Returns raw text value of this custom field.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getRawValue() {
		return $this->raw_value;
	}

	/**
	 * Returns value of this custom field.
	 * Method is overloaded in descendant classes and return value interpretation depends on field type.
	 *
	 * @return string
	 */
	public function getValue() {
		return $this->raw_value;
	}

	/**
	 * Sets the value of this custom field.
	 * Method is overloaded in descendant classes and value interpretation depends on field type.
	 *
	 * @param string $value Value.
	 * @return CustomField
	 */
	public function setValue($value) {
		$this->raw_value = Helper::assureString($value);
		return $this;
	}

	/**
	 * Returns field definition.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return CustomFieldDefinition
	 */
	public function getDefinition($reload = false) {
		if ($this->definition !== null && !$reload)
			return $this->definition;

		/** @noinspection PhpUndefinedMethodInspection */
		$this->definition = CustomFieldDefinition::getAll()->filterByName($this->getName())->first();
		return $this->definition;
	}

	/**
	 * Returns field option with provided identifier or value.
	 * Returns null if option was not found.
	 *
	 * @param mixed $value Identifier of option OR value of option OR option.
	 * @return CustomFieldOption
	 */
	public function getOption($value) {
		if (is_numeric($value)) { //value is option identifier
			return $this->getDefinition()->getOptionById($value);
		} elseif (is_string($value)) { //value is option value
			return $this->getDefinition()->getOptionByValue($value);
		} elseif ($value instanceof CustomFieldOption) { //value is option itself
			return $value;
		}
		return null;
	}
}