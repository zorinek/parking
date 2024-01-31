<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;

final class UpdateProjectFormFactory
{

	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var Model\Projects */
	private $projects;

	/** @var Nette\Localization\ITranslator */
	private $translator;
	private $p_projects;
	private $param = "p_projects";

	public function __construct($p_projects, FormFactory $factory, Nette\Localization\ITranslator $translator, Model\Projects $projects)
	{
		$this->p_projects = $p_projects;
		$this->factory = $factory;
		$this->translator = $translator;
		$this->projects = $projects;
	}

	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();
		$form->setTranslator($this->translator);
		$name = "pro_name";
		if ($this->{$this->param}["displayed"][$name])
		{
			$field = $form->addText($name);
			if ($this->{$this->param}["required"][$name])
			{
				$field->setRequired("projects.update.error_" . $name);
			}
		}
		$name = "pro_note";
		if ($this->{$this->param}["displayed"][$name])
		{
			$field = $form->addTextArea($name);
			if ($this->{$this->param}["required"][$name])
			{
				$field->setRequired("params.new.error_" . $name);
			}
		}

		$form->addSubmit('update_project');

		$form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void
		{
			try
			{
				$pro_id = $form->getPresenter()->getParameters()['pro_id'];
				$vals = [];

				$vals[$this->projects::COLUMN_PRO_ID] = $pro_id;

				$name = "pro_name";
				if ($this->{$this->param}["displayed"][$name])
				{
					$vals[$this->projects::COLUMN_PRO_NAME] = $values->{$name};
				}
				$name = "pro_note";
				if ($this->{$this->param}["displayed"][$name])
				{
					$vals[$this->projects::COLUMN_PRO_NOTE] = $values->{$name};
				}

				$this->projects->update($vals);

				$onSuccess($pro_id);
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
