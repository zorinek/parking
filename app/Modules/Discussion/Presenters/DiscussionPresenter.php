<?php

declare(strict_types=1);

namespace App\Modules\Discussion;

use App\Model;
use App\Forms;
use Nette\Application\UI\Form;
use Nette\Application\Responses\FileResponse;

final class DiscussionPresenter extends \App\Presenters\BasePresenter
{

	/** @var Forms\NewCommentFormFactory @inject */
	public $newCommentFactory;

	/** @var Model\Discussion @inject */
	public $discussion;

	/** @var Model\Captcha @inject */
	public $captcha;
	public $discussion_id;
	public $discussion_route;
	private $p_discussion;

	public function __construct($p_discussion)
	{
		$this->p_discussion = $p_discussion;
	}

	public function renderDefault()
	{
		$all = $this->discussion->getAll("test2");
		$tree = $this->discussion->buildTree($all);
		$this->template->discussion = $tree;
		$this->template->p_discussion = $this->p_discussion;
		if ($this->p_discussion['use_captcha'])
		{
			$this->template->captcha = $this->captcha->generate(3, 2, 1);
		}
	}

	public function actionImage($id)
	{
//        $id = $this->user->id;
		if ($id == 0)
		{
			$filename = "../uploads/user_images/default.png";
		} else if (is_file("../uploads/user_images/" . $id . ".jpg"))
		{
			$filename = "../uploads/user_images/" . $id . ".jpg";
		} else
		{
			$filename = "../uploads/user_images/default.png";
		}
		$response = new FileResponse($filename, NULL, NULL);
		$this->sendResponse($response);
	}

	/**
	 * NewComment form factory.
	 */
	protected function createComponentNewCommentForm(): Form
	{
		$this->discussion_id = "test2";
		$this->discussion_route = "Discussion:Discussion:default";
		return $this->newCommentFactory->create(function (): void
				{

					$this->redirect(':Discussion:Discussion:default');
				});
	}

}
