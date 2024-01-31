<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;

final class RemoveReservationSegmentFormFactory
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

		$form->addSubmit('remove_reservation_segment');
		$form->addHidden('seg_id');
		$form->addHidden('cam_id');

		$form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void
		{
			try
			{
				$pro_id = isset($form->getPresenter()->getParameters()["pro_id"]) ? $form->getPresenter()->getParameters()["pro_id"] : null;
				$cam_id = $values->cam_id;
				$use_id = $form->getPresenter()->getUser()->id;
				$seg_id = $values->seg_id;

				$vals = [];
				$vals[$this->campaignsSegments::COLUMN_CAM_ID] = $cam_id;
				$vals[$this->campaignsSegments::COLUMN_SEG_ID] = $seg_id;
				$vals[$this->campaignsSegments::COLUMN_CAS_DONE] = 0;
				$vals[$this->campaignsSegments::COLUMN_USE_ID] = $use_id;
				$vals[$this->campaignsSegments::COLUMN_CAS_DATETIME_RESERVATION] = null;

				$check = $this->campaignsSegments->checkReservationValid($vals);

				if ($check && $check->use_id == $use_id)
				{
					$vals[$this->campaignsSegments::COLUMN_USE_ID] = null;
					$this->campaignsSegments->update($vals);
				}
				$onSuccess($pro_id);
			} 
			catch (\Nette\Application\AbortException $e)
			{
				throw $e;
			} 
			catch (\Exception $e)
			{
				Debugger::log($e);
				return;
			}
		};

		return $form;
	}

}
