<?php
namespace Kayako\Api\Client\Object\Knowledgebase;

use Kayako\Api\Client\Common\Helper;
use Kayako\Api\Client\Common\ResultSet;
use Kayako\Api\Client\Config;
use Kayako\Api\Client\Exception\BadMethodCallException;
use Kayako\Api\Client\Exception\GeneralException;
use Kayako\Api\Client\Object\Base\ObjectBase;

/**
 * Kayako KnowledgebaseAttachment object.
 *
 * @author Saloni Dhall (https://github.com/SaloniDhall)
 * @link http://wiki.kayako.com/display/DEV/REST+-+KnowledgebaseAttachment
 * @since Kayako version 4.64
 */
class KnowledgebaseAttachment extends ObjectBase {

	/**
	 * kbarticle attachment identifier.
	 * @apiField
	 * @var int
	 */
	protected $id;

	/**
	 * kbarticleid identifier.
	 * @apiField
	 * @var int
	 */
	protected $kbarticle_id;

	/**
	 * kbarticle file name.
	 * @apiField required_create=true
	 * @var string
	 */
	protected $file_name;

	/**
	 * kbarticle size in bytes.
	 * @apiField
	 * @var int
	 */
	protected $file_size;

	/**
	 * kbarticle MIME type.
	 * @apiField
	 * @var string
	 */
	protected $file_type;

	/**
	 * Raw contents of attachment.
	 * @apiField required_create=true
	 * @var string
	 */
	protected $contents;

	/**
	 * kbarticle with this attachment.
	 * @var KnowledgebaseArticle
	 */
	private $kbarticle = null;

	/**
	 * Timestamp of when this attachment was created.
	 * @apiField
	 * @var int
	 */
	protected $dateline;

	static protected $controller = '/Knowledgebase/Attachment';
	static protected $object_xml_name = 'kbattachment';

	protected function parseData($data) {
		$this->id = intval($data['id']);
		$this->kbarticle_id = Helper::assurePositiveInt($data['kbarticleid']);
		$this->file_name = $data['filename'];
		$this->file_size = intval($data['filesize']);
		$this->file_type = $data['filetype'];
		$this->dateline = Helper::assurePositiveInt($data['dateline']);
		if (array_key_exists('contents', $data) && strlen($data['contents']) > 0)
			$this->contents = base64_decode($data['contents']);
	}

	public function buildData($create) {
		$this->checkRequiredAPIFields($create);

		$data = array();

		$data['kbarticleid'] = $this->kbarticle_id;
		$data['filename'] = $this->file_name;
		$data['contents'] = $this->contents;

		return $data;
	}

	/**
	 * Fetches all attachments of knowledgebase article from the server.
	 *
	 * @param KnowledgebaseArticle|int $knowledgebase_article Knowledgebase article object or knowledgebase article identifier.
	 * @return ResultSet|KnowledgebaseAttachment[]
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
	 * Fetches knowledgebase article attachment from the server.
	 *
	 * @param int $knowledgebase_article_id Knowledgebase article identifier.
	 * @param int $id Attachment identifier.
	 * @return KnowledgebaseAttachment
	 */
	static public function get($knowledgebase_article_id, $id) {
		return parent::genericGet(array($knowledgebase_article_id, $id));
	}

	public function update() {
		throw new BadMethodCallException("You can't update objects of type KnowledgebaseAttachment.");
	}

	public function delete() {
		self::getRESTClient()->delete(static::$controller, array($this->kbarticle_id, $this->id));
	}

	public function toString() {
		return sprintf("%s (filetype: %s, filesize: %s)", $this->getFileName(), $this->getFileType(), $this->getFileSize(true));
	}

	public function getId($complete = false) {
		return $complete ? array($this->kbarticle_id, $this->id) : $this->id;
	}

	/**
	 * Returns identifier of the kbarticle this attachment belongs to.
	 *
	 * @return int
	 */
	public function getKbarticleId() {
		return $this->kbarticle_id;
	}

	/**
	 * Sets identifier of the kbarticle this attachment will belong to.
	 *
	 * @param int $kbarticle_id kbarticle identifier.
	 * @return KnowledgebaseAttachment
	 */
	public function setKbarticleId($kbarticle_id) {
		$this->kbarticle_id = Helper::assurePositiveInt($kbarticle_id);
		$this->kbarticle = null;
		return $this;
	}

