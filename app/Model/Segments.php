<?php

declare(strict_types=1);

namespace App\Model;

use Nette;

/**
 * Segments
 */
final class Segments
{

	use Nette\SmartObject;

	public const
			TABLE_NAME = 'segments',
			COLUMN_SEG_ID = 'seg_id',
			COLUMN_PRO_ID = 'pro_id';

	/** @var Nette\Database\Context */
	private $db;

	public function __construct(Nette\Database\Context $db)
	{
		$this->db = $db;
	}

	public function get($seg_id)
	{
		$get = $this->db->table(self::TABLE_NAME)->where(self::COLUMN_SEG_ID, $seg_id)->fetch();
		return $get;
	}

}
