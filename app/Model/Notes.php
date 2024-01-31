<?php

declare(strict_types=1);

namespace App\Model;

use Nette;
use App\Model;

/**
 * Notes
 */
final class Notes
{

	use Nette\SmartObject;

	/** @var Nette\Database\Context */
	private $db;

	public const
			TABLE_NAME = 'notes',
			COLUMN_NOT_ID = 'not_id',
			COLUMN_NOT_PAGE = 'not_page',
			COLUMN_NOT_NOTE = 'not_note',
			COLUMN_USE_ID = 'use_id';

	public function __construct(Nette\Database\Context $db)
	{
		$this->db = $db;
	}

	public function insert($vals)
	{
		$this->db->table(self::TABLE_NAME)->insert($vals);
	}

	public function getNotesForPage($not_page, $use_id)
	{
		$get = $this->db->table(self::TABLE_NAME)->where(self::COLUMN_NOT_PAGE, $not_page)->where(self::COLUMN_USE_ID, $use_id)->order(self::COLUMN_NOT_ID . " DESC")->fetchAll();
		return $get;
	}

	public function getNotesForPageWithoutUser($not_page)
	{
		$get = $this->db->table(self::TABLE_NAME)->where(self::COLUMN_NOT_PAGE, $not_page)->order(self::COLUMN_NOT_ID . " ASC")->fetchAll();
		return $get;
	}

	public function removeWithUser($not_id, $use_id)
	{
		$this->db->table(self::TABLE_NAME)->where(self::COLUMN_NOT_ID, $not_id)->where(self::COLUMN_USE_ID, $use_id)->delete();
	}

}
