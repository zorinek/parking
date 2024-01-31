<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Contributte;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{

	/** @persistent */
	public $locale;

	/** @var Nette\Localization\ITranslator @inject */
	public $translator;

	/** @var Contributte\Translation\LocalesResolvers\Session @inject */
	public $translatorSessionResolver;
	protected $p_default;

	public function setPDefault($p_default)
	{
		$this->p_default = $p_default;
	}

	public function handleChangeLocale(string $locale): void
	{
		$this->translatorSessionResolver->setLocale($locale);
		$this->redirect('this');
	}

	public function startup()
	{
		parent::startup();
		$this->template->locale = $this->locale;
		$this->template->cookiebar_enabled = $this->p_default['cookiebar_enabled'];
	}

}
