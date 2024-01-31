<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;
use Nette\Utils\Random;
use Nette\Security\Passwords;
use Tracy\Debugger;

final class ManagementFormFactory
{

	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var Model\Users */
	private $users;

	/** @var Model\Otp */
	private $otp;

	/** @var Model\MailSender */
	private $mailSender;

	/** @var Model\Captcha */
	private $captcha;

	/** @var Passwords */
	private $passwords;
	private $p_logreg;
	private $p_discussion;

	public function __construct(
			$p_logreg,
			$p_discussion,
			FormFactory $factory,
			Model\Users $users,
			Model\Otp $otp,
			Model\MailSender $mailSender,
			Model\Captcha $captcha,
			Passwords $passwords
	)
	{
		$this->factory = $factory;
		$this->p_logreg = $p_logreg;
		$this->p_discussion = $p_discussion;
		$this->users = $users;
		$this->otp = $otp;
		$this->mailSender = $mailSender;
		$this->captcha = $captcha;
		$this->passwords = $passwords;
	}

	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();

		$form->addSubmit('send_reset_password_email', 'Reset Password');
		$form->addSubmit('activate_tfa', 'Activate tfa');
		$form->addSubmit('deactivate_tfa', 'Deactivate tfa');
		$form->addSubmit('activate_user', 'Activate user');
		$form->addSubmit('deactivate_user', 'Deactivate user');
		$form->addSubmit('remove_otp_passwords', 'Remove otp passwords');
		$form->addSubmit('add_discussion_authorization', 'Add discussion authorization');
		$form->addSubmit('remove_discussion_authorization', 'Remove discussion authorization');

		$form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void
		{
			try
			{
				$user_data = $this->users->get($form->getPresenter()->use_id);
				if ($form->isSubmitted()->getName() == "send_reset_password_email")
				{
					$vals = [];
					$vals[$this->users::COLUMN_USE_ID] = $form->getPresenter()->use_id;
					$vals[$this->users::COLUMN_USE_TOKEN_PASSWORD] = Random::generate($this->p_logreg['password_token_length']);
					$vals[$this->users::COLUMN_USE_TOKEN_EXPIRATION_PASSWORD] = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")) + $this->p_logreg['password_token_expiration']);
					$vals[$this->users::COLUMN_USE_PASSHASH] = $this->passwords->hash(Random::generate($this->p_logreg['reset_password_length']));
					$this->users->update($vals);
					$params = [
						'token' => $vals[$this->users::COLUMN_USE_TOKEN_PASSWORD],
						'token_expiration' => $vals[$this->users::COLUMN_USE_TOKEN_EXPIRATION_PASSWORD],
					];
					$this->mailSender->sendEmail(
							__DIR__ . '/../Modules/Login/templates/Login/reset_password_verification_email.latte',
							$params,
							$this->p_logreg['reset_password_email_sender'],
							$user_data->use_email
					);
				}
				if ($this->p_logreg['two_factor_auth_enabled'])
				{
					if ($form->isSubmitted()->getName() == "deactivate_tfa")
					{
						$vals = [];
						$vals[$this->users::COLUMN_USE_ID] = $form->getPresenter()->use_id;
						$vals[$this->users::COLUMN_USE_TFA_ENABLED] = $this->p_logreg['two_factor_auth_status']["disable"];
						$vals[$this->users::COLUMN_USE_TFA_SECRET] = NULL;
						$this->users->update($vals);
					}
				}
				if ($form->isSubmitted()->getName() == "activate_user")
				{
					$vals = [];
					$vals[$this->users::COLUMN_USE_ID] = $form->getPresenter()->use_id;
					$vals[$this->users::COLUMN_USE_ACTIVE] = $this->p_logreg['user_activation_status']["activated"];
					$this->users->update($vals);
				} 
				else if ($form->isSubmitted()->getName() == "deactivate_user")
				{
					$vals = [];
					$vals[$this->users::COLUMN_USE_ID] = $form->getPresenter()->use_id;
					$vals[$this->users::COLUMN_USE_ACTIVE] = $this->p_logreg['user_activation_status']["deactivated"];
					$this->users->update($vals);
				}
				
				if ($this->p_logreg['use_one_time_password'])
				{
					if ($form->isSubmitted()->getName() == "remove_otp_passwords")
					{
						$this->otp->removeAll($form->getPresenter()->use_id);
					}
				}
				if ($form->isSubmitted()->getName() == "add_discussion_authorization")
				{
					$vals = [];
					$vals[$this->users::COLUMN_USE_ID] = $form->getPresenter()->use_id;
					$vals[$this->users::COLUMN_USE_DISCUSSION_AUTHORIZED] = $this->p_discussion["user_authorized_status"]["enabled"];
					$this->users->update($vals);
				} 
				else if ($form->isSubmitted()->getName() == "remove_discussion_authorization")
				{
					$vals = [];
					$vals[$this->users::COLUMN_USE_ID] = $form->getPresenter()->use_id;
					$vals[$this->users::COLUMN_USE_DISCUSSION_AUTHORIZED] = $this->p_discussion["user_authorized_status"]["disabled"];
					$this->users->update($vals);
				}
			} 
			catch (\App\Exceptions\DuplicateEmailException $e)
			{
				Debugger::log($e);
				return;
			}
			$onSuccess();
		};

		return $form;
	}

}
