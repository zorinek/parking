<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;

final class ConfigurationFormFactory
{

	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var Model\UsersConfigurations */
	private $users_configurations;

	/** @var Nette\Localization\ITranslator */
	private $translator;
	private $p_configurations;
	private $param = "p_configurations";

	public function __construct($p_configurations, FormFactory $factory, Nette\Localization\ITranslator $translator, Model\UsersConfigurations $usersConfigurations)
	{
		$this->p_configurations = $p_configurations;
		$this->factory = $factory;
		$this->translator = $translator;
		$this->users_configurations = $usersConfigurations;
	}

	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();
		$form->setTranslator($this->translator);
		$name = "layout";
		if ($this->{$this->param}["displayed"][$name])
		{
			$field = $form->addSelect($name, "", $this->{$this->param}[$name])->setPrompt("configuration.screen_layout.notselected");
			if ($this->{$this->param}["required"][$name])
			{
				$field->setRequired("configuration.new.error_" . $name);
			}
		}
		$name = "measurement";
		if ($this->{$this->param}["displayed"][$name])
		{
			$field = $form->addSelect($name, "", $this->{$this->param}[$name])->setPrompt("configuration.screen_measurement.notselected");
			if ($this->{$this->param}["required"][$name])
			{
				$field->setRequired("configuration.new.error_" . $name);
			}
		}
		$name = "play_pause";
		if ($this->{$this->param}["displayed"][$name])
		{
			$field = $form->addText($name);
			if ($this->{$this->param}["required"][$name])
			{
				$field->setRequired("configuration.new.error_" . $name);
			}
			$field->setHtmlAttribute("placeholder", $this->{$this->param}["default_value"][$name]);
		}
		$name = "speed_up";
		if ($this->{$this->param}["displayed"][$name])
		{
			$field = $form->addText($name);
			if ($this->{$this->param}["required"][$name])
			{
				$field->setRequired("configuration.new.error_" . $name);
			}
			$field->setHtmlAttribute("placeholder", $this->{$this->param}["default_value"][$name]);
		}
		$name = "speed_down";
		if ($this->{$this->param}["displayed"][$name])
		{
			$field = $form->addText($name);
			if ($this->{$this->param}["required"][$name])
			{
				$field->setRequired("configuration.new.error_" . $name);
			}
			$field->setHtmlAttribute("placeholder", $this->{$this->param}["default_value"][$name]);
		}
		$name = "reset_speed";
		if ($this->{$this->param}["displayed"][$name])
		{
			$field = $form->addText($name);
			if ($this->{$this->param}["required"][$name])
			{
				$field->setRequired("configuration.new.error_" . $name);
			}
			$field->setHtmlAttribute("placeholder", $this->{$this->param}["default_value"][$name]);
		}
		$name = "left_minus_illegal";
		if ($this->{$this->param}["displayed"][$name])
		{
			$field = $form->addText($name);
			if ($this->{$this->param}["required"][$name])
			{
				$field->setRequired("configuration.new.error_" . $name);
			}
			$field->setHtmlAttribute("placeholder", $this->{$this->param}["default_value"][$name]);
		}
		$name = "left_plus_illegal";
		if ($this->{$this->param}["displayed"][$name])
		{
			$field = $form->addText($name);
			if ($this->{$this->param}["required"][$name])
			{
				$field->setRequired("configuration.new.error_" . $name);
			}
			$field->setHtmlAttribute("placeholder", $this->{$this->param}["default_value"][$name]);
		}
		$name = "left_minus_empty";
		if ($this->{$this->param}["displayed"][$name])
		{
			$field = $form->addText($name);
			if ($this->{$this->param}["required"][$name])
			{
				$field->setRequired("configuration.new.error_" . $name);
			}
			$field->setHtmlAttribute("placeholder", $this->{$this->param}["default_value"][$name]);
		}
		$name = "left_plus_empty";
		if ($this->{$this->param}["displayed"][$name])
		{
			$field = $form->addText($name);
			if ($this->{$this->param}["required"][$name])
			{
				$field->setRequired("configuration.new.error_" . $name);
			}
			$field->setHtmlAttribute("placeholder", $this->{$this->param}["default_value"][$name]);
		}
		$name = "left_minus_not";
		if ($this->{$this->param}["displayed"][$name])
		{
			$field = $form->addText($name);
			if ($this->{$this->param}["required"][$name])
			{
				$field->setRequired("configuration.new.error_" . $name);
			}
			$field->setHtmlAttribute("placeholder", $this->{$this->param}["default_value"][$name]);
		}
		$name = "left_plus_not";
		if ($this->{$this->param}["displayed"][$name])
		{
			$field = $form->addText($name);
			if ($this->{$this->param}["required"][$name])
			{
				$field->setRequired("configuration.new.error_" . $name);
			}
			$field->setHtmlAttribute("placeholder", $this->{$this->param}["default_value"][$name]);
		}
		$name = "left_delete";
		if ($this->{$this->param}["displayed"][$name])
		{
			$field = $form->addText($name);
			if ($this->{$this->param}["required"][$name])
			{
				$field->setRequired("configuration.new.error_" . $name);
			}
			$field->setHtmlAttribute("placeholder", $this->{$this->param}["default_value"][$name]);
		}
		$name = "right_minus_illegal";
		if ($this->{$this->param}["displayed"][$name])
		{
			$field = $form->addText($name);
			if ($this->{$this->param}["required"][$name])
			{
				$field->setRequired("configuration.new.error_" . $name);
			}
			$field->setHtmlAttribute("placeholder", $this->{$this->param}["default_value"][$name]);
		}
		$name = "right_plus_illegal";
		if ($this->{$this->param}["displayed"][$name])
		{
			$field = $form->addText($name);
			if ($this->{$this->param}["required"][$name])
			{
				$field->setRequired("configuration.new.error_" . $name);
			}
			$field->setHtmlAttribute("placeholder", $this->{$this->param}["default_value"][$name]);
		}
		$name = "right_minus_empty";
		if ($this->{$this->param}["displayed"][$name])
		{
			$field = $form->addText($name);
			if ($this->{$this->param}["required"][$name])
			{
				$field->setRequired("configuration.new.error_" . $name);
			}
			$field->setHtmlAttribute("placeholder", $this->{$this->param}["default_value"][$name]);
		}
		$name = "right_plus_empty";
		if ($this->{$this->param}["displayed"][$name])
		{
			$field = $form->addText($name);
			if ($this->{$this->param}["required"][$name])
			{
				$field->setRequired("configuration.new.error_" . $name);
			}
			$field->setHtmlAttribute("placeholder", $this->{$this->param}["default_value"][$name]);
		}
		$name = "right_minus_not";
		if ($this->{$this->param}["displayed"][$name])
		{
			$field = $form->addText($name);
			if ($this->{$this->param}["required"][$name])
			{
				$field->setRequired("configuration.new.error_" . $name);
			}
			$field->setHtmlAttribute("placeholder", $this->{$this->param}["default_value"][$name]);
		}
		$name = "right_plus_not";
		if ($this->{$this->param}["displayed"][$name])
		{
			$field = $form->addText($name);
			if ($this->{$this->param}["required"][$name])
			{
				$field->setRequired("configuration.new.error_" . $name);
			}
			$field->setHtmlAttribute("placeholder", $this->{$this->param}["default_value"][$name]);
		}
		$name = "right_delete";
		if ($this->{$this->param}["displayed"][$name])
		{
			$field = $form->addText($name);
			if ($this->{$this->param}["required"][$name])
			{
				$field->setRequired("configuration.new.error_" . $name);
			}
			$field->setHtmlAttribute("placeholder", $this->{$this->param}["default_value"][$name]);
		}
		$name = "minus_illegal";
		if ($this->{$this->param}["displayed"][$name])
		{
			$field = $form->addText($name);
			if ($this->{$this->param}["required"][$name])
			{
				$field->setRequired("configuration.new.error_" . $name);
			}
			$field->setHtmlAttribute("placeholder", $this->{$this->param}["default_value"][$name]);
		}
		$name = "plus_illegal";
		if ($this->{$this->param}["displayed"][$name])
		{
			$field = $form->addText($name);
			if ($this->{$this->param}["required"][$name])
			{
				$field->setRequired("configuration.new.error_" . $name);
			}
			$field->setHtmlAttribute("placeholder", $this->{$this->param}["default_value"][$name]);
		}
		$name = "minus_empty";
		if ($this->{$this->param}["displayed"][$name])
		{
			$field = $form->addText($name);
			if ($this->{$this->param}["required"][$name])
			{
				$field->setRequired("configuration.new.error_" . $name);
			}
			$field->setHtmlAttribute("placeholder", $this->{$this->param}["default_value"][$name]);
		}
		$name = "plus_empty";
		if ($this->{$this->param}["displayed"][$name])
		{
			$field = $form->addText($name);
			if ($this->{$this->param}["required"][$name])
			{
				$field->setRequired("configuration.new.error_" . $name);
			}
			$field->setHtmlAttribute("placeholder", $this->{$this->param}["default_value"][$name]);
		}
		$name = "minus_not";
		if ($this->{$this->param}["displayed"][$name])
		{
			$field = $form->addText($name);
			if ($this->{$this->param}["required"][$name])
			{
				$field->setRequired("configuration.new.error_" . $name);
			}
			$field->setHtmlAttribute("placeholder", $this->{$this->param}["default_value"][$name]);
		}
		$name = "plus_not";
		if ($this->{$this->param}["displayed"][$name])
		{
			$field = $form->addText($name);
			if ($this->{$this->param}["required"][$name])
			{
				$field->setRequired("configuration.new.error_" . $name);
			}
			$field->setHtmlAttribute("placeholder", $this->{$this->param}["default_value"][$name]);
		}
		$name = "delete";
		if ($this->{$this->param}["displayed"][$name])
		{
			$field = $form->addText($name);
			if ($this->{$this->param}["required"][$name])
			{
				$field->setRequired("configuration.new.error_" . $name);
			}
			$field->setHtmlAttribute("placeholder", $this->{$this->param}["default_value"][$name]);
		}



		$form->addSubmit('save_configuration');

		$form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void
		{
			try
			{

				$use_id = $form->getPresenter()->user->id;
				$date = date("Y-m-d H:i:s");

				foreach ($this->{$this->param}["default_value"] as $key => $line)
				{
					$vals = [];
					if ($this->{$this->param}["displayed"][$key])
					{
						$vals[$this->users_configurations::COLUMN_USC_TYPE] = $key;
						$vals[$this->users_configurations::COLUMN_USC_VALUE] = $values->{$key};
						$vals[$this->users_configurations::COLUMN_USE_ID] = $use_id;
						$vals[$this->users_configurations::COLUMN_USC_DATETIMEINSERT] = $date;
						$this->users_configurations->insertUpdate($vals);
					}
				}

				$onSuccess();
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
