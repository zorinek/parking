<?php

namespace App\Model;

use Nette\Security\Permission;

class Acl
{

	private $acl = null;

	public function __construct()
	{

		$this->acl = new Permission();

		$this->acl->addRole('user');
		$this->acl->addRole('admin', 'user');

//        $this->acl->addResource('frontend:homepage');
		$this->acl->addResource('admin:homepage');
		$this->acl->addResource('admin:accessdenied');
		$this->acl->addResource('settings:users');
		$this->acl->addResource('settings:account');
		$this->acl->addResource('contactform:contactformadmin');
		$this->acl->addResource('discussion:discussionadmin');
		$this->acl->addResource('files:filesadmin');
		$this->acl->addResource('notes:notes');
		$this->acl->addResource('notes:notesadmin');
		$this->acl->addResource('queries:queries');
		$this->acl->addResource('queries:queriesfrontend');
		$this->acl->addResource('projects:projects');
		$this->acl->addResource('projects:campaigns');
		$this->acl->addResource('sections:sections');
		$this->acl->addResource('usersconfigurations:usersconfigurations');

		$this->acl->addResource('crossroads:homepage');
		$this->acl->addResource('crossroads:directions');

//        $this->acl->allow('user', 'frontend:homepage');
		$this->acl->allow('user', 'admin:accessdenied');
		$this->acl->allow('user', 'admin:homepage');
		$this->acl->allow('admin', 'settings:users');
		$this->acl->allow('user', 'settings:account');
		$this->acl->allow('user', 'contactform:contactformadmin');
		$this->acl->allow('user', 'discussion:discussionadmin');
		$this->acl->allow('user', 'files:filesadmin');
		$this->acl->allow('user', 'notes:notes');
		$this->acl->allow('user', 'queries:queriesfrontend');
		$this->acl->allow('admin', 'notes:notesadmin');
		$this->acl->allow('admin', 'queries:queries');
		$this->acl->allow('user', 'projects:projects');
		$this->acl->allow('user', 'projects:campaigns');
		$this->acl->allow('user', 'sections:sections');
		$this->acl->allow('user', 'usersconfigurations:usersconfigurations');
		$this->acl->allow('user', 'crossroads:homepage');
		$this->acl->allow('user', 'crossroads:directions');
	}

	public function isAllowed($roles, $resource, $privilege)
	{
		if (is_array($roles))
		{
			foreach ($roles as $role)
			{
				$ret = $this->acl->isAllowed($role, $resource, $privilege);
				if ($ret)
				{
					return true;
				}
			}
			return false;
		} else
		{
			return $this->acl->isAllowed($roles, $resource, $privilege);
		}
	}

}
