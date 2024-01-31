<?php

declare(strict_types=1);

namespace App\Modules\UsersConfigurations;

use App\Model;
use App\Forms;
use Nette\Application\UI\Form;

final class UsersConfigurationsPresenter extends \App\Presenters\BaseProtectedPresenter
{

	/** @var Model\UsersConfigurations $usersConfiguration @inject */
	public $usersConfigurations;

	/** @var Forms\ConfigurationFormFactory $configurationFactory @inject */
	public $configurationFactory;

	private $p_configuration;

	public function __construct($p_configuration)
	{
		parent::__construct();
		$this->p_configuration = $p_configuration;
	}

	public function renderConfiguration()
	{
		$this->template->p_configuration = $this->p_configuration;
		$user_configuration = $this->usersConfigurations->getUserConfiguration($this->user->id);
		$this->template->user_configuration = $user_configuration;
		if (isset($user_configuration["layout"]))
		{
			$this["configurationForm"]->setDefaults(["layout" => $user_configuration["layout"]->usc_value]);
		}
		if (isset($user_configuration["measurement"]))
		{
			$this["configurationForm"]->setDefaults(["measurement" => $user_configuration["measurement"]->usc_value]);
		}
	}

	/**
	 * New project form factory.
	 */
	protected function createComponentConfigurationForm(): Form
	{
		return $this->configurationFactory->create(function (): void
		{
			$this->redirect(':UsersConfigurations:UsersConfigurations:configuration');
		});
	}
}
