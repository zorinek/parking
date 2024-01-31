<?php

declare(strict_types=1);

namespace App\Model;

use Nette;

/**
 * PreviewFactory
 */
final class PreviewFactory
{

	use Nette\SmartObject;

	/** @var Nette\Database\Context */
	private $db;

	public function __construct(Nette\Database\Context $db)
	{
		$this->db = $db;
	}

	public function provideExport($table, $request, $search_values, $where = false, $values = false)
	{
		if ($values == "all")
		{
			$params = [];
		} else if ($values == "selected")
		{
			$params = $request->getQuery();
		}

		if (empty($params))
		{
			$all = $this->getAll($table, false, false, $where);
//                $all_count = $this->getAllCount($table, $where);
		} else
		{
			$all = $this->search($table, $search_values, false, false, "all", $where);
//                $all_count = $this->search($table, $search_values, false, false, "count", $where);
		}
		$template['all'] = $all;
		$template["values"] = $values;
//            $template['all_count'] = $all_count;
		return $template;
	}

	public function providePreview($table, $page, $redirect_route, $request, $limit, $search_values, $presenter, $lang_file_name, $custom_buttons_path, $where = false)
	{
		$template = [];
		$params = $request->getQuery();
		if (is_null($page))
		{
			$offset = 0;
			$page = 1;
		} else
		{
			$offset = ($page - 1) * $limit;
		}
		if (empty($params))
		{
			$all = $this->getAll($table, $limit, $offset, $where);
			$all_count = $this->getAllCount($table, $where);
			$template['result_type'] = "normal";
		} else
		{
			$all = $this->search($table, $search_values, $limit, $offset, "all", $where);
			$all_count = $this->search($table, $search_values, $limit, $offset, "count", $where);
			$template['params'] = $params;
			$template['result_type'] = "search";
		}

		if ($offset >= $all_count->count && $all_count->count != 0)
		{
//                $this->redirect(":Admin:Unit:previewUnit", 1);
			$presenter->redirect($redirect_route, 1);
		}
		$template['all'] = $all;
		$template['all_count'] = $all_count;
		$template['page'] = $page;
		$template['limit'] = $limit;
		$template['redirect_route'] = $redirect_route;
		$template['lang_file_name'] = $lang_file_name;
		$template['custom_buttons_path'] = $custom_buttons_path;
		return $template;
	}

	public function getAll($table = false, $limit = false, $offset = false, $where = false)
	{
		if ($limit !== false && $offset !== false)
		{
			$get = $this->db->table($table);
			$get->limit($limit, $offset);
			if ($where)
			{
				$get->where($where);
			}
			$get = $get->fetchAll();
		} else
		{
			$get = $this->db->table($table);
			if ($where)
			{
				$get->where($where);
			}
			$get = $get->fetchAll();
		}
		return $get;
	}

	public function getAllCount($table = false, $where = false)
	{
		$get = $this->db->table($table);
		if ($where)
		{
			$get->where($where);
		}
		$get->select("COUNT(*) AS count");

		$get = $get->fetch();
		return $get;
	}

