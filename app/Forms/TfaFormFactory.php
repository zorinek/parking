<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;

final class TfaFormFactory
{

	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var Model\Users */
	private $users;
	private $p_logreg;

	public function __construct($p_logreg, FormFactory $factory, Model\Users $users)
	{
		$this->factory = $factory;
		$this->users = $users;
		$this->p_logreg = $p_logreg;
	}

	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();

		$form->addSubmit('enable_tfa', 'Enable');
		$form->addSubmit('skip_tfa', 'Skip');

		$form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void
		{

			$use_id = $form->getPresenter()->getSession()->getSection("verifed_user")->use_id;
			if ($form->isSubmitted()->getName() == "enable_tfa")
			{
				$this->users->changeTfa($use_id, $this->p_logreg['two_factor_auth_status']['enable']);
				$tfa_status = $this->p_logreg['two_factor_auth_status']['enable'];
			} else if ($form->isSubmitted()->getName() == "skip_tfa")
			{
				$this->users->changeTfa($use_id, $this->p_logreg['two_factor_auth_status']['disable']);
				$tfa_status = $this->p_logreg['two_factor_auth_status']['disable'];
			}
			$onSuccess($tfa_status, $use_id);
		};

		return $form;
	}

}
