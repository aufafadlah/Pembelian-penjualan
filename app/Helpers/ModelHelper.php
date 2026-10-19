<?php

namespace App\Helpers;

use App\Models\ModelHasRoles;
use App\Models\Permissions;
use App\Models\RoleHasPermissions;
use App\Models\Roles;
use App\Models\Users;
use DB;
use Str;

class ModelHelper
{
    private static $operators = [
        "\$gt" => ">",
        "\$gte" => ">=",
        "\$lte" => "<=",
        "\$lt" => "<",
        "\$like" => "like",
        "\$ilike" => "ilike",
        "\$not" => "<>",
        "\$in" => "in"
    ];

    public static function select($schema, $request = null, $class) 
    {
    	$selects = [];
    	$params = [];

    	if ($request) {
    		$params = $request->all();
    	}

    	if (isset($params['select']) && $params['select']) {
    		$selects = explode(',', $params['select']);
    	}

        $_select = [];

        foreach(array_values($schema) as $select) {
        	if ($selects) {
	        	if (in_array($select['alias'], $selects)) {
		        	if (isset($select['is_raw']) && $select['is_raw']) {
		        		$_select[] = DB::raw($select['column'] . ' as '. $select['alias']);
		        	} else {
		        		$_select[] = $select['column'] . ' as '. $select['alias'];
		        	}
	        	}
        	} else {
	        	if (isset($select['is_raw']) && $select['is_raw']) {
	        		$_select[] = DB::raw($select['column'] . ' as '. $select['alias']);
	        	} else {
	        		$_select[] = $select['column'] . ' as '. $select['alias'];
	        	}
        	}
        }

        return $class::select($_select);
    }

    public static function join($schema, $request = null, $model) 
    {
        foreach($schema as $join) {
            if ($join['type'] == 'left') {
            	if (count($join['on']) < 3) {
            		$model->leftJoin($join['table'], function($q) use ($join) {
            			foreach ($join['on'] as $single_join) {
            				if (count($single_join) > 3) {
            					if (!$single_join[3]) {
            						$q->on($single_join[0], $single_join[1], $single_join[2]);
            					} else {
            						$q->on($single_join[0], $single_join[1], DB::raw("'".$single_join[2]."'"));
            					}
            				} else {
            					$q->on($single_join[0], $single_join[1], DB::raw("'".$single_join[2]."'"));
            				}
            			}
				    });
            	} else {
            		$model->leftJoin($join['table'], function($q) use ($join) {
            			$q->on([$join['on']]);
            			if (isset($join['is_softdelete']) && $join['is_softdelete']) {
            				$q->whereNull($join['table'].'.deleted_at');
            			}
            		});
            	}
                
            } else {
                $model->join($join['table'], [$join['on']]);
            }
        }
    }

	public static function dynamicFilterAnd($params, $request, $model, $class)
	{
        foreach (array($params) as $k => $v) {
            foreach (array_keys($v) as $key => $row) {
                if (isset($class::mapSchema()['field'][$row])) {
                	$column = $class::mapSchema()['field'][$row]['column'];
                	if (isset($class::mapSchema()['field'][$row]['is_raw']) && $class::mapSchema()['field'][$row]['is_raw']) {
                		$column = DB::raw($column);
                	}

                    if (is_array(array_values($v)[$key])) {
                        if (count(array_values($v)[$key]) > 0) {
                            foreach(array_values($v)[$key] as $keyOpr => $valOpr) {
                                if (self::$operators[$keyOpr] != 'ilike') {
                                	if (self::$operators[$keyOpr] == '<>' && $valOpr == 'null') {
                                		$model->whereNotNull($column);
                                		$model->where($column, '!=', '');
                                	} else if (self::$operators[$keyOpr] == 'in') {
                                		$string_to_arr = explode(',', $valOpr);
                                		$model->whereIn($column, $string_to_arr);
                                	} else {
                                		$model->where($column, self::$operators[$keyOpr], $valOpr);
                                	}
                                } else {
                                    $model->where($column, 'ilike', '%'.$valOpr.'%');
                                }
                            }
                        }
                    } else {
                        if ($class::mapSchema()['field'][$row]['type'] === 'int') {
                        	if (array_values($v)[$key] != 'null') {
                        		$model->where($column, array_values($v)[$key]);
                        	} else if (array_values($v)[$key] == 'null') {
                        		$model->whereNull($column);
                        	} else if (array_values($v)[$key] == 'not_null') {
                        		$model->whereNotNull($column);
                        	}
                        } else {
                        	if (array_values($v)[$key] != 'null') {
                        		$model->where($column, 'ilike', '%'.array_values($v)[$key].'%');
                        	} else if (array_values($v)[$key] == 'null') {
                        		$model->whereNull($column);
                        	} else if (array_values($v)[$key] == 'not_null') {
                        		$model->whereNotNull($column);
                        	}
                        }
                    }
                }
            }
        }
	}

