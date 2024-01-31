<?php

declare(strict_types=1);

namespace App\Modules\Login;

use Nette\Application\UI\Form;
use App\Forms;
use App\Model;

final class LoginPresenter extends \App\Presenters\BasePresenter
{

	/** @persistent */
	public $backlink = '';

	/** @var Forms\LoginFormFactory @inject */
	public $loginFactory;

	/** @var Forms\LoginTfaFormFactory @inject */
	public $loginTfaFactory;

	/** @var Forms\ResetPasswordFormFactory @inject */
	public $resetPasswordFactory;

	/** @var Forms\SetNewPasswordFormFactory @inject */
	public $setNewPasswordFactory;

	/** @var Model\Users @inject */
	public $users;
	private $p_logreg;

	public function __construct($p_logreg)
	{
		parent::__construct();
		$this->p_logreg = $p_logreg;
	}

	public function renderLogin()
	{
		$this->template->disable_password_change = $this->p_logreg['disable_password_change'];
		$this->template->password_display = $this->p_logreg['password_display'];
	}

	public function renderResetVerification($token)
	{
		if ($this->p_logreg['disable_password_change'])
		{
			$this->redirect(":Login:Login:login");
		}

		$check = $this->users->verifyTokenPassword($token);
		if (!is_null($check))
		{
			$now = new \DateTime();

			$token_date = $check->use_token_expiration_password;
			if ($now > $token_date) // token expired
			{
				$this->template->message = $this->p_logreg['password_verification_status']['expired'];
			} 
			else // token verifed
			{
				$this->template->message = 0;
				$this->template->password_second = $this->p_logreg['password_second'];
			}
		} 
		else //token not found
		{
			$this->template->message = $this->p_logreg['password_verification_status']['not_found'];
		}
		$this->template->password_display = $this->p_logreg['password_display'];
	}

	public function actionLogout(): void
	{
		$this->getUser()->logout(true);
		$this->redirect(":Login:Login:login");
	}

	public function renderResetPassword()
	{
		if ($this->p_logreg['disable_password_change'])
		{
			$this->redirect(":Login:Login:login");
		}
	}

	public function renderResetSuccessfull()
	{
		if ($this->p_logreg['disable_password_change'])
		{
			$this->redirect(":Login:Login:login");
		}
	}

	/**
	 * Login form factory.
	 */
	protected function createComponentLoginForm(): Form
	{
		return $this->loginFactory->create(function (): void
		{
			$this->redirect(':Admin:Homepage:default');
		});
	}

	/**
	 * Login-tfa form factory.
	 */
	protected function createComponentLoginTfaForm(): Form
	{
		return $this->loginTfaFactory->create(function (): void
		{
			$this->restoreRequest($this->backlink);
			$this->redirect(':Admin:Homepage:default');
		});
	}

	/**
	 * ResetPassword form factory.
	 */
	protected function createComponentResetPasswordForm(): Form
	{
		return $this->resetPasswordFactory->create(function (): void
		{
			$this->redirect(':Login:Login:resetSuccessfull');
		});
	}

	/**
	 * SetNewPassword form factory.
	 */
	protected function createComponentSetNewPasswordForm(): Form
	{
		return $this->setNewPasswordFactory->create(function (): void
		{
			$this->redirect(':Login:Login:login');
		});
	}

}
