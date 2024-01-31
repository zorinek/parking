<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Contributte;
use App\Model;

/**
 * Base presenter for all application presenters.
 */
abstract class BaseProtectedPresenter extends Nette\Application\UI\Presenter
{

	/** @persistent */
	public $locale;

	/** @var Nette\Localization\ITranslator @inject */
	public $translator;

	/** @var Contributte\Translation\LocalesResolvers\Session @inject */
	public $translatorSessionResolver;

	/** @var Model\Notes $notes @inject */
	public $notes;
	protected $p_default;
	protected $p_showbox;

	public function setPDefault($p_default)
	{
		$this->p_default = $p_default;
	}

	public function setPShowbox($p_showbox)
	{
		$this->p_showbox = $p_showbox;
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

		if (!$this->getUser()->isLoggedIn())
		{
			if ($this->name == "Api:Main" || $this->name == "Login:Sign")
			{
				
			} else if (($this->action != 'in' && $this->name != 'Login'))
			{
				$this->flashMessage('Do této části aplikace nemáte přístup bez přihlášení. Prosím přihlašte se.');
				$this->redirect(':Login:Login:login');
			}
		} else
		{
			$acl = new \App\Model\Acl();

			$roles = $this->getUser()->getIdentity()->getRoles();
			//$role = array_shift($roles);
			$this->template->user_roles = $roles;
//            dump($roles);

			$allowed = $acl->isAllowed($roles, strtolower($this->name), $this->action);
//            dump($allowed);
//            die();
			if (!$allowed)//otestování, jestli má uživatelská role pro daný zdroj možnost provádět s ním aktuální akci
			{
//                if ($this->action != 'in' && $this->name != 'Login')
//                {
				$this->flashMessage('Do této části aplikace nemáte přístup. Byl jste přesměrován');
				$this->redirect(':Admin:AccessDenied:default');
//                }
			}
			$url = $this->getHttpRequest()->getUrl();
			$parsed = explode("?", (string) $url);
			$this->template->p_showbox = $this->p_showbox;
			if (!in_array($this->getPresenter()->name . ':' . $this->getPresenter()->action, $this->p_showbox['disallowed_pages']))
			{
//                $notes = $this->notes->getNotesForPage($parsed[0], $this->user->id);
				$notes = $this->notes->getNotesForPageWithoutUser($parsed[0]);
				$this->template->notes = $notes;
			}
		}
	}

}
