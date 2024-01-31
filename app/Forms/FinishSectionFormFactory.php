<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;

final class FinishSectionFormFactory
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

		$form->addHidden("illegal_left");
		$form->addHidden("illegal_right");
		$form->addHidden("empty_left");
		$form->addHidden("empty_right");
		$form->addHidden("not_left");
		$form->addHidden("not_right");

		$form->addHidden("illegal");
		$form->addHidden("empty");
		$form->addHidden("not");

		$form->addSubmit('next_section');
		$form->addSubmit('back_to_map');

		$form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void
		{
			try
			{
				$pro_id = $form->getPresenter()->getParameters()["pro_id"];
				$cam_id = $form->getPresenter()->getParameters()["cam_id"];
				
				$vals = [];
				$vals[$this->campaignsSegments::COLUMN_CAM_ID] = $cam_id;
				$vals[$this->campaignsSegments::COLUMN_SEG_ID] = $form->getPresenter()->getParameters()["seg_id"];
				if (empty($values->illegal) && empty($values->empty) && empty($values->not))
				{
					$vals[$this->campaignsSegments::COLUMN_CAS_PARKINGILLEGAL_LEFT] = (int) $values->illegal_left;
					$vals[$this->campaignsSegments::COLUMN_CAS_PARKINGILLEGAL_RIGHT] = (int) $values->illegal_right;
					$vals[$this->campaignsSegments::COLUMN_CAS_PARKINGILLEGAL] = (int) $values->illegal_left + (int) $values->illegal_right;
					$vals[$this->campaignsSegments::COLUMN_CAS_PARKINGFREE_LEFT] = (int) $values->empty_left;
					$vals[$this->campaignsSegments::COLUMN_CAS_PARKINGFREE_RIGHT] = (int) $values->empty_right;
					$vals[$this->campaignsSegments::COLUMN_CAS_PARKINGFREE] = (int) $values->empty_left + (int) $values->empty_right;
					$vals[$this->campaignsSegments::COLUMN_CAS_PARKINGNOTDETECTED_LEFT] = (int) $values->not_left;
					$vals[$this->campaignsSegments::COLUMN_CAS_PARKINGNOTDETECTED_RIGHT] = (int) $values->not_right;
					$vals[$this->campaignsSegments::COLUMN_CAS_PARKINGNOTDETECTED] = (int) $values->not_left + (int) $values->not_right;
				} 
				else
				{
					$vals[$this->campaignsSegments::COLUMN_CAS_PARKINGILLEGAL] = (int) $values->illegal;
					$vals[$this->campaignsSegments::COLUMN_CAS_PARKINGFREE] = (int) $values->empty;
					$vals[$this->campaignsSegments::COLUMN_CAS_PARKINGNOTDETECTED] = (int) $values->not;
				}

				$vals[$this->campaignsSegments::COLUMN_CAS_DONE] = 1;
				$vals[$this->campaignsSegments::COLUMN_USE_ID] = $form->getPresenter()->getUser()->id;

				$check = $this->campaignsSegments->checkReservationValid($vals);
				if ($check)
				{
					$this->campaignsSegments->update($vals);
				}

				if ($form->isSubmitted()->getName() === "next_section")
				{
					$next_segment = $this->campaignsSegments->getNextNotDoneSegment($cam_id);
					$onSuccess($pro_id, $cam_id, $next_segment->seg_id);
				} 
				else if ($form->isSubmitted()->getName() === "back_to_map")
				{
					$onSuccess($pro_id, $cam_id, null);
				}
			} 
			catch (\Exception $e)
			{
				Debugger::log($ex);
				return;
			}
		};

		return $form;
	}

}
