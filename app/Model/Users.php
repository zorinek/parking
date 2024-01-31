<?php

declare(strict_types=1);

namespace App\Model;

use Nette;
use Nette\Security\Passwords;
use App\Model;

/**
 * Users
 */
final class Users implements Nette\Security\IAuthenticator
{

	use Nette\SmartObject;

	public const
			TABLE_NAME = 'users',
			COLUMN_USE_ID = 'use_id',
			COLUMN_USE_EMAIL = 'use_email',
			COLUMN_USE_PASSHASH = 'use_passhash',
			COLUMN_USE_NAME = 'use_name',
			COLUMN_USE_PHONE = 'use_phone',
			COLUMN_USE_TOKEN_EMAIL = 'use_token_email',
			COLUMN_USE_TOKEN_EXPIRATION_EMAIL = 'use_token_expiration_email',
			COLUMN_USE_EMAIL_VERIFED = 'use_email_verifed',
			COLUMN_USE_TOKEN_PASSWORD = 'use_token_password',
			COLUMN_USE_TOKEN_EXPIRATION_PASSWORD = 'use_token_expiration_password',
			COLUMN_USE_TERMS_AGREEMENT = 'use_terms_agreement',
			COLUMN_USE_TFA_ENABLED = 'use_tfa_enabled',
			COLUMN_USE_TFA_SECRET = 'use_tfa_secret',
			COLUMN_USE_ROLE = 'use_role',
			COLUMN_USE_ACTIVE = 'use_active',
			COLUMN_USE_FIRST_LOGIN = 'use_first_login',
			COLUMN_USE_DISCUSSION_AUTHORIZED = 'use_discussion_authorized',
			COLUMN_FIL_ID = 'fil_id';

	/** @var Nette\Database\Context */
	private $db;

	/** @var Passwords */
	private $passwords;

	/** @var Model\Roles $roles */
	private $roles;
	private $p_logreg;

	public function __construct($p_logreg, Nette\Database\Context $db, Passwords $passwords, Model\Roles $roles)
	{
		$this->p_logreg = $p_logreg;
		$this->db = $db;
		$this->passwords = $passwords;
		$this->roles = $roles;
	}

	/**
	 * Adds new user.
	 * @throws DuplicateNameException
	 */
	public function add($values)
	{
		Nette\Utils\Validators::assert($values[self::COLUMN_USE_EMAIL], 'email');
		try
		{
			$ins = $this->db->table(self::TABLE_NAME)->insert($values);
			return $ins->{self::COLUMN_USE_ID};
		} catch (Nette\Database\UniqueConstraintViolationException $e)
		{
			throw new \App\Exceptions\DuplicateEmailException;
		}
	}

	public function verifyToken($token)
	{
		$check = $this->db->table(self::TABLE_NAME)->where(self::COLUMN_USE_TOKEN_EMAIL, $token)->fetch();
		return $check;
	}

	public function verifyEmail($use_id, $use_email_verifed)
	{
		$this->db->table(self::TABLE_NAME)->where(self::COLUMN_USE_ID, $use_id)->update([
			self::COLUMN_USE_EMAIL_VERIFED => $use_email_verifed
		]);
	}

	public function updateTfa($use_id, $use_tfa_secret)
	{
		$this->db->table(self::TABLE_NAME)->where(self::COLUMN_USE_ID, $use_id)->update([
			self::COLUMN_USE_TFA_SECRET => $use_tfa_secret
		]);
	}

	public function changeTfa($use_id, $use_tfa_enabled)
	{
		$this->db->table(self::TABLE_NAME)->where(self::COLUMN_USE_ID, $use_id)->update([
			self::COLUMN_USE_TFA_ENABLED => $use_tfa_enabled
		]);
	}

	public function checkUser($use_email, $use_passhash)
	{
		$row = $this->db->table(self::TABLE_NAME)
				->where(self::COLUMN_USE_EMAIL, $use_email)
				->fetch();

		if (!$row)
		{
			throw new Nette\Security\AuthenticationException('', self::IDENTITY_NOT_FOUND);
		} else if ($this->p_logreg['send_email_verification'] && $row[self::COLUMN_USE_EMAIL_VERIFED] !== 1)
		{
			throw new \App\Exceptions\EmailNotValidatedException;
		} else if (!$this->passwords->verify($use_passhash, $row[self::COLUMN_USE_PASSHASH]))
		{
			throw new Nette\Security\AuthenticationException('', self::INVALID_CREDENTIAL);
		} else if ($this->passwords->needsRehash($row[self::COLUMN_USE_PASSHASH]))
		{
			$row->update([
				self::COLUMN_USE_PASSHASH => $this->passwords->hash($use_passhash),
			]);
		}
		return $row;
	}

