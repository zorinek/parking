<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;

final class ManagementProfileFormFactory
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
	private $p_logreg;

	public function __construct(
			$p_logreg,
			FormFactory $factory,
			Model\Users $users,
			Model\Otp $otp,
			Model\MailSender $mailSender,
			Model\Captcha $captcha
	)
	{
		$this->factory = $factory;
		$this->p_logreg = $p_logreg;
		$this->users = $users;
		$this->otp = $otp;
		$this->mailSender = $mailSender;
		$this->captcha = $captcha;
	}

	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();

		$form->addSubmit('activate_tfa', 'Activate tfa');
		$form->addSubmit('deactivate_tfa', 'Deactivate tfa');
		$form->addSubmit('remove_otp_passwords', 'Remove otp passwords');
		$form->addSubmit('generate_otp_passwords', 'Add otp passwords');

		$form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void
		{
			try
			{
				if ($this->p_logreg['two_factor_auth_enabled'])
				{
					if ($form->isSubmitted()->getName() == "deactivate_tfa")
					{
						$vals = [];
						$vals[$this->users::COLUMN_USE_ID] = $form->getPresenter()->user->id;
						$vals[$this->users::COLUMN_USE_TFA_ENABLED] = $this->p_logreg['two_factor_auth_status']["disable"];
						$vals[$this->users::COLUMN_USE_TFA_SECRET] = NULL;
						$this->users->update($vals);
					} 
					else if ($form->isSubmitted()->getName() == "activate_tfa")
					{
						$vals = [];
						$vals[$this->users::COLUMN_USE_ID] = $form->getPresenter()->user->id;
						$vals[$this->users::COLUMN_USE_TFA_ENABLED] = $this->p_logreg['two_factor_auth_status']["enable"];
						$this->users->update($vals);
					}
				}

				if ($this->p_logreg['use_one_time_password'])
				{
					if ($form->isSubmitted()->getName() == "remove_otp_passwords")
					{
						$this->otp->removeAll($form->getPresenter()->user->id);
					} 
					else if ($form->isSubmitted()->getName() == "generate_otp_passwords")
					{
						$this->otp->update($form->getPresenter()->user->id, $this->p_logreg['one_time_password_status']["activated"]);
					}
				}
			} 
			catch (\App\Exceptions\DuplicateEmailException $e)
			{
				Debugger::log($e);
			}
			$onSuccess();
		};

		return $form;
	}

}
