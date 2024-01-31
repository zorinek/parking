<?php

declare(strict_types=1);

namespace App\Model;

use Nette;

/**
 * UsersConfigurations
 */
final class UsersConfigurations
{

	use Nette\SmartObject;

	public const
			TABLE_NAME = 'users_configurations',
			COLUMN_USC_ID = 'usc_id',
			COLUMN_USE_ID = 'use_id',
			COLUMN_USC_TYPE = 'usc_type',
			COLUMN_USC_VALUE = 'usc_value',
			COLUMN_USC_DATETIMEINSERT = 'usc_datetimeinsert';

	/** @var Nette\Database\Context */
	private $db;

	public function __construct(Nette\Database\Context $db)
	{
		$this->db = $db;
	}

	public function insert($values)
	{
		$ins = $this->db->table(self::TABLE_NAME)->insert($values);
		return $ins->{self::COLUMN_USC_ID};
	}

	public function getUserConfiguration($use_id)
	{
		$get = $this->db->table(self::TABLE_NAME)->where(self::COLUMN_USE_ID, $use_id)->fetchAssoc("usc_type->");
		return $get;
	}

	public function check($use_id, $usc_type)
	{
		$get = $this->db->table(self::TABLE_NAME)->where(self::COLUMN_USE_ID, $use_id)->where(self::COLUMN_USC_TYPE, $usc_type)->fetch();
		return $get;
	}

	public function insertUpdate($values)
	{
		$check = $this->check($values[self::COLUMN_USE_ID], $values[self::COLUMN_USC_TYPE]);
		if (!$check)
		{
			$this->insert($values);
		} 
		else
		{
			$this->db->table(self::TABLE_NAME)->where(self::COLUMN_USE_ID, $values[self::COLUMN_USE_ID])->where(self::COLUMN_USC_TYPE, $values[self::COLUMN_USC_TYPE])->update($values);
		}
	}

}
