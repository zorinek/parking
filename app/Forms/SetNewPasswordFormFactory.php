<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;
use Nette\Security\Passwords;
use Tracy\Debugger;

final class SetNewPasswordFormFactory
{

	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var Model\Users */
	private $users;

	/** @var Passwords */
	private $passwords;
	private $p_logreg;

	public function __construct($p_logreg, FormFactory $factory, Model\Users $users, Passwords $passwords)
	{
		$this->factory = $factory;
		$this->p_logreg = $p_logreg;
		$this->users = $users;
		$this->passwords = $passwords;
	}

	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();

		$form->addPassword('use_passhash')
				->setRequired('registration.set_new_password.error_create_password')
				->addRule($form::MIN_LENGTH, sprintf('registration.set_new_password.error_password_min_length', $this->p_logreg['password_min_length']), $this->p_logreg['password_min_length'])
				->addRule('\App\Forms\CustomFormRules::validateCustomPassword', "registration.set_new_password.error_custom_validation", $this->p_logreg['default_password_type']);
		if ($this->p_logreg['password_second'])
		{
			$form->addPassword('use_passhash_second')
					->setRequired('registration.set_new_password.error_password_same')
					->addRule($form::EQUAL, "registration.set_new_password.error_password_same", $form['use_passhash']);
		}

		$form->addSubmit('reset_password');

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
				$check = $this->users->verifyTokenPassword($form->getPresenter()->getParameters()['token']);
				if (!is_null($check))
				{
					$vals = [];
					$vals[$this->users::COLUMN_USE_ID] = $check->use_id;
					$vals[$this->users::COLUMN_USE_PASSHASH] = $this->passwords->hash($values->use_passhash);
					$vals[$this->users::COLUMN_USE_TOKEN_PASSWORD] = "";
					$vals[$this->users::COLUMN_USE_TOKEN_EXPIRATION_PASSWORD] = NULL;
					$this->users->update($vals);
					$form->getPresenter()->flashMessage("registration.set_new_password.flash_changed");
				} 
				else
				{
					$form->getPresenter()->redirect(":Login:Login:resetVerification", $form->getPresenter()->getParameters()['token']);
				}
				$onSuccess();
			} 
			catch (\App\Exceptions\NotSamePasswordException $e)
			{
				Debugger::log($e);
				$form[$this->users::COLUMN_USE_PASSHASH]->addError('registration.set_new_password.error_passwords_not_same');
				return;
			}
		};

		return $form;
	}

}
