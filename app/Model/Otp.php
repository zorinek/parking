<?php

declare(strict_types=1);

namespace App\Model;

use Nette;
use Nette\Utils\Random;
use Nette\Security\Passwords;
use App\Model;

/**
 * Otp
 */
final class Otp implements Nette\Security\IAuthenticator
{

	use Nette\SmartObject;

	/** @var Nette\Database\Context */
	private $db;

	/** @var Nette\Localization\ITranslator */
	private $translator;

	/** @var Passwords */
	private $passwords;
	/* @var Model\Users */
	private $users;

	public const
			TABLE_NAME = 'users_otp',
			COLUMN_USO_ID = 'uso_id',
			COLUMN_USO_PASSHASH = 'uso_passhash',
			COLUMN_USO_VALID_TO = 'uso_valid_to',
			COLUMN_USO_ENABLED = 'uso_enabled',
			COLUMN_USE_ID = 'use_id';

	private $p_logreg;

	public function __construct($p_logreg, Nette\Database\Context $db, Nette\Localization\ITranslator $translator, Passwords $passwords, Model\Users $users)
	{
		$this->db = $db;
		$this->translator = $translator;
		$this->passwords = $passwords;
		$this->users = $users;
		$this->p_logreg = $p_logreg;
	}

	public function generate()
	{
		$out = [];
		for ($i = 0; $i < $this->p_logreg['one_time_password_count']; $i++)
		{
			$out[$i] = [];
			for ($j = 0; $j < $this->p_logreg['one_time_password_count_sequences']; $j++)
			{
				$str = Random::generate($this->p_logreg['one_time_password_count_sequence_length']);
				$out[$i][] = $str;
			}
		}
		return $out;
	}

	public function check($use_id, $uso_enabled)
	{
		$check = $this->db->table(self::TABLE_NAME)->where(self::COLUMN_USE_ID, $use_id)->where(self::COLUMN_USO_ENABLED, $uso_enabled)->fetch();
		return $check;
	}

	public function removeUnused($use_id, $uso_enabled)
	{
		$delete = $this->db->table(self::TABLE_NAME)->where(self::COLUMN_USE_ID, $use_id)->where(self::COLUMN_USO_ENABLED, $uso_enabled)->delete();
	}

	public function insert($use_id, $uso_valid_to, $uso_enabled, $passwords)
	{
		$arr = [];
		foreach ($passwords as $line)
		{
			$pass = implode("-", $line);
			$tmp[self::COLUMN_USE_ID] = $use_id;
			$tmp[self::COLUMN_USO_VALID_TO] = $uso_valid_to;
			$tmp[self::COLUMN_USO_ENABLED] = $uso_enabled;
			$tmp[self::COLUMN_USO_PASSHASH] = $this->passwords->hash($pass);
			$arr[] = $tmp;
		}
		$ins = $this->db->table(self::TABLE_NAME)->insert($arr);
	}

	public function update($use_id, $uso_enabled)
	{
		$this->db->table(self::TABLE_NAME)->where(self::COLUMN_USE_ID, $use_id)->update([
			self::COLUMN_USO_ENABLED => $uso_enabled
		]);
	}

	/**
	 * Performs an authentication.
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials): Nette\Security\IIdentity
	{
//            echo "HereOTP";
//            die();
		[$use_id, $uso_passhash] = $credentials;

		$rows = $this->db->table(self::TABLE_NAME)
				->where(self::COLUMN_USE_ID, $use_id)
				->fetchAll();
		$verifed = false;
		$out = null;
		foreach ($rows as $row)
		{
			if ($this->passwords->verify($uso_passhash, $row[self::COLUMN_USO_PASSHASH]))
			{
				$verifed = true;
				$out = $row;
				break;
			}
		}
		if (!$verifed)
		{
			throw new Nette\Security\AuthenticationException('The password is incorrect.');
		}

		$this->removeLastUsed($out->{self::COLUMN_USO_ID});
		$arr = $this->users->get($out->{$this->users::COLUMN_USE_ID});

		$arr = $arr->toArray();
		unset($arr[$this->users::COLUMN_USE_PASSHASH]);

		return new Nette\Security\Identity($arr[$this->users::COLUMN_USE_ID], $arr[$this->users::COLUMN_USE_ROLE], $arr);
	}

	public function removeLastUsed($uso_id)
	{
		$delete = $this->db->table(self::TABLE_NAME)->where(self::COLUMN_USO_ID, $uso_id)->delete();
	}

	public function getUnusedCount($use_id)
	{
		$get = $this->db->table(self::TABLE_NAME)->where(self::COLUMN_USE_ID, $use_id)->select("COUNT(*) AS count, uso_valid_to")->group("uso_valid_to")->fetch();
		return $get;
	}

	public function removeAll($use_id)
	{
		$delete = $this->db->table(self::TABLE_NAME)->where(self::COLUMN_USE_ID, $use_id)->delete();
	}

}
