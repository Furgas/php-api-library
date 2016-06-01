<?php
namespace Kayako\Api\Client\Object\Base;

use Kayako\Api\Client\Common\ResultSet;
use Kayako\Api\Client\Object\Staff\Staff;
use Kayako\Api\Client\Object\User\User;

/**
 * Base class for Kayako objects which can be commented.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @since Kayako version 4.51.1891
 */
abstract class CommentableBase extends ObjectBase {

	/**
	 * Name of class representing comments for this object.
	 * @var string
	 */
	static protected $comment_class = null;

	/**
	 * Comments for this object.
	 * @var CommentBase[]
	 */
	protected $comments;

	/**
	 *
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return CommentBase[]|ResultSet
	 */
	public function getComments($reload = false) {
		if ($this->comments !== null && !$reload)
			return $this->comments;

		$id = $this->getId();
		if ($id === null)
			return null;

		$classname = static::$comment_class;
		/** @noinspection PhpUndefinedMethodInspection */
		$this->comments = $classname::getAll($id);
		return new ResultSet($this->comments);
	}

	/**
	 * Creates a new comment for this object.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param User|Staff|string $creator Creator (staff object, user object or user fullname) of this comment.
	 * @param string $contents Contents of this comment.
	 * @return CommentBase
	 */
	public function newComment($creator, $contents) {
		$classname = static::$comment_class;
		/** @noinspection PhpUndefinedMethodInspection */
		return $classname::createNew($this, $creator, $contents);
	}
}