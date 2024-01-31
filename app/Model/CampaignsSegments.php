<?php

declare(strict_types=1);

namespace App\Model;

use Nette;
use Nette\Application\LinkGenerator;

/**
 * CampaignsSegments
 */
final class CampaignsSegments
{

	use Nette\SmartObject;

	public const
			TABLE_NAME = 'campaigns_segments',
			COLUMN_CAS_ID = 'cas_id',
			COLUMN_CAM_ID = 'cam_id',
			COLUMN_SEG_ID = 'seg_id',
			COLUMN_CAS_PARKINGDETECTED_LEFT = 'cas_parkingdetected_left',
			COLUMN_CAS_PARKINGFREE_LEFT = 'cas_parkingfree_left',
			COLUMN_CAS_PARKINGILLEGAL_LEFT = 'cas_parkingillegal_left',
			COLUMN_CAS_PARKINGNOTDETECTED_LEFT = 'cas_parkingnotdetected_left',
			COLUMN_CAS_PARKINGDETECTED_RIGHT = 'cas_parkingdetected_right',
			COLUMN_CAS_PARKINGFREE_RIGHT = 'cas_parkingfree_right',
			COLUMN_CAS_PARKINGILLEGAL_RIGHT = 'cas_parkingillegal_right',
			COLUMN_CAS_PARKINGNOTDETECTED_RIGHT = 'cas_parkingnotdetected_right',
			COLUMN_CAS_PARKINGDETECTED = 'cas_parkingdetected',
			COLUMN_CAS_PARKINGFREE = 'cas_parkingfree',
			COLUMN_CAS_PARKINGILLEGAL = 'cas_parkingillegal',
			COLUMN_CAS_PARKINGNOTDETECTED = 'cas_parkingnotdetected',
			COLUMN_CAS_DONE = 'cas_done',
			COLUMN_USE_ID = 'use_id',
			COLUMN_CAS_DATETIME_RESERVATION = 'cas_datetime_reservation';

	/** @var Nette\Database\Context */
	private $db;

	/** @var Nette\Application\LinkGenerator */
	private $linkGenerator;

	public function __construct(Nette\Database\Context $db, Nette\Application\LinkGenerator $linkGenerator)
	{
		$this->db = $db;
		$this->linkGenerator = $linkGenerator;
	}

	public function insert($values)
	{
		$ins = $this->db->table(self::TABLE_NAME)->insert($values);
		return $ins->{self::COLUMN_PRO_ID};
	}

	public function update($values)
	{
		$upd = $this->db->table(self::TABLE_NAME)
				->where(self::COLUMN_CAM_ID, $values[self::COLUMN_CAM_ID])
				->where(self::COLUMN_SEG_ID, $values[self::COLUMN_SEG_ID])
				->update($values);
	}

	public function getOne($cam_id, $seg_id, $cas_done)
	{
		$get = $this->db->table(self::TABLE_NAME)->where(self::COLUMN_CAM_ID, $cam_id)->where(self::COLUMN_SEG_ID, $seg_id)->fetch();
		return $get;
	}

	public function getSegmentsForMap($cam_id)
	{
		$get = $this->db->table("segments")->select(":campaigns_segments(seg_id).cam_id, seg_coordinates, cas_done, segments.seg_id, pro_id")->where("cam_id", $cam_id)->fetchAll();
		$out = [];
		$i = 0;
		foreach ($get as $line)
		{
			if (!is_null($line->seg_coordinates))
			{
				$bl = explode(",", $line->seg_coordinates);
				$res = array_map(function ($value)
				{
					return explode(" ", $value);
				}, $bl);

				$out[$i]["poly"] = $res;
				$out[$i]["done"] = $line->cas_done;
				$out[$i]["link"] = $this->linkGenerator->link("Projects:Campaigns:detail", [$line->pro_id, $line->cam_id, $line->seg_id]);
				$i++;
			}
		}
		return json_encode($out);
	}

	public function checkReservation($values)
	{
		$check = $this->db->table(self::TABLE_NAME)->where(self::COLUMN_CAM_ID, $values[self::COLUMN_CAM_ID])->where(self::COLUMN_SEG_ID, $values[self::COLUMN_SEG_ID])->where(self::COLUMN_USE_ID . " IS NULL")->fetch();
		return $check;
	}

	public function checkReservationValid($values)
	{
		$check = $this->db->table(self::TABLE_NAME)->where(self::COLUMN_CAM_ID, $values[self::COLUMN_CAM_ID])->where(self::COLUMN_SEG_ID, $values[self::COLUMN_SEG_ID])->where(self::COLUMN_USE_ID, $values[self::COLUMN_USE_ID])->fetch();
		return $check;
	}

	public function checkUserReservation($use_id, $cam_id)
	{
		$get = $this->db->table(self::TABLE_NAME)->where(self::COLUMN_USE_ID, $use_id)->where(self::COLUMN_CAM_ID, $cam_id)->where(self::COLUMN_CAS_DONE, 2)->fetch();
		return $get;
	}

	public function getNextNotDoneSegment($cam_id)
	{
		$get = $this->db->table(self::TABLE_NAME)->where(self::COLUMN_CAM_ID, $cam_id)->where(self::COLUMN_CAS_DONE, '0')->where(self::COLUMN_USE_ID . " IS NULL")->fetch();
		return $get;
	}

	public function getMyReservations($use_id, $pro_id)
	{
		$get = $this->db->table(self::TABLE_NAME)->select(self::TABLE_NAME . ".*, cam_id.cam_name, cam_id.pro_id")->where(self::COLUMN_USE_ID, $use_id)->where("cam_id.pro_id", $pro_id)->where(self::COLUMN_CAS_DONE, 2)->fetchAll();
		return $get;
	}

	public function getMyReservationsAll($use_id)
	{
		$get = $this->db->table(self::TABLE_NAME)->select(self::TABLE_NAME . ".*, cam_id.cam_name, cam_id.pro_id, cam_id.pro_id.pro_name")->where(self::COLUMN_USE_ID, $use_id)->where(self::COLUMN_CAS_DONE, 2)->fetchAll();
		return $get;
	}

	public function getAll($pro_id)
	{
		$get = $this->db->table(self::TABLE_NAME)->select(self::TABLE_NAME . ".*, cam_id.pro_id.pro_name, cam_name, seg_id.on")->where("cam_id.pro_id", $pro_id)->order(self::COLUMN_SEG_ID . " ASC")->fetchAssoc("cam_name->cas_id->");
		return $get;
	}

	public function getAllFilled($seg_id, $cam_id)
	{
		$get = $this->db->table(self::TABLE_NAME)->select(self::TABLE_NAME . ".*, cam_id.cam_name")->where(self::COLUMN_SEG_ID, $seg_id)->where("campaigns_segments." . self::COLUMN_CAM_ID . " != ?", $cam_id)->where(self::COLUMN_CAS_DONE, 1)->fetchAll();
		return $get;
	}

}
