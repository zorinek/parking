<?php

declare(strict_types=1);

namespace App\Model;

use Nette;

/**
 * Campaigns
 */
final class Campaigns
{

	use Nette\SmartObject;

	public const
			TABLE_NAME = 'campaigns',
			COLUMN_CAM_ID = 'cam_id',
			COLUMN_PRO_ID = 'pro_id',
			COLUMN_CAM_NAME = 'cam_name';

	/** @var Nette\Database\Context */
	private $db;

	public function __construct(Nette\Database\Context $db)
	{
		$this->db = $db;
	}

	public function insert($values)
	{
		$ins = $this->db->table(self::TABLE_NAME)->insert($values);
		return $ins->{self::COLUMN_PRO_ID};
	}

	public function getAll($pro_id)
	{
		$get = $this->db->table(self::TABLE_NAME)->where(self::COLUMN_PRO_ID, $pro_id)->fetchAll();
		return $get;
	}

	public function getCampaign($pro_id, $cam_id)
	{
		$get = $this->db->table(self::TABLE_NAME)->where(self::COLUMN_PRO_ID, $pro_id)->where(self::COLUMN_CAM_ID, $cam_id)->fetch();
		return $get;
	}

}