	public static function dynamicFilterOr($params, $request, $model, $class)
	{
		$n = 0;
		$comparison_total = -1;

	    foreach($params as $orKey => $orVal) {
	        if (isset($class::mapSchema()['field'][$orKey])) {
	        	$explode_if_got_separator = explode('||', $orVal);
	        	foreach ($explode_if_got_separator as $val) {
	        		$comparison_total += 1;
	        	}
	        }
	    }

	    foreach($params as $orKey => $orVal) {
	        if (isset($class::mapSchema()['field'][$orKey])) {
	        	$explode_if_got_separator = explode('||', $orVal);
	        	foreach ($explode_if_got_separator as $val) {
	        		if ($val == 'null') {
		                if ($n < 1) {
		                    $model->whereRaw('( '.$class::mapSchema()['field'][$orKey]['column'] . ' IS NULL');
		                } else if ($n > 0 && $n < $comparison_total) {
		                    $model->orWhereRaw($class::mapSchema()['field'][$orKey]['column'] . ' IS NULL');
		                } else {
		                    $model->orWhereRaw($class::mapSchema()['field'][$orKey]['column'] . ' IS NULL)');
		                }
	        		} else if ($val == 'not_null') {
		                if ($n < 1) {
		                    $model->whereRaw('( '.$class::mapSchema()['field'][$orKey]['column'] . ' IS NOT NULL');
		                } else if ($n > 0 && $n < $comparison_total) {
		                    $model->orWhereRaw($class::mapSchema()['field'][$orKey]['column'] . ' IS NOT NULL');
		                } else {
		                    $model->orWhereRaw($class::mapSchema()['field'][$orKey]['column'] . ' IS NOT NULL)');
		                }
	        		} else {
			            if ($class::mapSchema()['field'][$orKey]['type'] === 'int') {
			                if ($n < 1) {
			                    $model->whereRaw('( '.$class::mapSchema()['field'][$orKey]['column'] . ' = \'' .$val.'\'');
			                } else if ($n > 0 && $n < $comparison_total) {
			                    $model->orWhereRaw($class::mapSchema()['field'][$orKey]['column'] . ' = \''.$val.'\'');
			                } else {
			                    $model->orWhereRaw($class::mapSchema()['field'][$orKey]['column'] . ' = \'' .$val.'\' )');
			                }
			            } else {
			                if ($n < 1) {
			                    $model->whereRaw('( '.$class::mapSchema()['field'][$orKey]['column'] . ' ilike \'%'.$val.'%\'');
			                } else if ($n > 0 && $n < $comparison_total) {
			                    $model->orWhereRaw($class::mapSchema()['field'][$orKey]['column'] . ' ilike \'%'.$val.'%\'');
			                } else {
			                    $model->orWhereRaw($class::mapSchema()['field'][$orKey]['column'] . ' ilike \'%'.$val.'%\')');
			                }
			            }
	        		}
		            $n++;
	        	}
	        }
	    }
	}
	
	public static function generateAllResults($schema, $params, $request, $model, $append = [])
	{
		if (isset($params['order']) && is_array($params['order'])) {
			foreach ($params['order'] as $orderKey => $orderVal) {
				if (is_array($orderVal)) {
					foreach ($orderVal as $key => $val) {
						$model->orderBy($schema['field'][$key]['column'], $val);
					}
				} else {
					$model->orderBy($schema['field'][$orderKey]['column'], $orderVal);
				}
			}
		}

		$data = $model->get();

		return self::response($data, false);
	}

