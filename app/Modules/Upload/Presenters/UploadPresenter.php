<?php

declare(strict_types=1);

namespace App\Modules\Upload;

use Nette;
use App\Forms;
use Nette\Application\UI\Form;

class UploadPresenter extends \App\Presenters\BasePresenter
{

	/** @var Forms\FileUploadFormFactory @inject */
	public $fileUploadFactory;
	
	public $file_upload_dir;

	/**
	 * FileUpload form factory.
	 */
	protected function createComponentFileUploadForm(): Form
	{
		$this->file_upload_dir = "test/";
		return $this->fileUploadFactory->create(function (): void
		{
			$this->redirect(':Upload:Upload:default');
		});
	}

}
