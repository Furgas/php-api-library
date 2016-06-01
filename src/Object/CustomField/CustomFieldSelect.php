<?php
namespace Kayako\Api\Client\Object\CustomField;

use Kayako\Api\Client\Common\Helper;

/**
 * Class for select custom field with single option.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @since Kayako version 4.40.1079
 */
class CustomFieldSelect extends CustomField {

	/**
	 * Selected option.
	 * @var CustomFieldOption
	 */
	protected $option;

	protected function parseData($data) {
		parent::parseData($data);
		$this->option = $this->getOption($data['_contents']);
	}

	public function buildData($create) {
		$this->checkRequiredAPIFields($create);

		$data = array();

		if ($this->option !== null) {
			$data[$this->name] = $this->option->getId();
		}

		return $data;
	}

	/**
	 * Sets the field selected option.
	 *
	 * @param CustomFieldOption $option Field option.
	 * @return CustomFieldSelect
	 */
	public function setSelectedOption($option) {
		$this->option = Helper::assureObject($option, CustomFieldOption::class);

		$this->raw_value = $this->option !== null ? $this->option->getValue() : null;
		return $this;
	}

	/**
	 * Returns selected option for this field.
	 *
	 * @return CustomFieldOption
	 */
	public function getSelectedOption() {
		return $this->option;
	}

	/**
	 * Returns selected field option.
	 *
	 * @see CustomField::getValue()
	 * @see CustomFieldSelect::getSelectedOption()
	 *
	 * @return CustomFieldOption
	 */
	public function getValue() {
		return $this->option;
	}

	/**
	 * Sets the option for this field.
	 *
	 * @see CustomField::setValue()
	 * @see CustomField::setSelectedOption()
	 *
	 * @param mixed $value Identifier of field option OR value of field option OR an option.
	 * @return CustomFieldSelect
	 */
	public function setValue($value) {
		$this->setSelectedOption($this->getOption($value));
		return $this;
	}
}