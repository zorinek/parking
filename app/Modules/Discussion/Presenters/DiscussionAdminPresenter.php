<?php

declare(strict_types=1);

namespace App\Modules\Discussion;

use App\Model;
use App\Forms;
use Nette\Application\UI\Form;
use Nette\Application\Responses\FileResponse;
use Nette\Security\User;

final class DiscussionAdminPresenter extends \App\Presenters\BasePresenter
{

	/** @var Model\PreviewFactory $previewFactory @inject */
	public $previewFactory;

	/** @var Forms\NewCommentFormFactory @inject */
	public $newCommentFactory;

	/** @var Model\Discussion @inject */
	public $discussion;

	/** @var Model\Captcha @inject */
	public $captcha;

	/** @persistent */
	public $dis_id = "";

	/** @persistent */
	public $dis_identificator = "";

	/** @persistent */
	public $use_id = "";

	/** @persistent */
	public $dis_email = "";

	/** @persistent */
	public $dis_message = "";

	/** @persistent */
	public $dis_reply = "";

	/** @persistent */
	public $dis_authorized_by = "";

	/** @persistent */
	public $dis_datetimeinsert = "";

	/** @persistent */
	public $and_or = "and";

	/** @persistent */
	public $order = [];

	/** @persistent */
	public $order_dir = [];
	public $discussion_id;
	public $discussion_route;
	private $p_discussion;

	public function __construct($p_discussion)
	{
		$this->p_discussion = $p_discussion;
	}

	public function renderDefault($page)
	{
		$this->template->setFile("../app/Modules/components/default_preview.latte");
		$request = $this->getHttpRequest();
		$search_values = [
			"dis_id" => ["dis_id", "like", $this->dis_id],
			"dis_identificator" => ["dis_identificator", "like", $this->dis_identificator],
			"use_id" => ["use_id", "like", $this->use_id],
			"dis_email" => ["dis_email", "like", $this->dis_email],
			"dis_message" => ["dis_message", "like", $this->dis_message],
			"dis_reply" => ["dis_reply", "like", $this->dis_reply],
			"dis_authorized_by" => ["dis_authorized_by", "like", $this->dis_authorized_by, "users", "use_name", "Neschválen", "Zobrazit neschválené"],
			"dis_datetimeinsert" => ["dis_datetimeinsert", "like", $this->dis_datetimeinsert],
//                    ["use_id", "like", $this->use_id, "users", "use_name", "Nevyřešeno"], 
			"and_or" => ["and_or", "", $this->and_or],
			"order" => ["order", "", $this->order],
			"order_dir" => ["order_dir", "", $this->order_dir]
		];
		$ret = $this->previewFactory->providePreview("discussion", $page, ":Discussion:DiscussionAdmin:default", $request, 10, $search_values, $this, 'discussion.admin.', "Discussion/templates/DiscussionAdmin/");
		$this->template->setParameters($ret);
		$this->template->search_values = $search_values;
		$this->template->search = empty($ret['params']) ? false : true;
	}

	public function actionApprove($id, $page)
	{
		$this->discussion->approve($id, $this->user->id);
		$this->redirect(":Discussion:DiscussionAdmin:default", $page);
		$this->terminate();
	}

	public function actionDisapprove($id, $page)
	{
		$this->discussion->disapprove($id, $this->user->id);
		$this->redirect(":Discussion:DiscussionAdmin:default", $page);
		$this->terminate();
	}

	public function actionRemove($id, $page)
	{
		$this->discussion->remove($id);
		$this->redirect(":Discussion:DiscussionAdmin:default", $page);
		$this->terminate();
	}

}
