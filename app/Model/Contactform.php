<?php

declare(strict_types=1);

namespace App\Model;

use Nette;
use Nette\Utils\Random;

/**
 * Contactform
 */
final class Contactform
{

	use Nette\SmartObject;

	public const
			TABLE_NAME = 'contactform',
			COLUMN_CON_ID = 'con_id',
			COLUMN_CON_EMAIL = 'con_email',
			COLUMN_CON_MESSAGE = 'con_message',
			COLUMN_CON_NAME = 'con_name',
			COLUMN_CON_PHONE = 'con_phone',
			COLUMN_CON_CATEGORY = 'con_category',
			COLUMN_CON_SENDER = 'con_sender',
			COLUMN_CON_DATETIMEINSERT = 'con_datetimeinsert',
			COLUMN_USE_ID = 'use_id',
			COLUMN_CON_DATETIMEDONE = 'con_datetimedone';

	/** @var Nette\Database\Context */
	private $db;

	public function __construct(Nette\Database\Context $db)
	{
		$this->db = $db;
	}

	public function insert($values)
	{
		$this->db->table(self::TABLE_NAME)->insert($values);
	}

	public function prepareFileName($name)
	{
		$name = str_replace(" ", "_", $name);
		$name = str_replace(":", "-", $name);
		return $name;
	}

	public function saveFile($html, $dir, $name)
	{
		if (!is_dir($dir))
		{
			throw new \App\Exceptions\DirNotExists;
		}
		$name = $this->prepareFileName($name);
		file_put_contents($dir . $name . ".html", $html);
	}

	public function get($con_id)
	{
		$get = $this->db->table(self::TABLE_NAME)->where(self::COLUMN_CON_ID, $con_id)->fetch();
		return $get;
	}

	public function setDone($con_id, $use_id, $con_datetimedone)
	{
		$this->db->table(self::TABLE_NAME)->where(self::COLUMN_CON_ID, $con_id)->update([
			self::COLUMN_USE_ID => $use_id,
			self::COLUMN_CON_DATETIMEDONE => $con_datetimedone
		]);
	}

}
