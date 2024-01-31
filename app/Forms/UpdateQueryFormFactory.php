<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;

final class UpdateQueryFormFactory
{

	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var Model\Queries */
	private $queries;

	/** @var Nette\Localization\ITranslator */
	private $translator;
	private $p_queries;

	public function __construct($p_queries, FormFactory $factory, Nette\Localization\ITranslator $translator, Model\Queries $queries)
	{
		$this->p_queries = $p_queries;
		$this->factory = $factory;
		$this->translator = $translator;
		$this->queries = $queries;
	}

	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();
		$form->setTranslator($this->translator);
		$name = "que_name";
		if ($this->p_queries["displayed"][$name])
		{
			$field = $form->addText($name);
			if ($this->p_queries["required"][$name])
			{
				$field->setRequired("queries.new.error_" . $name);
			}
		}
		$name = "que_query";
		if ($this->p_queries["displayed"][$name])
		{
			$field = $form->addTextArea($name);
			if ($this->p_queries["required"][$name])
			{
				$field->setRequired("queries.new.error_" . $name);
			}
		}
		$name = "que_note";
		if ($this->p_queries["displayed"][$name])
		{
			$field = $form->addTextArea($name);
			if ($this->p_queries["required"][$name])
			{
				$field->setRequired("queries.new.error_" . $name);
			}
		}
		$name = "que_status";
		if ($this->p_queries["displayed"][$name])
		{
			$field = $form->addSelect("que_status", "", $this->p_queries["que_status"])->setPrompt("queries.new." . $name . "_prompt");
			if ($this->p_queries["required"][$name])
			{
				$field->setRequired("queries.new.error_" . $name);
			}
		}



		$form->addSubmit('insert_query');

		$form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void
		{
			try
			{
				$que_id = $form->getPresenter()->getParameters()['que_id'];
				$vals = [];

				$vals[$this->queries::COLUMN_QUE_ID] = $que_id;

				$name = "que_name";
				if ($this->p_queries["displayed"][$name])
				{
					$vals[$this->queries::COLUMN_QUE_NAME] = $values->{$name};
				}
				$name = "que_query";
				if ($this->p_queries["displayed"][$name])
				{
					$vals[$this->queries::COLUMN_QUE_QUERY] = $values->{$name};
				}
				$name = "que_note";
				if ($this->p_queries["displayed"][$name])
				{
					$vals[$this->queries::COLUMN_QUE_NOTE] = $values->{$name};
				}
				$name = "que_status";
				if ($this->p_queries["displayed"][$name])
				{
					$vals[$this->queries::COLUMN_QUE_STATUS] = $values->{$name};
				}


				$vals[$this->queries::COLUMN_QUE_DATETIMEINSERT] = date("Y-m-d H:i:s");

				$this->queries->update($vals);

				$onSuccess($que_id);
			} catch (Exception $ex)
			{
				echo $ex->getMessage();
				return;
			}
		};

		return $form;
	}

}