	public static function generatePagingResults($schema, $page, $params, $request, $model, $append = [])
	{
		$per_page = 10;

		if (isset($params['order']) && is_array($params['order'])) {
			foreach ($params['order'] as $orderKey => $orderVal) {
				if (is_array($orderVal)) {
					foreach ($orderVal as $key => $val) {
						$model->orderBy($schema['field'][$key]['column'], $val);
					}
				} else {
					$model->orderBy($schema['field'][$orderKey]['column'], $orderVal);
				}
			}
		}

		if (isset($params['per_page']) && $params['per_page'] > 0) {
			$per_page = $params['per_page'];
		}
		
        $countAll = $model->count();
        $currentPage = $page > 0 ? $page - 1 : 0;
        $page = $page > 0 ? $page + 1 : 2; 
        $nextPage = $request->url().'?page='.$page;
        $prevPage = $request->url().'?page='.($currentPage < 1 ? 1 : $currentPage);
        $totalPage = ceil((int)$countAll / $per_page);

        $model->skip($currentPage * $per_page)
           ->take($per_page);

		$data = $model->get();
		
		$results['totalData'] = $countAll;
		$results['nextPage'] = $nextPage;
		$results['prevPage'] = $prevPage;
		$results['totalPage'] = $totalPage;
		$results['data'] = $data;

		return self::response($results, true);
	}

	public static function response($params, $is_paging)
	{
		$results = $params;

		if ($is_paging) {
			$results = [
	            'nav' => [
	                'totalData' => $params['totalData'],
	                'nextPage' => $params['nextPage'],
	                'prevPage' => $params['prevPage'],
	                'totalPage' => $params['totalPage']
	            ],
	            'data' => $params['data']
			];
		}

		return $results;
	}

