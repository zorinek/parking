<?php

declare(strict_types=1);

namespace App\Modules\Contactform;

use Nette;
use App\Model;
use App\Forms;
use Nette\Application\UI\Form;

final class ContactformPresenter extends \App\Presenters\BasePresenter
{

	/** @var Forms\ContactformFormFactory @inject */
	public $contactformFactory;

	/** @var Model\Captcha @inject */
	public $captcha;
	private $p_contactform;

	public function __construct($p_contactform)
	{
		$this->p_contactform = $p_contactform;
	}

	public function renderDefault()
	{
		$this->template->parameters = $this->p_contactform;
		if ($this->p_contactform["captcha_enabled"])
		{
			$this->template->captcha = $this->captcha->generate(3, 2, 1);
		}
	}

	/**
	 * Contactform form factory.
	 */
	protected function createComponentContactformForm(): Form
	{
		return $this->contactformFactory->create(function (): void
				{
					$this->redirect(':Contactform:Contactform:sended');
				});
	}

}
