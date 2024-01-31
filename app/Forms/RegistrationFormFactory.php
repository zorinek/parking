<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;
use Nette\Utils\Random;
use Nette\Security\Passwords;
use Tracy\Debugger;

final class RegistrationFormFactory
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

	public function __construct(
			$p_logreg,
			$p_discussion,
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
		$form->addEmail('use_email')->setRequired('registration.registration.error_use_email');

		$form->addPassword('use_passhash')
				->setRequired('registration.registration.error_create_password')
				->addRule($form::MIN_LENGTH, sprintf('registration.registration.error_password_min_length', $this->p_logreg['password_min_length']), $this->p_logreg['password_min_length'])
				->addRule('\App\Forms\CustomFormRules::validateCustomPassword', "registration.registration.error_custom_validation", $this->p_logreg['default_password_type']);
		if ($this->p_logreg['password_second'])
		{
			$form->addPassword('use_passhash_second')
					->setRequired('registration.registration.error_password_same')
					->addRule($form::EQUAL, "registration.registration.error_password_same", $form['use_passhash']);
		}
		if ($this->p_logreg['registration']['name'])
		{
			$form->addText('use_name')->setRequired('registration.registration.error_use_name');
		}
		if ($this->p_logreg['registration']['phone'])
		{
			$form->addText('use_phone')->setRequired('registration.registration.error_use_phone');
		}
		if ($this->p_logreg['registration']['terms'])
		{
			$form->addCheckbox('use_terms_agreement')->setRequired("registration.registration.error_use_terms_agreement");
		}
		if ($this->p_logreg['use_captcha_registration'])
		{
			$form->addText('captcha')->setRequired('registration.registration.error_captcha');
			$form->addHidden("captcha_text");
		}
		$form->addSubmit('register');

		$form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void
		{
			try
			{
				if ($this->p_logreg['password_second'])
				{
					if ($values->use_passhash != $values->use_passhash_second)
					{
						throw new \App\Exceptions\NotSamePasswordException;
					}
				}
				$vals = [];
				$vals[$this->users::COLUMN_USE_EMAIL] = $values->use_email;
				$vals[$this->users::COLUMN_USE_PASSHASH] = $this->passwords->hash($values->use_passhash);
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
				if ($this->p_logreg['send_email_verification'])
				{
					$vals[$this->users::COLUMN_USE_TOKEN_EMAIL] = Random::generate($this->p_logreg['registration_token_length']);
					$vals[$this->users::COLUMN_USE_TOKEN_EXPIRATION_EMAIL] = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")) + $this->p_logreg['registration_token_expiration']);
				}

				if (!$this->p_logreg['multiple_roles'])
				{
					$vals[$this->users::COLUMN_USE_ROLE] = $this->p_logreg['roles_types'][$this->p_logreg['default_role']];
				}

				$vals[$this->users::COLUMN_USE_ACTIVE] = $this->p_logreg['default_user_activation'];
				$vals[$this->users::COLUMN_USE_FIRST_LOGIN] = $this->p_logreg['first_login_status']["notlogged"];

				$vals[$this->users::COLUMN_USE_DISCUSSION_AUTHORIZED] = $this->p_discussion["user_authorized_status"][$this->p_discussion["default_user_authorized"]];

				if ($this->p_logreg['use_captcha_registration'])
				{
					$text_value = $this->captcha->decode($values->captcha_text);
					if ($text_value != $values->captcha)
					{
						throw new \App\Exceptions\InvalidCaptchaException;
					}
				}

				$id = $this->users->add($vals);
				if ($this->p_logreg['multiple_roles'])
				{
					$this->roles->insert($id, $this->p_logreg['roles_types'][$this->p_logreg['default_role']]);
				}
				if ($this->p_logreg['send_email_verification'])
				{
					$params = [
						'token' => $vals[$this->users::COLUMN_USE_TOKEN_EMAIL],
						'token_expiration' => $vals[$this->users::COLUMN_USE_TOKEN_EXPIRATION_EMAIL],
					];
					$this->mailSender->sendEmail(
							__DIR__ . '/../Modules/Login/templates/Registration/registration_verification_email.latte',
							$params,
							$this->p_logreg['registration_email_sender'],
							$values->use_email
					);
				}
				$onSuccess($id, $vals[$this->users::COLUMN_USE_EMAIL]);
			} 
			catch (\App\Exceptions\DuplicateEmailException $e)
			{
				Debugger::log($e);
				$form[$this->users::COLUMN_USE_EMAIL]->addError('registration.registration.error_email_is_taken');
				return;
			} 
			catch (\App\Exceptions\NotSamePasswordException $e)
			{
				Debugger::log($e);
				$form[$this->users::COLUMN_USE_PASSHASH]->addError('registration.registration.error_passwords_not_same');
				return;
			} 
			catch (\App\Exceptions\InvalidCaptchaException $e)
			{
				Debugger::log($e);
				$form["captcha"]->addError('registration.registration.error_captcha_not_valid');
				return;
			}
			catch (\Exception $e)
			{
				Debugger::log($e);
			}
		};

		return $form;
	}

}
