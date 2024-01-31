<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;
use Nette\Security\Passwords;
use Tracy\Debugger;

final class ResetPasswordAdminFormFactory
{

	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var Model\Users */
	private $users;

	/** @var User */
	private $user;

	/** @var Passwords */
	private $passwords;
	private $p_logreg;

	public function __construct($p_logreg, FormFactory $factory, Model\Users $users, User $user, Passwords $passwords)
	{
		$this->p_logreg = $p_logreg;
		$this->factory = $factory;
		$this->users = $users;
		$this->user = $user;
		$this->passwords = $passwords;
	}

	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();

		$form->addPassword("current_password")->setRequired();
		$form->addPassword('new_password', 'Create a password:')
				->setOption('description', sprintf('at least %d characters', $this->p_logreg['password_min_length']))
				->setRequired('Please create a password.')
				->addRule($form::MIN_LENGTH, null, $this->p_logreg['password_min_length'])
				->addRule('\App\Forms\CustomFormRules::validateCustomPassword', "Vaše heslo nesplňuje validační podmínky", $this->p_logreg['default_password_type']);

		$form->addSubmit('change_password', 'Change password');

		$form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void
		{
			try
			{
				$check = $this->users->checkPasswordAdmin($this->user->id, $values->current_password);
				$vals = [];
				$vals[$this->users::COLUMN_USE_ID] = $check->{$this->users::COLUMN_USE_ID};
				$vals[$this->users::COLUMN_USE_PASSHASH] = $this->passwords->hash($values->new_password);
				$this->users->update($vals);
				$onSuccess();
			} 
			catch (Nette\Security\AuthenticationException $e)
			{
				Debugger::log($e);
				$form["current_password"]->addError("Password is not valid!");
				return;
			}
		};

		return $form;
	}

}
