<?php
/**
 * Helper functions and classes.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 */

namespace Kayako\Api\Client\Common;

use Kayako\Api\Client\Exception\GeneralException;
use Kayako\Api\Client\Object\CustomField\CustomFieldDefinition;

class Helper {

	/**
	 * Transforms XML data to array.
	 *
	 * @param string $xml XML data.
	 * @param string[] $namespaces List of namespaces to include in parsing or empty to include all namespaces.
	 * @return array
	 */
	static public function convertXmlToArray($xml, $namespaces = null) {
		$iter = 0;
		$arr = array();

		if (is_string($xml))
			$xml = new \SimpleXMLElement($xml);

		if (!($xml instanceof \SimpleXMLElement))
			return $arr;

		if ($namespaces === null)
			$namespaces = $xml->getDocNamespaces(true);

		foreach ($xml->attributes() as $attributeName => $attributeValue) {
			$arr["_attributes"][$attributeName] = trim($attributeValue);
		}
		foreach ($namespaces as $namespace_prefix => $namespace_name) {
			foreach ($xml->attributes($namespace_prefix, true) as $attributeName => $attributeValue) {
				$arr["_attributes"][$namespace_prefix . ':' . $attributeName] = trim($attributeValue);
			}
		}

		$has_children = false;

		foreach ($xml->children() as $element) {
			/** @var $element \SimpleXMLElement */

			$has_children = true;

			$elementName = $element->getName();

			if ($element->children()) {
				$arr[$elementName][] = self::convertXmlToArray($element, $namespaces);
			} else {
				$shouldCreateArray = array_key_exists($elementName, $arr) && !is_array($arr[$elementName]);

				if ($shouldCreateArray) {
					$arr[$elementName] = array($arr[$elementName]);
				}

				$shouldAddValueToArray = array_key_exists($elementName, $arr) && is_array($arr[$elementName]);

				if ($shouldAddValueToArray) {
					$arr[$elementName][] = trim($element[0]);
				} else {
					$arr[$elementName] = trim($element[0]);
				}

			}

			$iter++;
		}

		if (!$has_children) {
			$arr['_contents'] = trim($xml[0]);
		}

		return $arr;
	}

	/**
	 * Outputs seconds in hh:mm:ss format.
	 *
	 * @param int $seconds Seconds.
	 * @return string
	 */
	static public function formatSeconds($seconds) {
		if (!is_numeric($seconds))
			return $seconds;

		$minus = $seconds < 0 ? "-" : "";
		$seconds = abs($seconds);

		$formatted_seconds = str_pad(($seconds % 60), 2, "0", STR_PAD_LEFT);
		$minutes = floor($seconds / 60);
		$formatted_minutes = str_pad(($minutes % 60), 2, "0", STR_PAD_LEFT);
		$formatted_hours = str_pad(floor($minutes / 60), 2, "0", STR_PAD_LEFT);
		return sprintf("%s%s:%s:%s", $minus, $formatted_hours, $formatted_minutes, $formatted_seconds);
	}

	/**
	 * Outputs formatted bytes.
	 *
	 * @param int $bytes Bytes.
	 * @return string
	 */
	static public function formatBytes($bytes) {
		$unim = array("B", "KB", "MB", "GB", "TB", "PB");
		$c = 0;
		while ($bytes >= 1024) {
			$c++;
			$bytes = $bytes / 1024;
		}
		return number_format($bytes, ($c ? 2 : 0), ",", ".") . " " . $unim[$c];
	}

	/**
	 * Returns field value from POST data.
	 *
	 * @param CustomFieldDefinition $custom_field_definition Custom field definition.
	 * @throws GeneralException
	 * @return mixed Field value.
	 */
	static public function getPostValue($custom_field_definition) {

		$field_name = $custom_field_definition->getName();
		$required = $custom_field_definition->getIsRequired();
		$regexp = $custom_field_definition->getRegexpValidate();
		$as_array = $custom_field_definition->getType() === CustomFieldDefinition::TYPE_CHECKBOX || $custom_field_definition->getType() === CustomFieldDefinition::TYPE_MULTI_SELECT;

		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			return null;
		}

		if (!array_key_exists($field_name, $_POST)) {
			if ($required)
				throw new GeneralException("Field '%s' is required.", $custom_field_definition->getTitle());

			return null;
		}

		if ($as_array) {
			$value = $_POST[$field_name];
			if (!is_array($value)) {
				if (strlen(trim($value)) > 0) {
					$value = array($value);
				} else {
					$value = array();
				}
			}

			if ($required && count($value) === 0)
				throw new GeneralException("Field '%s' is required.", $custom_field_definition->getTitle());
		} else {
			$value = trim($_POST[$field_name]);

			if ($required && strlen($value) === 0)
				throw new GeneralException("Field '%s' is required.", $custom_field_definition->getTitle());

			if (strlen($regexp) > 0 && !preg_match($regexp, $value))
				throw new GeneralException("Error validating field '%s'.", $custom_field_definition->getTitle());
		}

