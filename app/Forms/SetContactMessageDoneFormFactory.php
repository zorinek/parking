<?php

declare(strict_types=1);

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;
use App\Model;

final class SetContactMessageDoneFormFactory
{

	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var User */
	private $user;

	/** @var Model\Contactform */
	private $contactform;

	public function __construct(FormFactory $factory, User $user, Model\Contactform $contactform)
	{
		$this->factory = $factory;

		$this->user = $user;
		$this->contactform = $contactform;
	}

	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();

		$form->addSubmit('set_done', 'Set done');

		$form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void
		{

			if ($form->isSubmitted()->getName() == "set_done")
			{
				$id = $form->getPresenter()->getParameter("id");
				$this->contactform->setDone($id, $this->user->id, date("Y-m-d H:i:s"));
			}

			$onSuccess($id);
		};

		return $form;
	}

}
