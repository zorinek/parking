<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Modules\Settings;

/**
 * Description of AccountPresenter
 *
 * @author Kovarik
 */
use Nette;
use App\Model;
use App\Forms;
use Nette\Application\UI\Form;

class AccountPresenter extends \App\Presenters\BaseProtectedPresenter
{
	/** @var Forms\UpdateUserFormFactory @inject */
	public $updateUserFactory;

	/** @var Forms\AccountImageFormFactory @inject */
	public $accountImageFactory;

	/** @var Forms\ResetPasswordAdminFormFactory @inject */
	public $resetPasswordAdminFactory;

	/** @var Forms\ManagementProfileFormFactory @inject */
	public $managementProfileFactory;

	/** @var Model\Users @inject */
	public $users;

	/** @var Model\Otp @inject */
	public $otp;
	
	private $p_logreg;
	public $file_upload_dir;

	public function __construct($p_logreg)
	{
		parent::__construct();
		$this->p_logreg = $p_logreg;
	}

	public function renderProfile()
	{
		$user_data = $this->users->get($this->user->id);
		$this->template->name_enabled = $this->p_logreg['registration']['name'];
		$this->template->phone_enabled = $this->p_logreg['registration']['phone'];
		$this->template->two_factor_auth_enabled = $this->p_logreg['two_factor_auth_enabled'];
		$this->template->two_factor_auth_status = $this->p_logreg['two_factor_auth_status'];
		$this->template->use_one_time_password = $this->p_logreg['use_one_time_password'];

		$this["updateUserForm"]->setDefaults($user_data);

		$this->template->user_data = $user_data;
		$this->template->form_loc = "profile";
		$this->template->password_display = $this->p_logreg['password_display'];
		if ($this->p_logreg['use_one_time_password'])
		{
			$count_unused_otp_passwords = $this->otp->getUnusedCount($this->user->id);
			$this->template->count_unused_otp_passwords = $count_unused_otp_passwords;
		}

		if ($this->p_logreg['two_factor_auth_enabled'])
		{
			$g = new \Sonata\GoogleAuthenticator\GoogleAuthenticator();
			$use_tfa_secret = $g->generateSecret();

			$qr = \Sonata\GoogleAuthenticator\GoogleQrUrl::generate($user_data->use_email, $use_tfa_secret, $this->p_logreg['google_authenticator_name']);
			$this->template->qr = $qr;
			$this->template->code = $use_tfa_secret;
			if ($user_data->use_tfa_enabled == $this->p_logreg['two_factor_auth_status']["disable"])
			{
				$this->users->updateTfa($user_data->use_id, $use_tfa_secret, $this->p_logreg['two_factor_auth_enabled']);
			}
		}
		if ($this->p_logreg['use_one_time_password'])
		{
			$use_id = $this->user->id;
			$check_a = $this->otp->check($use_id, $this->p_logreg['one_time_password_status']["activated"]);

			if ($check_a)
			{
				$this->template->otp_active = true;
				$this->template->otp_pass = [];
			} 
			else
			{
				$this->template->otp_active = false;
				$this->otp->removeUnused($use_id, $this->p_logreg['one_time_password_status']["deactivated"]);
				$otp_res = $this->otp->generate();
				$this->otp->insert($use_id, date("Y-m-d H:i:s", strtotime("+ " . $this->p_logreg['one_time_password_expiration_days'] . " days")), $this->p_logreg['one_time_password_status']["deactivated"], $otp_res);
				$this->template->otp_pass = $otp_res;
			}
		}
	}

	public function actionImage()
	{
		$id = $this->user->id;
		if (is_file("../uploads/user_images/" . $id . ".jpg"))
		{
			$filename = "../uploads/user_images/" . $id . ".jpg";
		} 
		else
		{
			$filename = "../uploads/user_images/default.png";
		}
		$response = new Nette\Application\Responses\FileResponse($filename, NULL, FALSE);
		$this->sendResponse($response);
	}

	/**
	 * Update User form factory.
	 */
	protected function createComponentUpdateUserForm(): Form
	{
		return $this->updateUserFactory->create(function (): void
		{
			$this->redirect(":Settings:Account:profile");
		}, "profile");
	}

	/**
	 * Account image form factory.
	 */
	protected function createComponentAccountImageForm(): Form
	{
		$this->file_upload_dir = "user_images/";
		return $this->accountImageFactory->create(function (): void
		{
			$this->redirect(":Settings:Account:profile");
		});
	}

	/**
	 * Reset password admin form factory.
	 */
	protected function createComponentResetPasswordAdminForm(): Form
	{
		return $this->resetPasswordAdminFactory->create(function (): void
		{
			$this->redirect(':Settings:Account:profile');
		});
	}

	/**
	 * ManagementProfile form factory.
	 */
	protected function createComponentManagementProfileForm(): Form
	{
		return $this->managementProfileFactory->create(function (): void
		{
			$this->redirect(':Settings:Account:profile');
		});
	}

}
