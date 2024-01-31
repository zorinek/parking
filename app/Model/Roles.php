<?php

declare(strict_types=1);

namespace App\Model;

use Nette;
use Nette\Utils\Random;
use Nette\Security\Passwords;
use App\Model;

/**
 * Roles
 */
final class Roles
{

	use Nette\SmartObject;

	/** @var Nette\Database\Context */
	private $db;

	public const
			TABLE_NAME = 'users_roles',
			COLUMN_USR_ID = 'usr_id',
			COLUMN_USE_ID = 'use_id',
			COLUMN_USR_ROLE = 'usr_role';

	public function __construct(Nette\Database\Context $db)
	{
		$this->db = $db;
	}

	public function getRoles($use_id)
	{
		$roles = $this->db->table(self::TABLE_NAME)->where(self::COLUMN_USE_ID, $use_id)->fetchPairs(self::COLUMN_USR_ID, self::COLUMN_USR_ROLE);
		return $roles;
	}

	public function insert($use_id, $usr_role)
	{
		$this->db->table(self::TABLE_NAME)->insert([
			self::COLUMN_USE_ID => $use_id,
			self::COLUMN_USR_ROLE => $usr_role
		]);
	}

	public function remove($use_id)
	{
		$this->db->table(self::TABLE_NAME)->where(self::COLUMN_USE_ID, $use_id)->delete();
	}

}
