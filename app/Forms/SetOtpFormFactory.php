<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;

final class SetOtpFormFactory
{

	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var Model\Otp */
	private $otp;
	private $p_logreg;

	public function __construct($p_logreg, FormFactory $factory, Model\Otp $otp)
	{
		$this->p_logreg = $p_logreg;
		$this->factory = $factory;
		$this->otp = $otp;
	}

	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();

		$form->addSubmit('enable_otp', 'Enable');
		$form->addSubmit('disable_otp', 'Skip');

		$form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void
		{

			$use_id = $form->getPresenter()->getSession()->getSection("verifed_user")->use_id;
			if ($form->isSubmitted()->getName() == "enable_otp")
			{
				$this->otp->update($use_id, $this->p_logreg['one_time_password_status']["activated"]);
				$message = "Jenorázové kódy pro přístup byly úspěšně vytvořeny! Pokračujte přihlášením.";
			} else if ($form->isSubmitted()->getName() == "disable_otp")
			{
				$this->otp->removeUnused($use_id, $this->p_logreg['one_time_password_status']["deactivated"]);
				$message = "Jednorázové kódy pro přístup nebyly vytvořeny! Doporučujeme je vytvořit po přihlášení!";
			}
			$onSuccess($message);
		};

		return $form;
	}

}