	/**
	 * Returns the kbarticle this attachment belongs to.
	 *
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return KnowledgebaseArticle
	 */
	public function getKbarticle($reload = false) {
		if ($this->kbarticle !== null && !$reload)
			return $this->kbarticle;

		if ($this->kbarticle_id === null)
			return null;

		$this->kbarticle = KnowledgebaseArticle::get($this->kbarticle_id);
		return $this->kbarticle;
	}

	/**
	 * Returns attachment file name.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getFileName() {
		return $this->file_name;
	}

	/**
	 * Sets the attachment file name.
	 *
	 * @param string $file_name File name.
	 * @return KnowledgebaseAttachment
	 */
	public function setFileName($file_name) {
		$this->file_name = Helper::assureString($file_name);
		return $this;
	}

	/**
	 * Returns attachment file size.
	 *
	 * @param bool $formatted True to format result nicely (KB, MB, and so on).
	 * @return mixed
	 * @filterBy
	 * @orderBy
	 */
	public function getFileSize($formatted = false) {
		if ($formatted) {
			return Helper::formatBytes($this->file_size);
		}

		return $this->file_size;
	}

	/**
	 * Returns attachment MIME type.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getFileType() {
		return $this->file_type;
	}

	/**
	 * Returns date and time of when this attachment was created.
	 *
	 * @see http://www.php.net/manual/en/function.date.php
	 *
	 * @param string $format Output format of the date. If null the format set in client configuration is used.
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getDateline($format = null) {
		if ($this->dateline == null)
			return null;

		if ($format === null) {
			$format = Config::get()->getDatetimeFormat();
		}

		return date($format, $this->dateline);
	}

	/**
	 * Return raw contents of the attachment (NOT base64 encoded).
	 *
	 * @param bool $auto_fetch True to automatically fetch the contents of the attachment if not present.
	 * @return string
	 */
	public function &getContents($auto_fetch = true) {
		if ($this->contents === null && is_numeric($this->id) && is_numeric($this->kbarticle_id) && $auto_fetch) {
			$attachment = $this->get($this->kbarticle_id, $this->id);
			$this->contents = $attachment->getContents(false);
		}
		return $this->contents;
	}

	/**
	 * Sets raw contents of the attachment (NOT base64 encoded).
	 *
	 * @param string $contents Raw contents of the attachment (NOT base64 encoded).
	 * @return KnowledgebaseAttachment
	 */
	public function setContents(&$contents) {
		$this->contents =& $contents;
		return $this;
	}

	/**
	 * Sets contents of the attachment by reading it from a physical file.
	 *
	 * @param string $file_path Path to file.
	 * @param string $file_name Optional. Use to set filename other than physical file.
	 * @throws GeneralException
	 * @return KnowledgebaseAttachment
	 */
	public function setContentsFromFile($file_path, $file_name = null) {
		$contents = base64_encode(file_get_contents($file_path));
		if ($contents === false)
			throw new GeneralException(sprintf("Error reading contents of %s.", $file_path));

		$this->contents = &$contents;
		if ($file_name === null)
			$file_name = basename($file_path);
		$this->file_name = $file_name;
		return $this;
	}

	/**
	 * Creates new attachment for kbarticle with contents provided as parameter.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param KnowledgebaseArticle $kbarticle knowledgebase article.
	 * @param string $contents Raw contents of the file.
	 * @param string $file_name Filename.
	 * @return KnowledgebaseAttachment
	 */
	static public function createNew($kbarticle, $contents, $file_name) {
		$new_kbarticle_attachment = new KnowledgebaseAttachment();

		$new_kbarticle_attachment->setKbarticleId($kbarticle->getId());
		$new_kbarticle_attachment->setContents($contents);
		$new_kbarticle_attachment->setFileName($file_name);

		return $new_kbarticle_attachment;
	}

	/**
	 * Creates new attachment for kbarticle with contents read from physical file.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param KnowledgebaseArticle $knowledgebase_article KnowledgebaseArticle.
	 * @param string $file_path Path to file.
	 * @param string $file_name Optional. Use to set filename other than physical file.
	 * @return KnowledgebaseAttachment
	 */
	static public function createNewFromFile($knowledgebase_article, $file_path, $file_name = null) {
		$new_kbarticle_attachment = new KnowledgebaseAttachment();

		$new_kbarticle_attachment->setKbarticleId($knowledgebase_article->getId());
		$new_kbarticle_attachment->setContentsFromFile($file_path, $file_name);

		return $new_kbarticle_attachment;
	}
}