<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;
use Tracy\Debugger;

final class AccountImageFormFactory
{

	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var Model\Files */
	private $files;

	/** @var Model\Users */
	private $users;

	/** @var User */
	private $user;

	/** @var Nette\Localization\ITranslator */
	private $translator;
	private $p_upload;

	public function __construct($p_upload, FormFactory $factory, Model\Files $files, Model\Users $users, User $user, Nette\Localization\ITranslator $translator)
	{
		$this->p_upload = $p_upload;
		$this->factory = $factory;
		$this->files = $files;
		$this->users = $users;
		$this->user = $user;
		$this->translator = $translator;
	}

	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();
		$form->setTranslator($this->translator);
		$form->addUpload("image")->setRequired("users.profile.image_error");

		$form->addSubmit('save_image', 'Uložit');

		$form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void
		{
			try
			{
				$ext = pathinfo($values->image->name, PATHINFO_EXTENSION);
				$file_name = $this->user->id . "." . $ext;
				$custom_dir = "";

				if (isset($form->getPresenter()->file_upload_dir))
				{
					$custom_dir = $form->getPresenter()->file_upload_dir;
					if (!is_dir($this->p_upload["upload_dir"] . $form->getPresenter()->file_upload_dir))
					{
						mkdir($this->p_upload["upload_dir"] . $form->getPresenter()->file_upload_dir);
					}
				}

				move_uploaded_file($values->image->getTemporaryFile(), $this->p_upload["upload_dir"] . $custom_dir . $file_name);

				$check = $this->users->checkImage($this->user->id);
				if (is_null($check))
				{
					$fil_id = $this->files->insertFile($values->image->getName(), $this->p_upload["upload_dir"] . $custom_dir, $file_name, $ext, $this->p_upload['file_types']["user_image"], $this->users::TABLE_NAME, "null");
					$this->users->setImageFile($this->user->id, $fil_id);
				} 
				else
				{
					$this->files->removeFile($check, false);
					$fil_id = $this->files->insertFile($values->image->getName(), $this->p_upload["upload_dir"] . $custom_dir, $file_name, $ext, $this->p_upload['file_types']["user_image"], $this->users::TABLE_NAME, "null");
					$this->users->setImageFile($this->user->id, $fil_id);
				}
			} 
			catch (\Exception $e)
			{
				Debugger::log($e);
			}
			$onSuccess();
		};

		return $form;
	}

}
