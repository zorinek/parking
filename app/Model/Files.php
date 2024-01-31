<?php

declare(strict_types=1);

namespace App\Model;

use Nette;

/**
 * Files
 */
class Files
{

	private const
			TABLE_NAME = 'files',
			COLUMN_FIL_ID = 'fil_id',
			COLUMN_FIL_NAME = 'fil_name',
			COLUMN_FIL_PATH = 'fil_path',
			COLUMN_FIL_STORAGENAME = 'fil_storagename',
			COLUMN_FIL_EXT = 'fil_ext',
			COLUMN_TYP_ID = 'typ_id',
			COLUMN_FIL_REF_TABLE = 'fil_ref_table',
			COLUMN_FIL_REF_TYPE = 'fil_ref_type';

	/** @var Nette\Database\Context */
	private $db;

	public function __construct(Nette\Database\Context $db)
	{
		$this->db = $db;
	}

	public function insertFile($fil_name, $fil_path, $fil_storagename, $fil_ext, $typ_id, $fil_ref_table, $fil_ref_type)
	{
		$ins = $this->db->table(self::TABLE_NAME)->insert([
			self::COLUMN_FIL_NAME => $fil_name,
			self::COLUMN_FIL_PATH => $fil_path,
			self::COLUMN_FIL_STORAGENAME => $fil_storagename,
			self::COLUMN_FIL_EXT => $fil_ext,
			self::COLUMN_TYP_ID => $typ_id,
			self::COLUMN_FIL_REF_TABLE => $fil_ref_table,
			self::COLUMN_FIL_REF_TYPE => $fil_ref_type
		]);
		return $ins->{self::COLUMN_FIL_ID};
	}

	public function getFile($fil_id)
	{
		$get = $this->db->table(self::TABLE_NAME)->where(self::COLUMN_FIL_ID, $fil_id)->fetch();
		return $get;
	}

	public function removeFile($fil_id, $remove_from_drive)
	{
		$file = $this->getFile($fil_id);
		if ($remove_from_drive)
		{
			unlink($file->{self::COLUMN_FIL_PATH} . $file->{self::COLUMN_FIL_STORAGENAME});
		}
		if (!is_null($file->{self::COLUMN_FIL_REF_TABLE}) && !is_null($file->{self::COLUMN_FIL_REF_TYPE}))
		{
			if ($file->{self::COLUMN_FIL_REF_TYPE} == "null")
			{
				$this->db->table($file->{self::COLUMN_FIL_REF_TABLE})->where(self::COLUMN_FIL_ID, $fil_id)->update([self::COLUMN_FIL_ID => NULL]);
			} 
			else if ($file->{self::COLUMN_FIL_REF_TYPE} == "remove")
			{
				$this->db->table($file->{self::COLUMN_FIL_REF_TABLE})->where(self::COLUMN_FIL_ID, $fil_id)->delete();
			}
		}
		$this->db->table(self::TABLE_NAME)->where(self::COLUMN_FIL_ID, $fil_id)->delete();
	}

}
