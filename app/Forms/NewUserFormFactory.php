<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;
use Nette\Utils\Random;
use Nette\Security\Passwords;
use Tracy\Debugger;

final class NewUserFormFactory
{

	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var Model\Users */
	private $users;

	/** @var Model\MailSender */
	private $mailSender;

	/** @var Model\Captcha */
	private $captcha;

	/** @var Model\Roles */
	private $roles;

	/** @var Passwords */
	private $passwords;

	/** @var Nette\Localization\ITranslator */
	private $translator;
	private $p_logreg;
	private $p_discussion;
	private $p_default;

	public function __construct(
			$p_logreg,
			$p_discussion,
			$p_default,
			FormFactory $factory,
			Model\Users $users,
			Model\MailSender $mailSender,
			Model\Captcha $captcha,
			Model\Roles $roles,
			Passwords $passwords,
			Nette\Localization\ITranslator $translator
	)
	{
		$this->factory = $factory;
		$this->p_logreg = $p_logreg;
		$this->p_discussion = $p_discussion;
		$this->p_default = $p_default;
		$this->users = $users;
		$this->mailSender = $mailSender;
		$this->captcha = $captcha;
		$this->roles = $roles;
		$this->passwords = $passwords;
		$this->translator = $translator;
	}

	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();
		$form->setTranslator($this->translator);
		$form->addEmail('use_email')->setRequired('users.new.error_use_email');

		$form->addPassword('use_passhash');
//			->setRequired('users.new.error_create_password')
//			->addRule($form::MIN_LENGTH, sprintf('users.new.error_password_min_length', $this->p_logreg['password_min_length']), $this->p_logreg['password_min_length'])
//                        ->addRule('\App\Forms\CustomFormRules::validateCustomPassword', "users.new.error_custom_validation", $this->p_logreg['default_password_type']);

		if ($this->p_logreg['registration']['name'])
		{
			$form->addText('use_name')->setRequired('users.new.error_use_name');
		}
		if ($this->p_logreg['registration']['phone'])
		{
			$form->addText('use_phone')->setRequired('users.new.error_use_phone');
		}

		if ($this->p_logreg['multiple_roles'])
		{
			$form->addMultiSelect("use_role", "Roles", array_flip($this->p_logreg['roles_types']))->setRequired('users.new.error_use_role');
		} 
		else
		{
			$form->addSelect("use_role", "Roles", array_flip($this->p_logreg['roles_types']))->setPrompt("users.new.notselected")->setRequired('users.new.error_use_role');
		}
		$form->addSelect("use_active", "Aktivace", $this->p_logreg['use_active'])->setPrompt("users.new.notselected")->setRequired('users.new.error_use_active');

		$form->addCheckbox("use_send_pass_mail");

		$form->addSubmit('register');

		$form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void
		{
			try
			{
				$vals = [];
				$vals[$this->users::COLUMN_USE_EMAIL] = $values->use_email;
				if (empty($values->use_passhash))
				{
					$pass = Random::generate($this->p_logreg["password_min_length"]);
				} 
				else
				{
					$pass = $values->use_passhash;
				}
				$vals[$this->users::COLUMN_USE_PASSHASH] = $this->passwords->hash($pass);
				if ($this->p_logreg['registration']['name'])
				{
					$vals[$this->users::COLUMN_USE_NAME] = $values->use_name;
				}
				if ($this->p_logreg['registration']['phone'])
				{
					$vals[$this->users::COLUMN_USE_PHONE] = $values->use_phone;
				}
				if ($this->p_logreg['registration']['name'])
				{
					$vals[$this->users::COLUMN_USE_TERMS_AGREEMENT] = $values->use_terms_agreement;
				}

				if (!$this->p_logreg['multiple_roles'])
				{
					$vals[$this->users::COLUMN_USE_ROLE] = $this->p_logreg['roles_types'][$this->p_logreg['default_role']];
				}

				$vals[$this->users::COLUMN_USE_ACTIVE] = $values->use_active;
				$vals[$this->users::COLUMN_USE_FIRST_LOGIN] = $this->p_logreg['first_login_status']["notlogged"];

				$vals[$this->users::COLUMN_USE_DISCUSSION_AUTHORIZED] = $this->p_discussion["user_authorized_status"][$this->p_discussion["default_user_authorized"]];

				$check = $this->users->checkEmail($values->use_email);
				if ($check)
				{
					throw new \App\Exceptions\DuplicateEmailException();
				}
				$id = $this->users->add($vals);
				if ($this->p_logreg['multiple_roles'])
				{
					foreach ($values->use_role as $role)
					{
						$this->roles->insert($id, $role);
					}
				}
				if ($values->use_send_pass_mail)
				{
					$params = [
						'server' => $this->p_default['server_name'],
						'server_url' => $this->p_default['server_url'],
						'use_passhash' => $pass,
						'username' => $values->use_email
					];
					$this->mailSender->sendEmail(
							__DIR__ . '/../Modules/Settings/templates/Users/new_user_email.latte',
							$params,
							$this->p_logreg['registration_email_sender'],
							$values->use_email
					);
				}
			} 
			catch (\App\Exceptions\DuplicateEmailException $e)
			{
				Debugger::log($e);
				$form[$this->users::COLUMN_USE_EMAIL]->addError('users.new.error_email_is_taken');
				return;
			}
			$onSuccess($id, $vals[$this->users::COLUMN_USE_EMAIL]);
		};

		return $form;
	}

}
