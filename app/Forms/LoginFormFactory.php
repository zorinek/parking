<?php

declare(strict_types=1);

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;
use App\Model;
use Tracy\Debugger;

final class LoginFormFactory
{

	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var User */
	private $user;

	/** @var Model\Users */
	private $users;

	/** @var Nette\Localization\ITranslator */
	private $translator;
	private $session;
	private $p_logreg;

	public function __construct($p_logreg, FormFactory $factory, User $user, Model\Users $users, Nette\Http\Session $session, Nette\Localization\ITranslator $translator)
	{
		$this->factory = $factory;
		$this->p_logreg = $p_logreg;
		$this->user = $user;
		$this->users = $users;
		$this->session = $session;
		$this->translator = $translator;
	}

	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();
		$form->setTranslator($this->translator);
		$form->addEmail('use_email')->setRequired('login.login.error_use_email');
		$form->addPassword('use_passhash')->setRequired('login.login.error_use_passhash');
		$form->addCheckbox('remember');

		$form->addSubmit('sign_in');

		$form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void
		{
			try
			{

				$result = $this->users->checkUser($values->use_email, $values->use_passhash);
				if (!is_null($result) && $result->{$this->users::COLUMN_USE_ACTIVE} != $this->p_logreg['user_activation_status']['activated'])
				{
					throw new \App\Exceptions\AccountDeactivatedException;
				} 
				elseif (!is_null($result) && $result->use_tfa_enabled == $this->p_logreg['two_factor_auth_status']['enable'])
				{
					$section = $this->session->getSection('user');
					$section->use_email = $values->use_email;
					$section->use_passhash = $values->use_passhash;
					$section->remember = $values->remember;
					$section->use_tfa_secret = $result->use_tfa_secret;
					$form->getPresenter()->redirect(":Login:Login:tfa");
				} 
				else if (!is_null($result) && $result->use_tfa_enabled == $this->p_logreg['two_factor_auth_status']['disable'])
				{
					$this->user->setExpiration($values->remember ? '14 days' : '20 minutes');
					$this->user->login($values->use_email, $values->use_passhash);
				}
			} 
			catch (Nette\Security\AuthenticationException $e)
			{
				Debugger::log($e);
				$form->addError('login.login.error_credentials');
				return;
			} 
			catch (\App\Exceptions\EmailNotValidatedException $e)
			{
				Debugger::log($e);
				$form->addError('1');
				return;
			} 
			catch (\App\Exceptions\AccountDeactivatedException $e)
			{
				Debugger::log($e);
				$form->addError('login.login.error_account_not_active');
				return;
			}
			catch (\Exception $e)
			{
				Debugger::log($e);
				return;
			}

			$onSuccess();
		};

		return $form;
	}

}
