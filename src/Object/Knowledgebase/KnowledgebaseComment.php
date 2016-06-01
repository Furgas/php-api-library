<?php
namespace Kayako\Api\Client\Object\Knowledgebase;

use Kayako\Api\Client\Common\Helper;
use Kayako\Api\Client\Common\ResultSet;
use Kayako\Api\Client\Object\Base\CommentBase;
use Kayako\Api\Client\Object\Staff\Staff;
use Kayako\Api\Client\Object\User\User;

/**
 * Kayako Knowledgebase comment object.
 *
 * @author Saloni Dhall (https://github.com/Furgas)
 * @link http://wiki.kayako.com/display/DEV/REST+-+KnowledgebaseComment
 * @since Kayako version 4.51.1891
 */
class KnowledgebaseComment extends CommentBase {

	static protected $controller = '/Knowledgebase/Comment';
	static protected $object_xml_name = 'kbarticlecomment';

	/**
	 * KnowledgebaseArticle identifier.
	 * @apiField required_create=true
	 * @var int
	 */
	protected $knowledgebase_article_id;

	/**
	 * KnowledgebaseArticle object.
	 * @var KnowledgebaseArticle
	 */
	protected $knowledgebase_article;

	protected function parseData($data) {
		parent::parseData($data);
		$this->knowledgebase_article_id = Helper::assurePositiveInt($data['kbarticleid']);
	}

	public function buildData($create) {
		$data = parent::buildData($create);

		$this->buildDataNumeric($data, 'knowledgebasearticleid', $this->knowledgebase_article_id);

		return $data;
	}

	/**
	 * Fetches all comments of knowledgebase article from the server.
	 *
	 * @param KnowledgebaseArticle|int $knowledgebase_article Knowledgebase article object or knowledgebase article identifier.
	 * @return ResultSet|KnowledgebaseComment[]
	 */
	static public function getAll($knowledgebase_article) {
		if ($knowledgebase_article instanceof KnowledgebaseArticle) {
			$knowledgebase_article_id = $knowledgebase_article->getId();
		} else {
			$knowledgebase_article_id = $knowledgebase_article;
		}

		$search_parameters = array('ListAll');

		$search_parameters[] = $knowledgebase_article_id;

		return parent::genericGetAll($search_parameters);
	}

	/**
	 * Return KnowledgebaseArticle identifier.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getKnowledgebaseArticleId() {
		return $this->knowledgebase_article_id;
	}

	/**
	 * Sets KnowledgebaseArticle identifier.
	 *
	 * @param int $knowledgebase_article_id KnowledgebaseArticle identifier.
	 * @return KnowledgebaseArticle
	 */
	public function setKnowledgebaseArticleId($knowledgebase_article_id) {
		$this->knowledgebase_article_id = Helper::assurePositiveInt($knowledgebase_article_id);
		$this->knowledgebase_article = null;
		return $this;
	}

	/**
	 * Return KnowledgebaseArticle.
	 *
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return KnowledgebaseArticle
	 */
	public function getKnowledgebaseArticle($reload = false) {
		if ($this->knowledgebase_article !== null && !$reload)
			return $this->knowledgebase_article;

		if ($this->knowledgebase_article_id === null)
			return null;

		$this->knowledgebase_article = KnowledgebaseArticle::get($this->knowledgebase_article_id);
		return $this->knowledgebase_article;
	}

	/**
	 * Sets KnowledgebaseArticle.
	 *
	 * @param KnowledgebaseArticle $knowledgebase_article KnowledgebaseArticle object.
	 * @return KnowledgebaseComment
	 */
	public function setKnowledgebaseArticle($knowledgebase_article) {
		$this->knowledgebase_article = Helper::assureObject($knowledgebase_article, KnowledgebaseArticle::class);
		$this->knowledgebase_article_id = $this->knowledgebase_article !== null ? $this->knowledgebase_article->getId() : null;
		return $this;
	}

	/**
	 * Creates a new Knowledgebase article comment.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param KnowledgebaseArticle $knowledgebase_article KnowledgebaseArticle object.
	 * @param User|Staff|string $creator Creator (staff object, user object or user fullname) of this comment.
	 * @param string $contents Contents of this comment.
	 * @return KnowledgebaseComment
	 */
	static public function createNew($knowledgebase_article, $creator, $contents) {
		/** @var $new_comment KnowledgebaseComment */
		$new_comment = parent::createNew($creator, $contents);
		$new_comment->setKnowledgebaseArticle($knowledgebase_article);
		return $new_comment;
	}
}