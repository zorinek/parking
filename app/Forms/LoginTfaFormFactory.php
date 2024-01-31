<?php

declare(strict_types=1);

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;
use Tracy\Debugger;

final class LoginTfaFormFactory
{

	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var User */
	private $user;
	private $session;

	public function __construct(FormFactory $factory, User $user, Nette\Http\Session $session)
	{
		$this->factory = $factory;
		$this->user = $user;
		$this->session = $session;
	}

	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();
		$form->addText('tfa_otp', 'Kód')
				->setRequired('Please enter your code.');

		$form->addSubmit('sign_in_tfa', 'Sign in');

		$form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void
		{
			try
			{
				$g = new \Sonata\GoogleAuthenticator\GoogleAuthenticator();
				$secret = $this->session->getSection("user")->use_tfa_secret;

				if ($g->checkCode($secret, $values->tfa_otp) && isset($this->session->getSection("user")->use_email))
				{
					$this->user->setExpiration($this->session->getSection("user")->remember ? '14 days' : '20 minutes');
					$this->user->login($this->session->getSection("user")->use_email, $this->session->getSection("user")->use_passhash);
					$this->session->getSection("user")->remove();
				} 
				else
				{
					if (isset($this->session->getSection("user")->count))
					{
						$this->session->getSection("user")->count++;
					} 
					else
					{
						$this->session->getSection("user")->count = 1;
					}

					throw new Nette\Security\AuthenticationException();
				}
			} 
			catch (Nette\Security\AuthenticationException $e)
			{
				Debugger::log($e);
				$form->addError('The code id not valid!');
				if ($this->session->getSection("user")->count > 3)
				{
					$this->session->getSection("user")->remove();
					$form->getPresenter()->redirect(":Login:Login:login");
				}                    
				return;
			}
			$onSuccess();
		};

		return $form;
	}

}
