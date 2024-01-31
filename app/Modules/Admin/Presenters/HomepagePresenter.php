<?php

declare(strict_types=1);

namespace App\Modules\Admin;

use App\Model;
use App\Forms;
use Nette\Application\UI\Form;

final class HomepagePresenter extends \App\Presenters\BaseProtectedPresenter
{

	/** @var Model\Users $users @inject */
	public $users;

	/** @var Model\Otp $users @inject */
	public $otp;

	/** @var Model\Projects $projects @inject */
	public $projects;

	/** @var Model\CampaignsSegments $campaignsSegments @inject */
	public $campaignsSegments;

	/** @var Forms\RemoveReservationSegmentFormFactory $removeReservationSegmentFactory @inject */
	public $removeReservationSegmentFactory;
	private $p_logreg;

	public function __construct($p_logreg)
	{
		$this->p_logreg = $p_logreg;
	}

	public function renderDefault()
	{
		if ($this->p_logreg['first_login_modal_enabled'])
		{
			$user_data = $this->users->get($this->user->id);
			if ($user_data["use_first_login"] == $this->p_logreg['first_login_status']["notlogged"])
			{
				$this->users->updateFirstLogin($this->user->id, $this->p_logreg['first_login_status']["logged"]);
				$this->template->two_factor_auth_enabled = $this->p_logreg['two_factor_auth_enabled'];
				$this->template->two_factor_auth_status = $this->p_logreg['two_factor_auth_status'];
			}
			$this->template->user_data = $user_data;

			if ($this->p_logreg['use_one_time_password'])
			{
				if ($user_data["use_first_login"] == $this->p_logreg['first_login_status']["notlogged"])
				{
					$otp_data = $this->otp->check($this->user->id, 1);
					$this->template->otp_data = $otp_data;
					$this->template->use_one_time_password = $this->p_logreg['use_one_time_password'];
				}
			}
		}
		$this->template->first_login_modal_enabled = $this->p_logreg['first_login_modal_enabled'];
		$this->template->first_login_status = $this->p_logreg['first_login_status'];

		$last_projects = $this->projects->getLastProjects();
		$this->template->last_projects = $last_projects;

		$my_reservations = $this->campaignsSegments->getMyReservationsAll($this->user->id);
		$this->template->my_reservations = $my_reservations;
	}

	public function renderHelp()
	{
		
	}

	/**
	 * Remove reservation segment form factory.
	 */
	protected function createComponentRemoveReservationSegmentForm(): Form
	{
		return $this->removeReservationSegmentFactory->create(function ($pro_id): void
				{

					$this->redirect(':Admin:Homepage:default');
				});
	}

}