	/**
	 * Performs an authentication.
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials): Nette\Security\IIdentity
	{
//            echo "Here";
		[$use_email, $use_passhash] = $credentials;

		$row = $this->db->table(self::TABLE_NAME)
				->where(self::COLUMN_USE_EMAIL, $use_email)
				->fetch();

		if (!$row)
		{
			throw new Nette\Security\AuthenticationException('', self::IDENTITY_NOT_FOUND);
		} elseif (!$this->passwords->verify($use_passhash, $row[self::COLUMN_USE_PASSHASH]))
		{
			throw new Nette\Security\AuthenticationException('', self::INVALID_CREDENTIAL);
		} elseif ($this->passwords->needsRehash($row[self::COLUMN_USE_PASSHASH]))
		{
			$row->update([
				self::COLUMN_USE_PASSHASH => $this->passwords->hash($use_passhash),
			]);
		}


		$arr = $row->toArray();
		unset($arr[self::COLUMN_USE_PASSHASH]);
		if ($this->p_logreg['multiple_roles'])
		{
			$roles = array_values($this->roles->getRoles($row[self::COLUMN_USE_ID]));
		} else
		{
			$roles = $row[self::COLUMN_USE_ROLE];
		}
		if (empty($roles))
		{
			throw new \Exception("User has no roles! Multiple roles in system activation is: " . ($this->p_logreg['multiple_roles'] ? 'true' : 'false'));
		}
		return new Nette\Security\Identity($row[self::COLUMN_USE_ID], $roles, $arr);
	}

	public function checkEmail($use_email)
	{
		$check = $this->db->table(self::TABLE_NAME)->where(self::COLUMN_USE_EMAIL, $use_email)->fetch();
		return $check;
	}

	public function update($values)
	{
		$upd = $this->db->table(self::TABLE_NAME)->where(self::COLUMN_USE_ID, $values[self::COLUMN_USE_ID])->update($values);
	}

	public function verifyTokenPassword($token)
	{
		$check = $this->db->table(self::TABLE_NAME)->where(self::COLUMN_USE_TOKEN_PASSWORD, $token)->fetch();
		return $check;
	}

	public function get($use_id)
	{
		$get = $this->db->table(self::TABLE_NAME)->where(self::COLUMN_USE_ID, $use_id)->fetch();
		return $get;
	}

	public function getByEmail($use_email)
	{
		$get = $this->db->table(self::TABLE_NAME)->where(self::COLUMN_USE_EMAIL, $use_email)->fetch();
		return $get;
	}

	public function checkPasswordAdmin($use_id, $use_passhash)
	{
		$row = $this->db->table(self::TABLE_NAME)
				->where(self::COLUMN_USE_ID, $use_id)
				->fetch();

		if (!$this->passwords->verify($use_passhash, $row[self::COLUMN_USE_PASSHASH]))
		{
			throw new Nette\Security\AuthenticationException('', self::INVALID_CREDENTIAL);
		} else if ($this->passwords->needsRehash($row[self::COLUMN_USE_PASSHASH]))
		{
			$row->update([
				self::COLUMN_USE_PASSHASH => $this->passwords->hash($use_passhash),
			]);
		}
		return $row;
	}

	public function updateFirstLogin($use_id, $use_first_login)
	{
		$this->db->table(self::TABLE_NAME)->where(self::COLUMN_USE_ID, $use_id)->update([
			self::COLUMN_USE_FIRST_LOGIN => $use_first_login
		]);
	}

	public function setImageFile($use_id, $fil_id)
	{
		$this->db->table(self::TABLE_NAME)->where(self::COLUMN_USE_ID, $use_id)->update([
			self::COLUMN_FIL_ID => $fil_id
		]);
	}

	public function checkImage($use_id)
	{
		$get = $this->db->table(self::TABLE_NAME)->where(self::COLUMN_USE_ID, $use_id)->fetch();
		return $get->{self::COLUMN_FIL_ID};
	}

}
