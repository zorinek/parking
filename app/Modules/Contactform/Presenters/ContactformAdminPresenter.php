<?php

declare(strict_types=1);

namespace App\Modules\Contactform;

use Nette;
use App\Model;
use App\Forms;
use Nette\Application\UI\Form;

final class ContactformAdminPresenter extends \App\Presenters\BaseProtectedPresenter
{

	/** @var Forms\SetContactMessageDoneFormFactory @inject */
	public $setContactMessageDoneFactory;

	/** @var Model\PreviewFactory $previewFactory @inject */
	public $previewFactory;

	/** @var Model\Contactform @inject */
	public $contactform;

	/** @persistent */
	public $con_id = "";

	/** @persistent */
	public $con_email = "";

	/** @persistent */
	public $con_message = "";

	/** @persistent */
	public $con_name = "";

	/** @persistent */
	public $con_phone = "";

	/** @persistent */
	public $con_category = "";

	/** @persistent */
	public $con_sender = "";

	/** @persistent */
	public $con_datetimeinsert = "";

	/** @persistent */
	public $use_id = "";

	/** @persistent */
	public $and_or = "and";

	/** @persistent */
	public $order = [];

	/** @persistent */
	public $order_dir = [];
	private $parameters;

	public function __construct($parameters)
	{
		$this->parameters = $parameters;
	}

	public function renderDefault($page)
	{
		$this->template->setFile("../app/Modules/components/default_preview.latte");
		$request = $this->getHttpRequest();
		$search_values = [
			["con_id", "like", $this->con_id],
			["con_email", "like", $this->con_email],
			["con_message", "like", $this->con_message],
			["con_name", "like", $this->con_name],
			["con_phone", "like", $this->con_phone],
			["con_category", "like", $this->con_category, false, "contactform.categories."],
			["con_sender", "like", $this->con_sender, false, "contactform.sender."],
			["con_datetimeinsert", "like", $this->con_datetimeinsert],
			["use_id", "like", $this->use_id, "users", "use_name", $this->translator->translate("contactform.admin.not_done"), $this->translator->translate("contactform.admin.not_done_tooltip")],
			["and_or", "", $this->and_or],
			["order", "", $this->order],
			["order_dir", "", $this->order_dir]
		];
		$ret = $this->previewFactory->providePreview("contactform", $page, ":Contactform:ContactformAdmin:default", $request, 10, $search_values, $this, 'contactform.admin.', "Contactform/templates/ContactformAdmin/");
		$this->template->setParameters($ret);
		$this->template->search_values = $search_values;
		$this->template->search = empty($ret['params']) ? false : true;
		$this->template->parameters = $this->parameters;
	}

	public function renderDetail($id)
	{
		$message = $this->contactform->get($id);
		$this->template->message = $message;
		$this->template->parameters = $this->parameters;
		if (is_file($this->parameters["notification_dir"] . $this->contactform->prepareFileName($message->con_datetimeinsert) . ".html"))
		{
			$this->template->notification_file = true;
		}
		if (is_file($this->parameters["sender_dir"] . $this->contactform->prepareFileName($message->con_datetimeinsert) . ".html"))
		{
			$this->template->sender_file = true;
		}
	}

	public function actionGetNotificationEmail($id)
	{
		$message = $this->contactform->get($id);
		$file = $this->parameters["notification_dir"] . $this->contactform->prepareFileName($message->con_datetimeinsert) . ".html";
		if (is_file($file))
		{
			$response = new Nette\Application\Responses\FileResponse($file, NULL);
			$this->sendResponse($response);
		}
	}

	public function actionGetSenderEmail($id)
	{
		$message = $this->contactform->get($id);
		$file = $this->parameters["sender_dir"] . $this->contactform->prepareFileName($message->con_datetimeinsert) . ".html";
		if (is_file($file))
		{
			$response = new Nette\Application\Responses\FileResponse($file, NULL);
			$this->sendResponse($response);
		}
	}

	/**
	 * SetContactMessageDone form factory.
	 */
	protected function createComponentSetContactMessageDoneForm(): Form
	{
		return $this->setContactMessageDoneFactory->create(function ($id): void
				{
					$this->redirect(':Contactform:ContactformAdmin:detail', [$id]);
				});
	}

}
