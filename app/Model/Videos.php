<?php

declare(strict_types=1);

namespace App\Model;

use Nette;

/**
 * Videos
 */
final class Videos
{

	use Nette\SmartObject;

	public const
			TABLE_NAME = 'videos',
			COLUMN_VID_ID = 'vid_id',
			COLUMN_VID_NAME = 'vid_name',
			COLUMN_VID_START = 'vid_start',
			COLUMN_VID_END = 'vid_end',
			COLUMN_VID_PLAYTIME = 'vid_playtime',
			COLUMN_VID_SIDE = 'vid_side',
			COLUMN_PRO_ID = 'pro_id';

	/** @var Nette\Database\Context */
	private $db;

	public function __construct(Nette\Database\Context $db)
	{
		$this->db = $db;
	}

	public function insert($values)
	{
		$ins = $this->db->table(self::TABLE_NAME)->insert($values);
		return $ins->{self::COLUMN_VID_ID};
	}

	public function check($values)
	{
		$check = $this->db->table(self::TABLE_NAME)
				->where(self::COLUMN_VID_NAME, $values[self::COLUMN_VID_NAME])
				->where(self::COLUMN_VID_START, $values[self::COLUMN_VID_START])
				->where(self::COLUMN_VID_END, $values[self::COLUMN_VID_END])
				->where(self::COLUMN_VID_PLAYTIME, $values[self::COLUMN_VID_PLAYTIME])
				->where(self::COLUMN_VID_SIDE, $values[self::COLUMN_VID_SIDE])
				->where(self::COLUMN_PRO_ID, $values[self::COLUMN_PRO_ID])
				->fetch();
		return $check;
	}

	public function getDuration($filename)
	{
		include_once('../lib/getid3/getid3.php');
		$getID3 = new \getID3;
		$file = $getID3->analyze($filename);
		return $file;
	}

	public function getByTime($max_time, $min_time, $pro_id)
	{
		$get = $this->db->query("SELECT * FROM " . self::TABLE_NAME . " WHERE (" . self::COLUMN_PRO_ID . " = ?) AND ((" . self::COLUMN_VID_START . " <= ?) AND (" . self::COLUMN_VID_END . " >= ?) OR (" . self::COLUMN_VID_START . " <= ?) AND (" . self::COLUMN_VID_END . " >= ?)) ORDER BY vid_start ASC", $pro_id, $min_time, $min_time, $max_time, $max_time);
		$res = $get->fetchAssoc("vid_side->");
		$all = $get->fetchAssoc("vid_side->vid_start->");
		return [$res, $all];
	}

	public function getByAllTimes($all_times, $pro_id)
	{
		$sql = [];
		$sql1 = [];
		$sql2 = [];
		$sql3 = [];
		$sql4 = [];
		foreach ($all_times as $ln)
		{
			$sql[] = "(" . self::COLUMN_VID_START . " <= '" . $ln[0] . "') AND (" . self::COLUMN_VID_END . " >= '" . $ln[0] . "') AND (" . self::COLUMN_VID_END . " <= '" . $ln[3] . "')";
			$sql1[] = "(" . self::COLUMN_VID_START . " <= '" . $ln[1] . "') AND (" . self::COLUMN_VID_END . " >= '" . $ln[1] . "') AND (" . self::COLUMN_VID_END . " <= '" . $ln[3] . "')";
			$sql2[] = "(" . self::COLUMN_VID_START . " <= '" . $ln[0] . "') AND (" . self::COLUMN_VID_END . " >= '" . $ln[0] . "') AND (" . self::COLUMN_VID_START . " >= '" . $ln[2] . "')";
			$sql3[] = "(" . self::COLUMN_VID_START . " <= '" . $ln[1] . "') AND (" . self::COLUMN_VID_END . " >= '" . $ln[1] . "') AND (" . self::COLUMN_VID_START . " >= '" . $ln[2] . "')";
			$sql4[] = "(" . self::COLUMN_VID_START . " >= '" . $ln[0] . "') AND (" . self::COLUMN_VID_END . " <= '" . $ln[1] . "')";
		}

		$imp = implode(" OR ", $sql);
		$imp1 = implode(" OR ", $sql1);
		$imp2 = implode(" OR ", $sql2);
		$imp3 = implode(" OR ", $sql3);
		$imp4 = implode(" OR ", $sql4);
		$get = $this->db->query(
				"SELECT * FROM (SELECT * FROM " . self::TABLE_NAME . " WHERE (" . self::COLUMN_PRO_ID . " = ?) AND " . $imp
				. " UNION "
				. "SELECT * FROM " . self::TABLE_NAME . " WHERE (" . self::COLUMN_PRO_ID . " = ?) AND " . $imp1
				. " UNION "
				. "SELECT * FROM " . self::TABLE_NAME . " WHERE (" . self::COLUMN_PRO_ID . " = ?) AND " . $imp2
				. " UNION "
				. "SELECT * FROM " . self::TABLE_NAME . " WHERE (" . self::COLUMN_PRO_ID . " = ?) AND " . $imp3
				. " UNION "
				. "SELECT * FROM " . self::TABLE_NAME . " WHERE (" . self::COLUMN_PRO_ID . " = ?) AND " . $imp4
				. ") t ORDER BY vid_start ASC", $pro_id, $pro_id, $pro_id, $pro_id, $pro_id);
		$res = $get->fetchAssoc("vid_side->");
		$all = $get->fetchAssoc("vid_side->vid_start->");
		return [$res, $all];
	}

}
