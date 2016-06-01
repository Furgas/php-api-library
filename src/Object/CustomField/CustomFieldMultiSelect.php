<?php
namespace Kayako\Api\Client\Object\CustomField;

use Kayako\Api\Client\Common\Helper;
use Kayako\Api\Client\Common\ResultSet;

/**
 * Class for select custom field with multiple options.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @since Kayako version 4.40.1079
 */
class CustomFieldMultiSelect extends CustomField {

	/**
	 * Separator of field selected values.
	 * @var string
	 */
	const VALUES_SEPARATOR = ', ';

	/**
	 * List of selected field options.
	 * @var CustomFieldOption[]
	 */
	protected $options;

	protected function parseData($data) {
		parent::parseData($data);

		$values = explode(self::VALUES_SEPARATOR, $data['_contents']);

		$this->options = array();
		foreach ($values as $value) {
			$field_option = $this->getOption($value);
			if ($field_option === null)
				continue;

			$this->options[] = $field_option;
		}
	}

	public function buildData($create) {
		$this->checkRequiredAPIFields($create);

		$data = array();

		foreach ($this->options as $key => $option) {
			/* @var $option CustomFieldOption */
			$data[sprintf('%s[%d]', $this->name, $key)] = $option->getId();
		}

		return $data;
	}

	/**
	 * Returns list of selected options of this custom field.
	 *
	 * @return ResultSet
	 */
	public function getSelectedOptions() {
		return new ResultSet($this->options);
	}

	/**
	 * Sets selected options of this custom field.
	 *
	 * @param CustomFieldOption[] $options List of options.
	 * @return CustomFieldMultiSelect
	 */
	public function setSelectedOptions($options) {
		//make sure it's array
		if (!is_array($options)) {
			if ($options === null) {
				$options = array();
			} else {
				$options = array($options);
			}
		}

		//check for proper class and eliminate duplicates
		$options_ids = array();
		$this->options = array();
		foreach ($options as $option) {
			$option = Helper::assureObject($option, CustomFieldOption::class);
			if ($option !== null && !in_array($option->getId(), $options_ids)) {
				$this->options[] = $option;
				$options_ids[] = $option->getId();
			}
		}

		//update raw value
		$option_values = array();
		foreach ($this->options as $field_option) {
			$option_values[] = $field_option->getValue();
		}
		$this->raw_value = implode(self::VALUES_SEPARATOR, $option_values);
	}

	/**
	 * Returns list of selected options of this custom field.
	 *
	 * @see CustomField::getValue()
	 * @see CustomFieldMultiSelect::getSelectedOptions()
	 *
	 * @return CustomFieldOption[]
	 */
	public function getValue() {
		return $this->options;
	}

	/**
	 * Returns selected options values as array:
	 * array(
	 *    <field option id> => '<field option value>',
	 *    ...
	 * )
	 *
	 * @return array
	 */
	public function getValues() {
		$values = array();
		foreach ($this->options as $field_option) {
			/* @var $field_option CustomFieldOption */
			$values[$field_option->getId()] = $field_option->getValue();
		}
		return $values;
	}

	/**
	 * Sets selected options of this custom field.
	 *
	 * @param array $value List of values where each value can be: identifier of field option OR value of field option OR an option.
	 * @return CustomFieldMultiSelect
	 */
	public function setValue($value) {
		//make sure it's array
		if (!is_array($value)) {
			if ($value === null) {
				$values = array();
			} else {
				$values = array($value);
			}
		} else {
			$values = $value;
		}

		//build list of CustomFieldOption objects
		$options = array();
		foreach ($values as $value) {
			$field_option = $this->getOption($value);
			if ($field_option === null)
				continue;

			$options[] = $field_option;
		}

		//set selected options
		$this->setSelectedOptions($options);

		return $this;
	}
}