<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;

final class NewProjectFormFactory
{

	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var Model\Projects */
	private $projects;

	/** @var Model\Campaigns */
	private $campaigns;

	/** @var Nette\Localization\ITranslator */
	private $translator;
	private $p_projects;
	private $param_proj = "p_projects";
	private $p_campaigns;
	private $param_camp = "p_campaigns";

	public function __construct($p_projects, $p_campaigns, FormFactory $factory, Nette\Localization\ITranslator $translator, Model\Projects $projects, Model\Campaigns $campaigns)
	{
		$this->p_projects = $p_projects;
		$this->p_campaigns = $p_campaigns;
		$this->factory = $factory;
		$this->translator = $translator;
		$this->projects = $projects;
		$this->campaigns = $campaigns;
	}

	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();
		$form->setTranslator($this->translator);
		$name = "pro_name";
		if ($this->{$this->param_proj}["displayed"][$name])
		{
			$field = $form->addText($name);
			if ($this->{$this->param_proj}["required"][$name])
			{
				$field->setRequired("projects.new.error_" . $name);
			}
		}
		$name = "pro_note";
		if ($this->{$this->param_proj}["displayed"][$name])
		{
			$field = $form->addTextArea($name);
			if ($this->{$this->param_proj}["required"][$name])
			{
				$field->setRequired("projects.new.error_" . $name);
			}
		}

		$name = "cam_name";
		if ($this->{$this->param_camp}["displayed"][$name])
		{
			$field = $form->addText($name);
			if ($this->{$this->param_camp}["required"][$name])
			{
				$field->setRequired("projects.new.error_" . $name);
			}
		}


		$form->addSubmit('insert_new_project');

		$form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void
		{
			try
			{
				$vals = [];
				$name = "pro_name";
				if ($this->{$this->param_proj}["displayed"][$name])
				{
					$vals[$this->projects::COLUMN_PRO_NAME] = $values->{$name};
				}
				$name = "pro_note";
				if ($this->{$this->param_proj}["displayed"][$name])
				{
					$vals[$this->projects::COLUMN_PRO_NOTE] = $values->{$name};
				}

				$vals[$this->projects::COLUMN_PRO_DATETIMEINSERT] = date("Y-m-d H:i:s");

				$pro_id = $this->projects->insert($vals);

				$vals = [];
				$vals[$this->campaigns::COLUMN_PRO_ID] = $pro_id;
				$name = "cam_name";
				if ($this->{$this->param_camp}["displayed"][$name])
				{
					$vals[$this->campaigns::COLUMN_CAM_NAME] = $values->{$name};
					$this->campaigns->insert($vals);

					foreach ($_POST[$this->campaigns::COLUMN_CAM_NAME . "_next"] as $line)
					{
						$vals[$this->campaigns::COLUMN_CAM_NAME] = $line;
						$this->campaigns->insert($vals);
					}
				}
				$onSuccess($pro_id);
			} catch (\Exception $e)
			{
				Debugger::log($e);
				return;
			}
		};

		return $form;
	}

}
