<?php

declare(strict_types=1);

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use App\Model;
use Nette\Security\User;

final class NewCommentFormFactory
{

	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var Model\Discussion */
	private $discussion;

	/* @var User */
	private $user;

	/** @var Model\Users */
	private $users;

	/** @var Model\MailSender */
	private $mailSender;

	/** @var Model\Captcha */
	private $captcha;

	/** @var Nette\Localization\ITranslator */
	private $translator;
	private $p_discussion;
	private $p_default;

	public function __construct($p_dicsussion, $p_default, FormFactory $factory, Model\Discussion $discussion, User $user, Model\Users $users, Model\MailSender $mailSender, Model\Captcha $captcha, Nette\Localization\ITranslator $translator)
	{
		$this->p_discussion = $p_dicsussion;
		$this->p_default = $p_default;
		$this->factory = $factory;
		$this->discussion = $discussion;
		$this->user = $user;
		$this->users = $users;
		$this->mailSender = $mailSender;
		$this->captcha = $captcha;
		$this->translator = $translator;
	}

	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();
		$form->setTranslator($this->translator);
		if (!$this->user->isLoggedIn())
		{
			$form->addText('dis_email')->setRequired('discussion.base.error_dis_email');
		}

		$form->addTextArea("dis_message")->setRequired('discussion.base.error_dis_message');
		$form->addHidden("dis_reply", 0);

		if ($this->p_discussion['use_captcha'])
		{
			$form->addText('captcha')->setRequired('discussion.base.error_captcha');
			$form->addHidden("captcha_text");
		}
		$form->addSubmit('save_message');

		$form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void
		{

			try
			{
				if ($this->p_discussion['use_captcha'])
				{
					$text_value = $this->captcha->decode($values->captcha_text);
					if ($text_value != $values->captcha)
					{
						throw new \App\Exceptions\InvalidCaptchaException;
					}
				}
				$vals = [];
				$vals[$this->discussion::COLUMN_DIS_IDENTIFICATOR] = $form->getPresenter()->discussion_id;
				if ($form->getPresenter()->getUser()->isLoggedIn())
				{
					$vals[$this->discussion::COLUMN_USE_ID] = $form->getPresenter()->getUser()->id;
					$vals[$this->discussion::COLUMN_DIS_EMAIL] = $form->getPresenter()->getUser()->getIdentity()->data['use_email'];
				} else
				{
					$vals[$this->discussion::COLUMN_DIS_EMAIL] = $values->{$this->discussion::COLUMN_DIS_EMAIL};
				}

				$vals[$this->discussion::COLUMN_DIS_MESSAGE] = $values->{$this->discussion::COLUMN_DIS_MESSAGE};
				$vals[$this->discussion::COLUMN_DIS_DATETIMEINSERT] = date("Y-m-d H:i:s");
				if (!empty($values->dis_reply))
				{
					$check = $this->discussion->checkReply($values->dis_reply, $form->getPresenter()->discussion_id);
					if (is_null($check))
					{
						throw new App\Exceptions\NotFoundSourceCommentException;
					}
				}
				$vals[$this->discussion::COLUMN_DIS_REPLY] = $values->{$this->discussion::COLUMN_DIS_REPLY};
//                        var_dump($check)
				if ($this->p_discussion['comment_authorization'] == $this->p_discussion['comment_authorization_status']["not_authorized"] && $form->getPresenter()->getUser()->getIdentity()->data['use_discussion_authorized'] == $this->p_discussion["user_authorized_status"]["enabled"])
				{
					$vals[$this->discussion::COLUMN_DIS_AUTHORIZED_BY] = 0;
				}
//                        dump($vals);
//                        die();
				$id = $this->discussion->insert($vals);

				if ($this->p_discussion['email_notification'])
				{
					if ($values->{$this->discussion::COLUMN_DIS_REPLY} != 0)
					{
						$user_data = $this->discussion->get($vals[$this->discussion::COLUMN_DIS_REPLY]);
						$email = $user_data->dis_email;
						$params = [
							'server_name' => $this->p_default['server_name'],
							'message' => $vals[$this->discussion::COLUMN_DIS_MESSAGE],
							'href' => $form->getPresenter()->discussion_route,
							'id' => $id
						];
						$this->mailSender->sendEmail(
								__DIR__ . '/../Modules/Discussion/templates/Discussion/new_comment_email.latte',
								$params,
								$this->p_discussion['email_sender'],
								$email
						);
					}
				}
			} catch (\App\Exceptions\NotFoundSourceCommentException $e)
			{
				$form->addError("discussion.base.error_source_comment");
			} catch (\App\Exceptions\InvalidCaptchaException $e)
			{
				$form["captcha"]->addError('discussion.base.error_captcha_invalid');
				return;
			}
			$onSuccess();
		};

		return $form;
	}

}
