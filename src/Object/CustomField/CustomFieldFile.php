<?php
namespace Kayako\Api\Client\Object\CustomField;

use Kayako\Api\Client\Common\Helper;
use Kayako\Api\Client\Exception\GeneralException;
use Kayako\Api\Client\Object\Base\ObjectBase;

/**
 * Class for file custom field.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @since Kayako version 4.40.1079
 */
class CustomFieldFile extends CustomField {

	/**
	 * File name.
	 * @apiField
	 * @var string
	 */
	protected $file_name;

	/**
	 * File contents.
	 * @var string
	 */
	protected $contents;

	/**
	 * Was the file changed after fetching or creating.
	 * @var bool
	 */
	protected $is_changed = false;

	protected function parseData($data) {
		parent::parseData($data);
		if (array_key_exists('filename', $data['_attributes'])) {
			$this->file_name = $data['_attributes']['filename'];
		}
		$this->contents = base64_decode($data['_contents']);
	}

	public function buildData($create) {
		$this->checkRequiredAPIFields($create);

		$data = array();

		if ($this->is_changed) {
			$data[ObjectBase::FILES_DATA_NAME][$this->getName()] = array('file_name' => $this->file_name, 'contents' => $this->contents);
		}

		return $data;
	}

	public function toString() {
		return sprintf("%s = %s", $this->getTitle(), $this->getFileName());
	}

	/**
	 * Returns name of the file.
	 *
	 * @return string
	 */
	public function getFileName() {
		return $this->file_name;
	}

	/**
	 * Sets name of the file.
	 *
	 * @param string $file_name File name.
	 * @return CustomFieldFile
	 */
	public function setFileName($file_name) {
		$file_name = Helper::assureString($file_name);

		if ($this->file_name !== $file_name) {
			$this->is_changed = true;
		}

		$this->file_name = $file_name;

		return $this;
	}

	/**
	 * Returns raw contents of the file (NOT base64 encoded).
	 *
	 * @return string
	 */
	public function getContents() {
		return $this->contents;
	}

	/**
	 * Sets raw contents of the attachment (NOT base64 encoded).
	 *
	 * @param string $contents Raw contents of the attachment (NOT base64 encoded).
	 * @return CustomFieldFile
	 */
	public function setContents(&$contents) {
		if (md5($this->contents) !== md5($contents)) {
			$this->is_changed = true;
		}
		$this->contents =& $contents;
		return $this;
	}

	/**
	 * Sets contents of the attachment and file name by reading it from a physical file.
	 *
	 * @param string $file_path Path to file.
	 * @param string $file_name Optional. Use to set file name other than physical file.
	 * @throws GeneralException
	 * @return CustomFieldFile
	 */
	public function setContentsFromFile($file_path, $file_name = null) {
		$contents = file_get_contents($file_path);
		if ($contents === false)
			throw new GeneralException(sprintf("Error reading contents of %s.", $file_path));

		$this->setContents($contents);

		if ($file_name === null)
			$file_name = basename($file_path);
		$this->setFileName($file_name);

		$this->raw_value = base64_encode($contents);

		return $this;
	}

	/**
	 * Returns file name and contents as
	 * array(
	 *    '<file name>',
	 *    '<file contents>'
	 * )
	 * @see CustomField::getValue()
	 *
	 * @return array
	 */
	public function getValue() {
		return array($this->file_name, &$this->contents);
	}

	/**
	 * Sets contents of the attachment and file name by reading it from a physical file.
	 * @see CustomField::setValue()
	 * @see CustomFieldFile::setContentsFromFile()
	 *
	 * @param string $value Path to file.
	 * @return CustomFieldFile
	 */
	public function setValue($value) {
		$this->setContentsFromFile($value);
	}
}