	public function search($table, $values, $limit, $offset, $type, $where = false)
	{
		$ao = "";
		$out = [];
		$out1 = [];
//            foreach($values as $line)
//            dump($values);
//            dump(array_values($values));
		$values = array_values($values);
//            die();
		$order = [];
		$join = [];
		for ($j = 0; $j < count($values) - 1; $j++)
		{
			if ($values[$j][1] == "equal")
			{
				if ($values[$j][2] !== "0")
				{
					$out[] = $values[$j][0] . " = " . $values[$j][2];
					$out1[] = [$values[$j][0], $values[$j][2]];
				}
			} else if ($values[$j][1] == "like")
			{
				if ($values[$j][2] !== "")
				{
					if (isset($values[$j][3]) && $values[$j][3] !== false)
					{
						if ($type !== "count")
						{
							if ($values[$j][7] == "n")
							{
//                                    echo "LTU";
								$out1[] = ["CAST(:" . $values[$j][3] . "(" . $values[$j][8] . ")." . $values[$j][4] . " AS TEXT) LIKE ?", "%" . $values[$j][2] . "%"];
//                                    $out1[] = [":users_roles(use_id).usr_role LIKE ?", "%" . $values[$j][2] . "%"]  ;
//                                    dump(":users_roles(use_id).usr_role LIKE ?", "%" . $values[$j][2] . "%");
//                                    die();
//                                    dump(":" . $values[$j][0] . "(users_roles)." . $values[$j][4] . " LIKE ?", "%" . $values[$j][2] . "%");
//                                    die();
							} else if ($values[$j][2] !== "null")
							{
								$out1[] = ["CAST(" . $values[$j][0] . "." . $values[$j][4] . " AS TEXT) LIKE ?", "%" . $values[$j][2] . "%"];
							} else
							{
								$out1[] = [$values[$j][0] . " IS NULL"];
							}
						}
					} else
					{
						$out[] = "CAST(" . $values[$j][0] . " AS TEXT) LIKE '%" . $values[$j][2] . "%'";
						$out1[] = ["CAST(" . $values[$j][0] . " AS TEXT) LIKE ?", "%" . $values[$j][2] . "%"];
					}
				}
			} else if ($values[$j][1] == "greater_equal")
			{
				if ($values[$j][2] !== "")
				{
					if (isset($values[$j][3]))
					{
						$out[] = $values[$j][3] . " >= '" . $values[$j][2] . "'";
						$out1[] = [$values[$j][3] . " >= ?", $values[$j][2]];
					} else
					{
						$out[] = $values[$j][0] . " >= '" . $values[$j][2] . "'";
						$out1[] = [$values[$j][0] . " >= ?", $values[$j][2]];
					}
				}
			} else if ($values[$j][1] == "lower_equal")
			{
				if ($values[$j][2] !== "")
				{
					if (isset($values[$j][3]))
					{
						$out[] = $values[$j][3] . " <= '" . $values[$j][2] . "'";
						$out1[] = [$values[$j][3] . " <= ?", $values[$j][2]];
					} else
					{
						$out[] = $values[$j][0] . " <= '" . $values[$j][2] . "'";
						$out1[] = [$values[$j][0] . " <= ?", $values[$j][2]];
					}
				}
			} else if ($values[$j][0] == "and_or")
			{
				if ($values[$j][2] == "and")
				{
					$ao = " AND ";
				} else if ($values[$j][2] == "or")
				{
					$ao = " OR ";
				}
			} else if ($values[$j][0] == "order")
			{
				if ($values[$j][2])
				{
					for ($i = 0; $i < count($values[$j][2]); $i++)
					{
						$order[] = $values[$j][2][$i] . " " . strtoupper($values[$j + 1][2][$i]);
					}
				}
			}
		}

//            if($and_or == "and")
//            {
//                $ao = " AND ";
//            }
//            else if($and_or == "or")
//            {
//                $ao = " OR ";
//            }
//            $out = [];
//            if($cs_id != "")
//            {
//                $out[] = "acc_id LIKE '%" . $cs_id . "%'";
//            }
//            if($cs_type != "0")
//            {
//                $out[] = "acc_type = " . $cs_type . "";
//            }
//            if($cs_name != "")
//            {
//                $out[] = "CONCAT(U1.use_name, ' ', U1.use_surname) LIKE '%" . $cs_name . "%'";
//            }
//            if($cs_status != "0")
//            {
//                $out[] = "acc_status = '" . $cs_status . "'";
//            }
//            if($cs_user != "")
//            {
//                $tmp = "( CONCAT(U2.use_name, ' ', U2.use_surname) LIKE '%" . $cs_user . "%'";
//                if(strpos("nepřevzato", strtolower($cs_user)) !== false)
//                {
//                    $tmp .= " OR usa_id_to IS NULL ";
//                }
//                $tmp .= ")";
//                $out[] = $tmp;
//            }

		$sql = implode($ao, $out);
//            dump($out);
//            die();
		if ($sql != "")
		{
			$sql = " WHERE " . $sql;
		}
		$or = implode(", ", $order);
//            if($or != "")
//            {
//                $or = "" . $or;
//            }
//            $or = str_replace("desc", "DESC", $or);
//            $or = str_replace("asc", "ASC", $or);
//            dump($or);
//            die();
//            if(!empty($join))
//            {
//                $jn = implode(", ", $join);
//            }
//            else
//            {
//                $jn = "";
//            }
		if ($type == "all")
		{
			$tp = $table . ".*";
			if ($limit != false)
			{
//                    $get = $this->db->query("SELECT " . $tp . " FROM " . $table . " " . $sql . " " . $or .  " LIMIT ? OFFSET ?", $limit, $offset)->fetchAll();
				$get = $this->db->table($table);
//                    if($jn != "")
//                    {
//                        $get->select($jn);
//                    }
				foreach ($out1 as $ln)
				{
					if (isset($ln[1]))
					{
						$get->where($ln[0], $ln[1]);
					} else
					{
						$get->where($ln[0]);
					}
				}
				if ($or != "")
				{
					$get->order($or);
				}
				$get->limit($limit, $offset);
				if ($where)
				{
					$get->where($where);
				}
				$get = $get->fetchAll();
			} else
			{
//                    $get = $this->db->query("SELECT " . $tp . " FROM " . $table . " " . $sql . " " . $or)->fetchAll();
				$get = $this->db->table($table);
//                    if($jn != "")
//                    {
//                        $get->select($jn);
//                    }
				foreach ($out1 as $ln)
				{
					if (isset($ln[1]))
					{
						$get->where($ln[0], $ln[1]);
					} else
					{
						$get->where($ln[0]);
					}
				}
				if ($or != "")
				{
					$get->order($or);
				}
//                    $get->limit($limit, $offset);
				if ($where)
				{
					$get->where($where);
				}
				$get = $get->fetchAll();
			}
		} else if ($type == "count")
		{
//                $tp = "COUNT(*) AS count ";
//                $get = $this->db->query("SELECT " . $tp . " FROM " . $table . " " . $sql)->fetch();
			$get = $this->db->table($table)->select("COUNT(*) AS count");
			foreach ($out1 as $ln)
			{
				$get->where($ln[0], $ln[1]);
			}
			if ($where)
			{
				$get->where($where);
			}
			$get = $get->fetch();
		}


		return $get;
	}

}
