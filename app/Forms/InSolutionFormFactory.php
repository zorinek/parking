<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;

final class InSolutionFormFactory
{

	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var Model\CampaignsSegments */
	private $campaignsSegments;

	/** @var Nette\Localization\ITranslator */
	private $translator;

	public function __construct(FormFactory $factory, Nette\Localization\ITranslator $translator, Model\CampaignsSegments $campaignsSegments)
	{
		$this->factory = $factory;
		$this->translator = $translator;
		$this->campaignsSegments = $campaignsSegments;
	}

	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();

		$form->addSubmit('in_solution');
		$form->addSubmit('find_new_section');

		$form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void
		{
			try
			{
				$pro_id = $form->getPresenter()->getParameters()["pro_id"];
				$cam_id = $form->getPresenter()->getParameters()["cam_id"];
				$use_id = $form->getPresenter()->getUser()->id;
				$seg_id = 0;

				if ($form->isSubmitted()->getName() === "in_solution")
				{
					$seg_id = $form->getPresenter()->getParameters()["seg_id"];

					$vals = [];
					$vals[$this->campaignsSegments::COLUMN_CAM_ID] = $cam_id;
					$vals[$this->campaignsSegments::COLUMN_SEG_ID] = $seg_id;
					$vals[$this->campaignsSegments::COLUMN_CAS_DONE] = 2;
					$vals[$this->campaignsSegments::COLUMN_USE_ID] = $use_id;
					$vals[$this->campaignsSegments::COLUMN_CAS_DATETIME_RESERVATION] = date("Y-m-d H:i:s");
					$check = $this->campaignsSegments->checkReservation($vals);
					$check_user = $this->campaignsSegments->checkUserReservation($use_id, $cam_id);

					if (!$check)
					{
						throw new \Exception("Tato sekce je již rezervována jiným uživatelem! ");
					} 
					else
					{
						if (!$check_user)
						{
							$this->campaignsSegments->update($vals);
						} 
						else
						{
							throw new \Exception("Uživatel může mít pouze jednu rezervovanou sekci pro zpracování!");
						}
					}
					$onSuccess($pro_id, $cam_id, $seg_id);
				} 
				else if ($form->isSubmitted()->getName() === "find_new_section")
				{
					$seg_id = $this->campaignsSegments->getNextNotDoneSegment($cam_id);
					$onSuccess($pro_id, $cam_id, $seg_id->seg_id);
				}
			} 
			catch (\Nette\Application\AbortException $ex)
			{
				throw $ex;
			} 
			catch (\Exception $ex)
			{
				Debugger::log($ex);
				$form->addError($ex->getMessage());
				return;
			}
		};

		return $form;
	}

}
