<?php

declare(strict_types=1);

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use App\Model;
use Nette\Utils\Random;

final class FileUploadFormFactory
{

	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var Model\Files $files */
	private $files;

	/** @var Nette\Localization\ITranslator */
	private $translator;
	private $p_upload;

	public function __construct($p_upload, FormFactory $factory, Model\Files $files, Nette\Localization\ITranslator $translator)
	{
		$this->p_upload = $p_upload;
		$this->factory = $factory;
		$this->files = $files;
		$this->translator = $translator;
	}

	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();
		$form->setTranslator($this->translator);
		$form->addMultiUpload("files")->setRequired("Vložte alespoň jeden soubor!");
		$form->addSubmit('upload_file', 'Save');

		$form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void
		{

			foreach ($values->files as $file)
			{
				$ext = pathinfo($file->name, PATHINFO_EXTENSION);
				$file_name = Random::generate(10) . "." . $ext;
				$custom_dir = "";
				if (isset($form->getPresenter()->file_upload_dir))
				{
					$custom_dir = $form->getPresenter()->file_upload_dir;
					if (!is_dir($this->p_upload["upload_dir"] . $form->getPresenter()->file_upload_dir))
					{
						mkdir($this->p_upload["upload_dir"] . $form->getPresenter()->file_upload_dir);
					}
				}
				move_uploaded_file($file->getTemporaryFile(), $this->p_upload["upload_dir"] . $custom_dir . $file_name);
				$fil_id = $this->files->insertFile("", $this->p_upload["upload_dir"] . $custom_dir, $file_name, $ext, $this->p_upload["file_types"]["doc"]);
			}
			$onSuccess();
		};

		return $form;
	}

}
