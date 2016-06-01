<?php
namespace Kayako\Api\Client\Object\News;

use Kayako\Api\Client\Common\Helper;
use Kayako\Api\Client\Common\ResultSet;
use Kayako\Api\Client\Object\Base\CommentBase;
use Kayako\Api\Client\Object\Staff\Staff;
use Kayako\Api\Client\Object\User\User;

/**
 * Kayako NewsComment object.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @link http://wiki.kayako.com/display/DEV/REST+-+NewsComment
 * @since Kayako version 4.51.1891
 */
class NewsComment extends CommentBase {

	static protected $controller = '/News/Comment';
	static protected $object_xml_name = 'newsitemcomment';

	/**
	 * News item identifier.
	 * @apiField required_create=true
	 * @var int
	 */
	protected $news_item_id;

	/**
	 * News item.
	 * @var NewsItem
	 */
	protected $news_item;

	protected function parseData($data) {
		parent::parseData($data);
		$this->news_item_id = Helper::assurePositiveInt($data['newsitemid']);
	}

	public function buildData($create) {
		$data = parent::buildData($create);

		$this->buildDataNumeric($data, 'newsitemid', $this->news_item_id);

		return $data;
	}

	/**
	 * Fetches all comments of news item from the server.
	 *
	 * @param NewsItem|int $news_item News item object or news item identifier.
	 * @return ResultSet|NewsComment[]
	 */
	static public function getAll($news_item) {
		if ($news_item instanceof NewsItem) {
			$news_item_id = $news_item->getId();
		} else {
			$news_item_id = $news_item;
		}

		$search_parameters = array('ListAll');

		$search_parameters[] = $news_item_id;

		return parent::genericGetAll($search_parameters);
	}

	/**
	 * Return news item identifier.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getNewsItemId() {
		return $this->news_item_id;
	}

	/**
	 * Sets news item identifier.
	 *
	 * @param int $news_item_id News item identifier.
	 * @return NewsComment
	 */
	public function setNewsItemId($news_item_id) {
		$this->news_item_id = Helper::assurePositiveInt($news_item_id);
		$this->news_item = null;
		return $this;
	}

	/**
	 * Return news item.
	 *
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return NewsItem
	 */
	public function getNewsItem($reload = false) {
		if ($this->news_item !== null && !$reload)
			return $this->news_item;

		if ($this->news_item_id === null)
			return null;

		$this->news_item = NewsItem::get($this->news_item_id);
		return $this->news_item;
	}

	/**
	 * Sets news item.
	 *
	 * @param NewsItem $news_item News item.
	 * @return NewsComment
	 */
	public function setNewsItem($news_item) {
		$this->news_item = Helper::assureObject($news_item, NewsItem::class);
		$this->news_item_id = $this->news_item !== null ? $this->news_item->getId() : null;
		return $this;
	}

	/**
	 * Creates a new news item comment.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param NewsItem $news_item News item.
	 * @param User|Staff|string $creator Creator (staff object, user object or user fullname) of this comment.
	 * @param string $contents Contents of this comment.
	 * @return NewsComment
	 */
	static public function createNew($news_item, $creator, $contents) {
		/** @var $new_comment NewsComment */
		$new_comment = parent::createNew($creator, $contents);
		$new_comment->setNewsItem($news_item);
		return $new_comment;
	}
}