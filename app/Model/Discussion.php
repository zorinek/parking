<?php

declare(strict_types=1);

namespace App\Model;

use Nette;

/**
 * Discussion
 */
final class Discussion
{

	use Nette\SmartObject;

	public const
			TABLE_NAME = 'discussion',
			COLUMN_DIS_ID = 'dis_id',
			COLUMN_DIS_IDENTIFICATOR = 'dis_identificator',
			COLUMN_USE_ID = 'use_id',
			COLUMN_DIS_EMAIL = 'dis_email',
			COLUMN_DIS_MESSAGE = 'dis_message',
			COLUMN_DIS_REPLY = 'dis_reply',
			COLUMN_DIS_AUTHORIZED_BY = 'dis_authorized_by',
			COLUMN_DIS_DATETIMEINSERT = 'dis_datetimeinsert';

	/** @var Nette\Database\Context */
	private $db;
	private $p_discussion;

	public function __construct($p_discussion, Nette\Database\Context $db)
	{
		$this->p_discussion = $p_discussion;
		$this->db = $db;
	}

	public function insert($values)
	{
		$ins = $this->db->table(self::TABLE_NAME)->insert($values);
		return $ins->{self::COLUMN_DIS_ID};
	}

	public function getAll($dis_identificator)
	{
		$get = $this->db->table(self::TABLE_NAME);
		$get->where(self::COLUMN_DIS_IDENTIFICATOR, $dis_identificator);
		if ($this->p_discussion['comment_authorization'] > $this->p_discussion['comment_authorization_status']["none"])
		{
			$get->where(self::COLUMN_DIS_AUTHORIZED_BY . " IS NOT NULL");
		}
		$res = $get->fetchAssoc(self::COLUMN_DIS_ID);
		return $res;
	}

	public function checkReply($dis_reply, $dis_identificator)
	{
		$get = $this->db->table(self::TABLE_NAME)->where(self::COLUMN_DIS_ID, $dis_reply)->where(self::COLUMN_DIS_IDENTIFICATOR, $dis_identificator)->fetch();
		return $get;
	}

	public function get($dis_id)
	{
		$get = $this->db->table(self::TABLE_NAME)->where(self::COLUMN_DIS_ID, $dis_id)->fetch();
		return $get;
	}

	public function buildTree(array $elements, $parentId = 0)
	{
		$branch = array();

		foreach ($elements as $element)
		{
			if ($element['dis_reply'] == $parentId)
			{
				$children = $this->buildTree($elements, $element['dis_id']);
				if ($children)
				{
					$element['children'] = $children;
				}
				$branch[] = $element;
			}
		}

		return $branch;
	}

	public function approve($dis_id, $use_id)
	{
		$get = $this->get($dis_id);
		if (!is_null($get) && is_null($get->{self::COLUMN_DIS_AUTHORIZED_BY}))
		{
			$get->update([
				self::COLUMN_DIS_AUTHORIZED_BY => $use_id
			]);
		}
	}

	public function disapprove($dis_id, $use_id)
	{
		$get = $this->get($dis_id);
		if (!is_null($get) && !is_null($get->{self::COLUMN_DIS_AUTHORIZED_BY}))
		{
			$get->update([
				self::COLUMN_DIS_AUTHORIZED_BY => null
			]);
		}
	}

	public function remove($dis_id)
	{
		$delete = $this->db->query("DELETE FROM discussion WHERE dis_id IN (with recursive cte (id, parent_id) as (
                select     dis_id,
                           dis_reply
                from       discussion
                where      dis_id = ?
                union all
                select     p.dis_id,
                           p.dis_reply
                from       discussion p
                inner join cte
                        on p.dis_reply = cte.id
              )
              select id from cte order by id desc)", $dis_id);
	}

}
