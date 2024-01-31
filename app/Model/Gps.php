<?php

declare(strict_types=1);

namespace App\Model;

use Nette;
use Nette\Utils\Random;

/**
 * Gps
 */
final class Gps
{

	use Nette\SmartObject;

	public const
			TABLE_NAME = 'gps',
			COLUMN_GPS_ID = 'gps_id',
			COLUMN_GPS_LAT = 'gps_lat',
			COLUMN_GPS_LNG = 'gps_lng',
			COLUMN_GPS_ODOMETERM = 'gps_odometerm',
			COLUMN_GPS_UTC = 'gps_utc',
			COLUMN_SEG_ID = 'seg_id',
			COLUMN_CAM_ID = 'cam_id';

	/** @var Nette\Database\Context */
	private $db;

	public function __construct(Nette\Database\Context $db)
	{
		$this->db = $db;
	}

	public function getAll($seg_id, $cam_id)
	{
		$get = $this->db->table(self::TABLE_NAME)->select(":detections(gps_id).gps_id, gps.*, det_id, det_lp, det_parkingtype")->where(self::COLUMN_SEG_ID, $seg_id)->where(self::COLUMN_CAM_ID, $cam_id)->order(self::COLUMN_GPS_UTC, "ASC")->fetchAll();
		$out = [];
		foreach ($get as $line)
		{
			if (is_null($line->det_id))
			{
				$out[$line->{self::COLUMN_GPS_UTC}->format("Y-m-d H:i:s.u")] = [$line->{self::COLUMN_GPS_LAT}, $line->{self::COLUMN_GPS_LNG}, $line->{self::COLUMN_GPS_UTC}->format("Y-m-d H:i:s.u")];
			} 
			else
			{
				$out[$line->{self::COLUMN_GPS_UTC}->format("Y-m-d H:i:s.u")] = [$line->{self::COLUMN_GPS_LAT}, $line->{self::COLUMN_GPS_LNG}, $line->{self::COLUMN_GPS_UTC}->format("Y-m-d H:i:s.u"), "detected", $line->det_lp, $line->det_parkingtype];
			}
		}
		return $out;
	}

	public function getMaxMinTimeAll($seg_id, $cam_id)
	{
		$get = $this->db->table(self::TABLE_NAME)->select("MAX(" . self::COLUMN_GPS_UTC . ") AS max_time, MIN(" . self::COLUMN_GPS_UTC . ") AS min_time")->where(self::COLUMN_SEG_ID, $seg_id)->where(self::COLUMN_CAM_ID, $cam_id)->fetch();
		return $get;
	}

}
