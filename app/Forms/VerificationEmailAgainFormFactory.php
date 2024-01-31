<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;
use Nette\Utils\Random;
use Tracy\Debugger;

final class VerificationEmailAgainFormFactory
{

	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var Model\Users */
	private $users;

	/** @var Model\MailSender */
	private $mailSender;

	/** @var Nette\Localization\ITranslator */
	private $translator;
	private $p_logreg;

	public function __construct($p_logreg, FormFactory $factory, Model\Users $users, Model\MailSender $mailSender, Nette\Localization\ITranslator $translator)
	{
		$this->p_logreg = $p_logreg;
		$this->factory = $factory;
		$this->users = $users;
		$this->mailSender = $mailSender;
		$this->translator = $translator;
	}

	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();
		$form->setTranslator($this->translator);
		$form->addEmail('use_email')->setRequired('registration.again.error_use_email');

		$form->addSubmit('send_verification_again');

		$form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void
		{
			try
			{
				$check = $this->users->checkEmail($values->use_email);
				if (!is_null($check))
				{
					$vals = [];
					$vals[$this->users::COLUMN_USE_ID] = $check->use_id;

					$vals[$this->users::COLUMN_USE_TOKEN_EMAIL] = Random::generate($this->p_logreg['registration_token_length']);
					$vals[$this->users::COLUMN_USE_TOKEN_EXPIRATION_EMAIL] = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")) + $this->p_logreg['registration_token_expiration']);

					$this->users->update($vals);
					$params = [
						'token' => $vals[$this->users::COLUMN_USE_TOKEN_EMAIL],
						'token_expiration' => $vals[$this->users::COLUMN_USE_TOKEN_EXPIRATION_EMAIL],
					];
					$this->mailSender->sendEmail(
							__DIR__ . '/../Modules/Login/templates/Registration/registration_verification_email.latte',
							$params,
							$this->p_logreg['registration_email_sender'],
							$check->use_email
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
				$form[$this->users::COLUMN_USE_EMAIL]->addError('registration.again.error_email_not_found');
				return;
			}
		};

		return $form;
	}

}
