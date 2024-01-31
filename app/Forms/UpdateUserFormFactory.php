<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;
use Nette\Security\Passwords;
use Tracy\Debugger;

final class UpdateUserFormFactory
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
	private $form_loc;

	public function __construct(
			$p_logreg,
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
		$this->users = $users;
		$this->mailSender = $mailSender;
		$this->captcha = $captcha;
		$this->roles = $roles;
		$this->passwords = $passwords;
		$this->translator = $translator;
	}

	public function create(callable $onSuccess, $form_loc): Form
	{
		$form = $this->factory->create();
		$form->setTranslator($this->translator);
		if ($form_loc == "admin")
		{
			$form->addEmail('use_email')->setRequired('users.edituser.error_use_email');
		} 
		else if ($form_loc == "profile")
		{
			$form->addEmail('use_email');
		}

		if ($this->p_logreg['registration']['name'])
		{
			$form->addText('use_name')->setRequired('users.edituser.error_use_name');
		}
		if ($this->p_logreg['registration']['phone'])
		{
			$form->addText('use_phone')->setRequired('users.edituser.error_use_phone');
		}
		if ($form_loc == "admin")
		{
			if ($this->p_logreg['multiple_roles'])
			{
				$form->addMultiSelect("use_role", "Roles", array_flip($this->p_logreg['roles_types']))->setRequired('users.edituser.error_use_role');
			} 
			else
			{
				$form->addSelect("use_role", "Roles", array_flip($this->p_logreg['roles_types']))->setPrompt("users.edituser.notselected")->setRequired('users.edituser.error_use_role');
			}
			$form->addSelect("use_active", "Aktivace", $this->p_logreg['use_active'])->setPrompt("users.edituser.notselected")->setRequired('users.edituser.error_use_active');
		} 

		$form->addSubmit('save');
		$this->form_loc = $form_loc;
		$form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void
		{
			try
			{
				if ($this->form_loc == "admin")
				{
					$vals = [];
					$vals[$this->users::COLUMN_USE_ID] = $form->getPresenter()->use_id;
					$vals[$this->users::COLUMN_USE_EMAIL] = $values->use_email;

					if ($this->p_logreg['registration']['name'])
					{
						$vals[$this->users::COLUMN_USE_NAME] = $values->use_name;
					}
					if ($this->p_logreg['registration']['phone'])
					{
						$vals[$this->users::COLUMN_USE_PHONE] = $values->use_phone;
					}
					if ($this->p_logreg['multiple_roles'])
					{
						$this->roles->remove($vals[$this->users::COLUMN_USE_ID]);
						foreach ($values->use_role as $role)
						{
							$this->roles->insert($vals[$this->users::COLUMN_USE_ID], $role);
						}
					}
					else
					{
						$vals[$this->users::COLUMN_USE_ROLE] = $values->use_role;
					}
					$vals[$this->users::COLUMN_USE_ACTIVE] = $values->use_active;

					$this->users->update($vals);
				} 
				else if ($this->form_loc == "profile")
				{
					$vals = [];
					$vals[$this->users::COLUMN_USE_ID] = $form->getPresenter()->getUser()->id;

					if ($this->p_logreg['registration']['name'])
					{
						$vals[$this->users::COLUMN_USE_NAME] = $values->use_name;
					}
					if ($this->p_logreg['registration']['phone'])
					{
						$vals[$this->users::COLUMN_USE_PHONE] = $values->use_phone;
					}

					$this->users->update($vals);
				}
				$onSuccess();
			} 
			catch (\App\Exceptions\DuplicateEmailException $e)
			{
				Debugger::log($e);
				$form[$this->users::COLUMN_USE_EMAIL]->addError('users.edituser.error_email_exists');
				return;
			}

		};

		return $form;
	}

}
