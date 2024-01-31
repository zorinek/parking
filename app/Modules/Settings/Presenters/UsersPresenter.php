<?php

declare(strict_types=1);

namespace App\Modules\Settings;

use App\Forms;
use App\Model;
use Nette\Application\UI\Form;
use Nette\Application\Responses\FileResponse;

final class UsersPresenter extends \App\Presenters\BaseProtectedPresenter
{

	/** @var Model\PreviewFactory $previewFactory @inject */
	public $previewFactory;

	/** @var Model\ExportsFactory $exportsFactory @inject */
	public $exportsFactory;

	/** @var Forms\UpdateUserFormFactory @inject */
	public $updateUserFactory;

	/** @var Forms\ManagementFormFactory @inject */
	public $managementFactory;

	/** @var Forms\NewUserFormFactory @inject */
	public $newUserFactory;

	/** @var Model\Users @inject */
	public $users;

	/** @var Model\Otp @inject */
	public $otp;

	/** @var Model\Roles @inject */
	public $roles;

	/** @persistent */
	public $use_id = "";

	/** @persistent */
	public $use_email = "";

	/** @persistent */
	public $use_name = "";

	/** @persistent */
	public $use_phone = "";

	/** @persistent */
	public $use_role = "";

	/** @persistent */
	public $use_active = "";

	/** @persistent */
	public $and_or = "and";

	/** @persistent */
	public $order = [];

	/** @persistent */
	public $order_dir = [];

	/** @persistent */
	public $export_cols = [];
	private $p_logreg;
	private $p_discussion;

	public function __construct($p_logreg, $p_discussion)
	{
		parent::__construct();
		$this->p_logreg = $p_logreg;
		$this->p_discussion = $p_discussion;
	}

	public function renderDefault()
	{
		
	}

	public function renderPreview($page)
	{
		$this->template->setFile("../app/Modules/components/default_preview.latte");
		$request = $this->getHttpRequest();

		$search_values = [
			["use_id", "like", $this->use_id],
			["use_email", "like", $this->use_email],
			["use_name", "like", $this->use_name],
			["use_phone", "like", $this->use_phone],
			$this->p_logreg['multiple_roles'] ? ["use_role", "like", $this->use_role, "users_roles", "usr_role", "Hu", "HUHU", "n", "use_id"] : ["use_role", "like", $this->use_role],
			["use_active", "like", $this->use_active, false, "users.use_active."],
			["and_or", "", $this->and_or],
			["order", "", $this->order],
			["order_dir", "", $this->order_dir]
		];
		$ret = $this->previewFactory->providePreview("users", $page, ":Settings:Users:preview", $request, $this->p_default['results_number'], $search_values, $this, 'users.admin.', "Settings/templates/Users/");
		$this->template->setParameters($ret);
		$this->template->search_values = $search_values;
		$this->template->search = empty($ret['params']) ? false : true;
		$this->template->parameters = $this->p_logreg;
		$this->template->exports = [
			"url" => ":Settings:Users:export"
		];
	}

	public function actionExport($type, $values)
	{
		$request = $this->getHttpRequest();
		$search_values = [
			["use_id", "like", $this->use_id],
			["use_email", "like", $this->use_email],
			["use_name", "like", $this->use_name],
			["use_phone", "like", $this->use_phone],
			$this->p_logreg['multiple_roles'] ? ["use_role", "like", $this->use_role, "users_roles", "usr_role", "Hu", "HUHU", "n", "use_id"] : ["use_role", "like", $this->use_role],
			["use_active", "like", $this->use_active, false, "users.use_active."],
			["and_or", "", $this->and_or],
			["order", "", $this->order],
			["order_dir", "", $this->order_dir]
		];
		$ret = $this->previewFactory->provideExport("users", $request, $search_values, false, $values);
		$export = $this->exportsFactory->export($ret, $search_values, $this->export_cols, $this, $type, 'users.admin.');

		$response = new FileResponse($export['tmpfile'], $export['filename'], $export['content_type']);
		$this->sendResponse($response);
	}

	public function renderDetail($use_id)
	{
		$user_data = $this->users->get($use_id);
		$this->template->name_enabled = $this->p_logreg['registration']['name'];
		$this->template->phone_enabled = $this->p_logreg['registration']['phone'];
		$this->template->two_factor_auth_enabled = $this->p_logreg['two_factor_auth_enabled'];
		$this->template->two_factor_auth_status = $this->p_logreg['two_factor_auth_status'];
		$this->template->user_activation_status = $this->p_logreg['user_activation_status'];
		$this->template->use_one_time_password = $this->p_logreg['use_one_time_password'];
		$this->template->p_discussion = $this->p_discussion;
		$this["updateUserForm"]->setDefaults($user_data);
		if ($this->p_logreg['multiple_roles'])
		{
			$this["updateUserForm"]->setDefaults(["use_role" => $this->roles->getRoles($use_id)]);
		}
		$this->template->user_data = $user_data;
		if ($this->p_logreg['use_one_time_password'])
		{
			$count_unused_otp_passwords = $this->otp->getUnusedCount($use_id);
			$this->template->count_unused_otp_passwords = $count_unused_otp_passwords;
		}
		$this->template->form_loc = "admin";
	}

	public function renderNew()
	{
		$this->template->p_logreg = $this->p_logreg;
	}

	public function actionActivate_user($use_id)
	{
		$vals = [];
		$vals[$this->users::COLUMN_USE_ID] = $use_id;
		$vals[$this->users::COLUMN_USE_ACTIVE] = $this->p_logreg['user_activation_status']['activated'];
		$this->users->update($vals);
		$this->redirect(":Settings:Users:preview", ["use_id" => ""]);
	}

	public function actionDeactivate_user($use_id)
	{
		$vals = [];
		$vals[$this->users::COLUMN_USE_ID] = $use_id;
		$vals[$this->users::COLUMN_USE_ACTIVE] = $this->p_logreg['user_activation_status']['deactivated'];
		$this->users->update($vals);
		$this->redirect(":Settings:Users:preview", ["use_id" => ""]);
	}

	/**
	 * Update User form factory.
	 */
	protected function createComponentUpdateUserForm(): Form
	{
		return $this->updateUserFactory->create(function (): void
				{
//                        $this->flashMessage($message);
//			$this->redirect(':Login:Login:login');
				}, "admin");
	}

	/**
	 * Management form factory.
	 */
	protected function createComponentManagementForm(): Form
	{
		return $this->managementFactory->create(function (): void
				{
//                        $this->flashMessage($message);
//			$this->redirect(':Login:Login:login');
				});
	}

	/**
	 * NewUser form factory.
	 */
	protected function createComponentNewUserForm(): Form
	{
		return $this->newUserFactory->create(function ($use_id): void
				{
//                        $this->flashMessage($message);
					$this->redirect(':Settings:Users:detail', [$use_id]);
				});
	}

}
