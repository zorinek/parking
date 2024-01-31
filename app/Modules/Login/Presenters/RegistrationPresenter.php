<?php

declare(strict_types=1);

namespace App\Modules\Login;

use App\Forms;
use App\Model;
use Nette\Application\UI\Form;

final class RegistrationPresenter extends \App\Presenters\BasePresenter
{

	/** @var Forms\RegistrationFormFactory @inject */
	public $registrationFactory;

	/** @var Forms\TfaFormFactory @inject */
	public $tfaFactory;

	/** @var Forms\VerificationEmailAgainFormFactory @inject */
	public $verificationEmailAgainFactory;

	/** @var Model\Users @inject */
	public $users;

	/** @var Model\Captcha @inject */
	public $captcha;
	private $p_logreg;

	public function __construct($p_logreg)
	{
		parent::__construct();
		$this->p_logreg = $p_logreg;
	}

	public function renderRegistration()
	{
		$this->template->name_enabled = $this->p_logreg['registration']['name'];
		$this->template->phone_enabled = $this->p_logreg['registration']['phone'];
		$this->template->terms_enabled = $this->p_logreg['registration']['terms'];
		$this->template->password_second = $this->p_logreg['password_second'];
		$this->template->use_captcha_registration = $this->p_logreg['use_captcha_registration'];
		if ($this->p_logreg['use_captcha_registration'])
		{
			$this->template->captcha = $this->captcha->generate(3, 2, 1);
		}
		$this->template->password_display = $this->p_logreg['password_display'];
	}

	public function renderVerification($token)
	{
		$check = $this->users->verifyToken($token);
		if (!is_null($check))
		{
			$now = new \DateTime();

			$token_date = $check->use_token_expiration_email;
			if ($now > $token_date) // token expired
			{
				$this->template->message = $this->p_logreg['email_verification_status']['expired'];
			} 
			else // token verifed
			{
				$this->template->message = 0;
				$this->users->verifyEmail($check->use_id, $this->p_logreg['email_verifed']);
				if ($this->p_logreg['two_factor_auth_enabled'])
				{
					$this->getSession()->getSection("verifed_user")->use_id = $check->use_id;
					$this->getSession()->getSection("verifed_user")->use_email = $check->use_email;
					$this->flashMessage($this->translator->translate('registration.verification.flash_email_valid_tfa'));
					$this->redirect(":Login:Registration:setTfa");
				} 
				else
				{
					$this->flashMessage($this->translator->translate('registration.verification.flash_email_valid'));
					$this->redirect(":Login:Login:login");
				}
			}
		} 
		else //token not found
		{
			$this->template->message = $this->p_logreg['email_verification_status']['not_found'];
		}
	}

	public function renderSetTfa()
	{

		$verifed_user = $this->getSession()->getSection("verifed_user");

		if (!is_null($verifed_user->use_email))
		{
			$g = new \Sonata\GoogleAuthenticator\GoogleAuthenticator();
			$use_tfa_secret = $g->generateSecret();

			$qr = \Sonata\GoogleAuthenticator\GoogleQrUrl::generate($verifed_user->use_email, $use_tfa_secret, $this->p_logreg['google_authenticator_name']);
			$this->template->qr = $qr;
			$this->template->code = $use_tfa_secret;
			$this->users->updateTfa($verifed_user->use_id, $use_tfa_secret, $this->p_logreg['two_factor_auth_enabled']);
		} 
		else
		{
			$this->template->qr = "";
			$this->template->code = "";
			$this->redirect(":Login:Registration:registration");
		}
	}

	public function renderSendVerificationEmailAgain()
	{
		
	}

	/**
	 * Registration form factory.
	 */
	protected function createComponentRegistrationForm(): Form
	{
		return $this->registrationFactory->create(function ($use_id, $use_email): void
		{

			if ($this->p_logreg['send_email_verification'])
			{
				$this->redirect(':Login:Registration:registrationSuccessfull');
			} else
			{
				if ($this->p_logreg['two_factor_auth_enabled'])
				{
					$section = $this->getSession()->getSection("verifed_user");
					$section->use_id = $use_id;
					$section->use_email = $use_email;
					$this->redirect(":Login:Registration:setTfa");
				} 
				else
				{
					if ($this->p_logreg['use_one_time_password'])
					{
						$section = $this->getSession()->getSection("verifed_user");
						$section->use_id = $use_id;
						$this->redirect(":Login:Otp:create");
					} 
					else
					{
						$this->flashMessage($this->translator->translate('registration.registration.flash_success'), 'success');
						$this->redirect(':Login:Login:login');
					}
				}
			}
		});
	}

	/**
	 * Tfa form factory.
	 */
	protected function createComponentTfaForm(): Form
	{
		return $this->tfaFactory->create(function ($tfa_status, $use_id): void
		{

			if ($this->p_logreg['use_one_time_password'])
			{
				if ($tfa_status == $this->p_logreg['two_factor_auth_status']["enable"])
				{
					$this->flashMessage($this->translator->translate('registration.tfa.tfa_enabled_otp'));
				} 
				else if ($tfa_status == $this->p_logreg['two_factor_auth_status']["disable"])
				{
					$this->flashMessage($this->translator->translate('registration.tfa.tfa_disabled_otp'));
				}
				$section = $this->getSession()->getSection("verifed_user");
				$section->use_id = $use_id;
				$this->redirect(":Login:Otp:create");
			} 
			else
			{
				if ($tfa_status == $this->p_logreg['two_factor_auth_status']["enable"])
				{
					$this->flashMessage($this->translator->translate('registration.tfa.tfa_enabled_login'));
				} 
				else if ($tfa_status == $this->p_logreg['two_factor_auth_status']["disable"])
				{
					$this->flashMessage($this->translator->translate('registration.tfa.tfa_disabled_login'));
				}
				$this->redirect(':Login:Login:login');
			}
		});
	}

	/**
	 * VerificationEmailAgain form factory.
	 */
	protected function createComponentVerificationEmailAgainForm(): Form
	{
		return $this->verificationEmailAgainFactory->create(function (): void
		{
			$this->redirect(':Login:Login:login');
		});
	}

}
