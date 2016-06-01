<?php
namespace Kayako\Api\Client\Common;

use Psr\Log\AbstractLogger;

/**
 * Logs all messages using PHP error_log function.
 */
class ErrorLogLogger extends AbstractLogger {

	public function log($level, $message, array $context = array()) {
		error_log($message);
	}
}