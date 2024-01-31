<?php

declare(strict_types=1);

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;
use App\Model;

final class OtpFormFactory
{

	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var User */
	private $user;

	/** @var Model\Users */
	private $users;

	/** @var Model\Otp */
	private $otp;
	private $session;

	/** @var Nette\Localization\ITranslator */
	private $translator;

	public function __construct(FormFactory $factory, User $user, Model\Users $users, Model\Otp $otp, Nette\Http\Session $session, Nette\Localization\ITranslator $translator)
	{
		$this->factory = $factory;

		$this->user = $user;
		$this->users = $users;
		$this->otp = $otp;
		$this->session = $session;
		$this->translator = $translator;
	}

	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();
		$form->setTranslator($this->translator);
		$form->addText('use_email')->setRequired('otp.otp.error_use_email');

		$form->addPassword('uso_passhash')->setRequired('otp.otp.error_uso_passhash');

		$form->addSubmit('otp_sign_in');

		$form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void
		{
			try
			{

				$result = $this->users->getByEmail($values->use_email);
				if (is_null($result))
				{
					throw new \App\Exceptions\NotFoundEmailException;
				} else
				{
//                                $user_res = $this->otp->checkUser($result->use_id, $values->uso_passhash);

					$this->user->setAuthenticator($this->otp);
					$this->user->login($result->use_id, $values->uso_passhash);
				}
//                            
			} catch (\App\Exceptions\NotFoundEmailException $e)
			{
				$form->addError('otp.otp.error_email_not_found');
				return;
			} catch (Nette\Security\AuthenticationException $e)
			{
				$form->addError('otp.otp.error_credentials_incorrect');
				return;
			} catch (\App\Exceptions\EmailNotValidatedException $e)
			{
				$form->addError('1');
				return;
			} catch (\App\Exceptions\AccountDeactivatedException $e)
			{
				$form->addError('otp.otp.error_account_not_active');
				return;
			}

			$onSuccess();
		};

		return $form;
	}

}