		return $value;
	}

	/**
	 * Returns custom PHPDoc tag parameter list.
	 *
	 * Custom PHPDoc used by this library tags have following format:
	 * @tagName parameter1=value parameter2=value ...
	 *
	 * @param string $comment PHPDoc block.
	 * @param string $tag_name Custom tag name.
	 * @return bool|array List of parameters (may be empty if tag is parameter-less) or false when tag was not found.
	 */
	static public function getTagParameters($comment, $tag_name) {
		$tag_pos = stripos($comment, '@' . $tag_name);
		if ($tag_pos === false)
			return false;

		$tag_end_pos = stripos($comment, "\n", $tag_pos);
		$tag = trim(substr($comment, $tag_pos, $tag_end_pos - $tag_pos));
		if (strlen($tag) === 0)
			return false;

		$parameter_pairs = explode(' ', $tag);
		$parameters = array();
		foreach ($parameter_pairs as $parameter_pair) {
			if (stripos($parameter_pair, '=') === false)
				continue;

			list($name, $value) = explode('=', $parameter_pair);
			if (array_key_exists($name, $parameters)) {
				if (!is_array($parameters[$name])) {
					$parameters[$name] = array($parameters[$name]);
				}
				$parameters[$name][] = $value;
				$parameters[$name] = array_unique($parameters[$name]);
				if (count($parameters[$name]) === 1) {
					$parameters[$name] = reset($parameters[$name]);
				}
			} else {
				$parameters[$name] = $value;
			}
		}

		return $parameters;
	}

	/**
	 * Returns specified value as string.
	 *
	 * @param mixed $value Value.
	 * @param string|null $value_on_null What to return if value is null.
	 * @return string|null
	 */
	static public function assureString($value, $value_on_null = null) {
		return $value !== null ? strval($value) : $value_on_null;
	}

	/**
	 * Returns specified value as int.
	 *
	 * @param mixed $value Value.
	 * @param int|null $value_on_null What to return if value is null.
	 * @return int|null
	 */
	static public function assureInt($value, $value_on_null = null) {
		return $value !== null ? intval($value) : $value_on_null;
	}

	/**
	 * Returns specified value as positive int.
	 *
	 * @param mixed $value Value.
	 * @param int|null $value_on_non_positive What to return if value non-positive (including null).
	 * @return int|null
	 */
	static public function assurePositiveInt($value, $value_on_non_positive = null) {
		return intval($value) > 0 ? intval($value) : $value_on_non_positive;
	}

	/**
	 * Returns specified value as bool.
	 *
	 * @param mixed $value Value.
	 * @return bool
	 */
	static public function assureBool($value) {
		return $value ? true : false;
	}

	/**
	 * Returns specified value as array.
	 *
	 * @param mixed $value Value.
	 * @param mixed $value_on_null What to return if value is null.
	 * @return mixed
	 */
	static public function assureArray($value, $value_on_null = array()) {
		if (is_array($value))
			return $value;

		return $value !== null ? array($value) : $value_on_null;
	}

	/**
	 * Returns specified object only if it is an instance of specified class.
	 *
	 * @param object $object Object.
	 * @param string $class_name Class name to check for.
	 * @param mixed $value_on_invalid_object What to return if object is not an instance os specified class.
	 * @return mixed
	 */
	static public function assureObject($object, $class_name, $value_on_invalid_object = null) {
		return $object instanceof $class_name ? $object : $value_on_invalid_object;
	}

	/**
	 * Assures that specified value is proper constant value.
	 *
	 * @param mixed $value Value.
	 * @param string|object $object_or_class_name Object or class name with constants.
	 * @param string $constant_prefix Constants prefix (without last '_').
	 * @param mixed $value_on_invalid_constant What to return if value is not a valid constant.
	 * @return mixed
	 */
	static public function assureConstant($value, $object_or_class_name, $constant_prefix, $value_on_invalid_constant = null) {
		if (is_string($object_or_class_name)) {
			$class_name = $object_or_class_name;
		} elseif (is_object($object_or_class_name)) {
			$class_name = get_class($object_or_class_name);
		} else {
			return $value_on_invalid_constant;
		}

		$class = new \ReflectionClass($class_name);
		foreach ($class->getConstants() as $constant_name => $constant_value) {
			if (stripos($constant_name, $constant_prefix . '_') !== 0)
				continue;

			if ($value == $constant_value)
				return $constant_value;
		}
		return $value_on_invalid_constant;
	}
}