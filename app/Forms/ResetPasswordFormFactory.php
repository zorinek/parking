<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;
use Nette\Utils\Random;
use Nette\Security\Passwords;
use Tracy\Debugger;

final class ResetPasswordFormFactory
{

	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var Model\Users */
	private $users;

	/** @var Model\MailSender */
	private $mailSender;

	/** @var Passwords */
	private $passwords;

	/** @var Nette\Localization\ITranslator */
	private $translator;
	private $p_logreg;

	public function __construct($p_logreg, FormFactory $factory, Model\Users $users, Model\MailSender $mailSender, Passwords $passwords, Nette\Localization\ITranslator $translator)
	{
		$this->factory = $factory;
		$this->p_logreg = $p_logreg;
		$this->users = $users;
		$this->mailSender = $mailSender;
		$this->passwords = $passwords;
		$this->translator = $translator;
	}

	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();
		$form->setTranslator($this->translator);
		$form->addEmail('use_email')->setRequired('login.reset.error_use_email');

		$form->addSubmit('reset');

		$form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void
		{
			try
			{
				$check = $this->users->checkEmail($values->use_email);
				if (!is_null($check))
				{
					$vals = [];
					$vals[$this->users::COLUMN_USE_ID] = $check->{$this->users::COLUMN_USE_ID};
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
							$values->use_email
					);
				} 
				else
				{
					throw new \App\Exceptions\NotFoundEmailException;
				}
				$onSuccess();
			} 
			catch (\App\Exceptions\NotFoundEmailException $e)
			{
				Debugger::log($e);
				$form[$this->users::COLUMN_USE_EMAIL]->addError('login.reset.error_email_not_found');
				return;
			}
		};

		return $form;
	}

}