	public static function adjustSequencePostgreSql()
	{
		$tables = DB::select(DB::raw("SELECT table_name FROM information_schema.tables WHERE table_schema='public' AND table_type='BASE TABLE'")->getValue(DB::getQueryGrammar()));

        foreach ($tables as $table) {
            $primary_key = DB::select(DB::raw("SELECT               
              pg_attribute.attname, 
              format_type(pg_attribute.atttypid, pg_attribute.atttypmod) 
            FROM pg_index, pg_class, pg_attribute, pg_namespace 
            WHERE 
              pg_class.oid = '".$table->table_name."'::regclass AND 
              indrelid = pg_class.oid AND 
              nspname = 'public' AND 
              pg_class.relnamespace = pg_namespace.oid AND 
              pg_attribute.attrelid = pg_class.oid AND 
              pg_attribute.attnum = any(pg_index.indkey)
             AND indisprimary")->getValue(DB::getQueryGrammar()));

            if ($table->table_name && isset($primary_key[0]->attname)) {
                $sequence_name = DB::select(DB::raw("SELECT * FROM information_schema.sequences WHERE sequence_name = '".$table->table_name."_".$primary_key[0]->attname."_seq' ")->getValue(DB::getQueryGrammar()));
                if (isset($sequence_name[0]->sequence_name)) {
                    DB::select(
                    	DB::raw(
                    		"SELECT SETVAL('".$sequence_name[0]->sequence_name."', (SELECT MAX(".$primary_key[0]->attname.") + 1 FROM ".$table->table_name."))"
                		)->getValue(DB::getQueryGrammar())
					);
                }
            }
        }
	}

	public static function debugSql($query)
	{
		dd(Str::replaceArray('?', $query->getBindings(), $query->toSql()));
	}

	// author : Maulana R
	// Ini untuk digunakan dalam with, fungsi bawaan orm larave, tetapi dengan skema sesuap map schema, jadi semua kondisi & join pada schema akan terbawa
	// contoh penggunaan : 
	// $db->with(['purchase_order_details' => function ($query) {
    //      ModelHelper::joinFullSchema(PurchaseOrderDetails::class,$query);
	// }]);

	public static function joinFullSchema($schemaClass, &$query) {
		$x = $schemaClass::mapSchema();
		$_select = [];

		foreach(array_values($x['field']) as $select) {
			if (isset($select['is_raw']) && $select['is_raw']) {
				$_select[] = DB::raw($select['column'] . ' as '. $select['alias']);
			} else {
				$_select[] = $select['column'] . ' as '. $select['alias'];
			}
		}
		foreach($x['join'] as $join) {
			if ($join['type'] == 'left') {
				if (!is_array($join['on'][0])) {
					if (count($join['on']) < 3) {
						$query->leftJoin($join['table'], function($q) use ($join) {
							foreach ($join['on'] as $single_join) {
								if (count($single_join) > 3) {
									if (!$single_join[3]) {
										$q->on($single_join[0], $single_join[1], $single_join[2]);
									} else {
										$q->on($single_join[0], $single_join[1], DB::raw("'".$single_join[2]."'"));
									}
								} else {
									$q->on($single_join[0], $single_join[1], DB::raw("'".$single_join[2]."'"));
								}
							}
						});
					} else {
						$query->leftJoin($join['table'], function($q) use ($join) {
							$q->on([$join['on']]);
							if (isset($join['is_softdelete']) && $join['is_softdelete']) {
								$q->whereNull($join['table'].'.deleted_at');
							}
						});
					}
				} else {
					$query->leftJoin($join['table'], function($q) use ($join) {
						// dd($join['on']);
						foreach ($join['on'] as $key => $join_on) {
							if (count($join_on) < 3) {
								foreach ($join_on as $single_join) {
									if (count($single_join) > 3) {
										if (!$single_join[3]) {
											$q->on($single_join[0], $single_join[1], $single_join[2]);
										} else {
											$q->on($single_join[0], $single_join[1], DB::raw("'".$single_join[2]."'"));
										}
									} else {
										$q->on($single_join[0], $single_join[1], DB::raw("'".$single_join[2]."'"));
									}
								}
							} else {
								$join_type = false;
								if (isset($join_on[3]) && $join_on[3]) {
									$join_type = $join_on[3];
								}

								if ($join_type == 'andWhere') {
									if ($join_on[1] == '=' && $join_on[2] == 'null') {
										$q->whereNull($join_on[0]);
									} else {
										$q->where($join_on[0], $join_on[1], $join_on[2]);
									}
								} else if ($join_type == 'orWhere') {
									if ($join_on[1] == '=' && $join_on[2] == 'null') {
										$q->orWhereNull($join_on[0]);
									} else {
										$q->orWhere($join_on[0], $join_on[1], $join_on[2]);
									}
								} else if (!$join_type) {
									// if ($key > 0) {
									// 	dd($join_on[0], $join_on[1], $join_on[2]);
									// }
									$q->on($join_on[0], $join_on[1], $join_on[2]);
								} else {
									$q->on($join_on[0], $join_on[1], DB::raw("'".$join_on[2]."'"));
								}
								if (isset($join['is_softdelete']) && $join['is_softdelete']) {
									$q->whereNull($join['table'].'.deleted_at');
								}
							}
						}	
					});
				}
			} else {
				$query->join($join['table'], [$join['on']]);
			}
		}
		$query->select($_select);
	}

	// author : Maulana R
	// ini digunakan dalam join lewat orm laravel, sehingga return sesuai dengan skema yang digunakan pada map schema.
	// contoh penggunaan :
	// $foo = ModelHelper::FullSchema(ContractBillOfQuantityDetails::class)->where('type', 'job')->get();
	public static function FullSchema($schemaClass) 
	{
		$x = $schemaClass::mapSchema();
		$db = self::select($x['field'], NULL, $schemaClass);
		self::join($x['join'], NULL, $db);
		
		return $db;
	}

	public static function reorderPermissionAdmin()
    {
		$superadmin_role_id = 1;
		
		RoleHasPermissions::where('role_id', $superadmin_role_id)->delete();

		$filtered_data = [];

		$data = Permissions::get();

		foreach ($data as $row) {
			$filtered_data[] = [
				'permission_id' => $row['id'],
				'role_id' => $superadmin_role_id
			];
		}

		RoleHasPermissions::insert($filtered_data);

		// $users = Users::select('id', 'role_id')->where('role_id', $superadmin_role_id)->get();
		$users = Users::select('id', 'name', 'email', 'role_id')->get();
		$roles = Roles::select('id', 'name')->get()->pluck('name', 'id')->toArray();

		ModelHasRoles::where('model_type', 'App\Models\Users')->delete();

		foreach ($users as $user) {
			if (isset($roles[$user['role_id']])) {
				ModelHasRoles::create([
					'role_id' => $user['role_id'],
					'model_type' => 'App\Models\Users',
					'model_id' => $user['id']
				]);

				// echo $user['email'].' '.$user['name'].' '.$roles[$user['role_id']]." OK.\n";
			} else {
				// echo $user['email'].' '.$user['name'].' '.$roles[$user['role_id']]." UNKNOWN ROLE_ID [".$user['role_id']."].\n";
			}
		}
    }
}

