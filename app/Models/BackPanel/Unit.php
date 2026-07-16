<?php

namespace App\Models\BackPanel;


use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Unit extends Model
{
    public $incrementing = false;
    protected $keyType   = 'string';
    protected $table     = 'units';

    protected $fillable = ['unit_name', 'status', 'orgid', 'postedby', 'updatedby', 'updated_at', 'created_at'];

    /**
     * To save variation attribute
     *
     * @param [type] $post
     * @return void
     */
    public static function saveData($post)
    {
        try {
            $dataArray = [
                'unit_name' => $post['name'],
                'orgid'     => $post['orgid'],
                'status'    => 'Y',
            ];

            if (!empty($post['id'])) {
                $dataArray['updatedby']  = $post['updatedby'] ?? null;
                $dataArray['updated_at'] = Carbon::now();

                if (!Unit::where('id', $post['id'])->update($dataArray)) {
                    throw new Exception("Couldn't update record");
                }
            } else {
                // Restore soft-deleted record if one exists with same orgid+name
                $softDeleted = Unit::where('orgid', $post['orgid'])
                    ->where('unit_name', $post['name'])
                    ->where('status', 'N')
                    ->first();

                if ($softDeleted) {
                    $restoreArray = [
                        'status'     => 'Y',
                        'updatedby'  => $post['updatedby'] ?? null,
                        'updated_at' => Carbon::now(),
                    ];
                    if (!Unit::where('id', $softDeleted->id)->update($restoreArray)) {
                        throw new Exception("Couldn't save record");
                    }
                } else {
                    $dataArray['id']         = (string) Str::uuid();
                    $dataArray['postedby']   = $post['postedby'] ?? null;
                    $dataArray['created_at'] = Carbon::now();
                    if (!Unit::insert($dataArray)) {
                        throw new Exception("Couldn't save record");
                    }
                }
            }

            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * To list all variation attributes.
     *
     * @param [type] $post
     * @return void
     */
    public static function list($post)
    {
        try {
            $get = $post;

            foreach ($get['columns'] as $key => $value) {
                $get['columns'][$key]['search']['value'] = trim(strtolower(htmlspecialchars($value['search']['value'], ENT_QUOTES)));
            }

            $cond = " status = 'Y' AND orgid = '" . addslashes($post['orgid']) . "' ";

            if (!empty($get['columns'][1]['search']['value'])) {
                $cond .= " and lower(unit_name) like '%" . $get['columns'][1]['search']['value'] . "%'";
            }

            $limit  = 15;
            $offset = 0;
            if (!empty($get['length']) && $get['length']) {
                $limit  = $get['length'];
                $offset = $get['start'];
            }

            $query = Unit::selectRaw("(SELECT count(*) FROM units WHERE {$cond}) AS totalrecs, unit_name as name, id")
                ->whereRaw($cond);

            if ($limit > -1) {
                $result = $query->orderBy('id', 'desc')->offset($offset)->limit($limit)->get();
            } else {
                $result = $query->orderBy('id', 'desc')->get();
            }

            if ($result) {
                $ndata               = $result;
                $ndata['totalrecs']          = @$result[0]->totalrecs ? $result[0]->totalrecs : 0;
                $ndata['totalfilteredrecs']  = @$result[0]->totalrecs ? $result[0]->totalrecs : 0;
            } else {
                $ndata = [];
            }

            return $ndata;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * To delete record of variation attribute
     *
     * @param [type] $post
     * @return void
     */
    public static function deleteRecord($post)
    {
        try {
            $updateArray = [
                'status'     => 'N',
                'updated_at' => Carbon::now(),
            ];

            if (!Unit::where('id', $post['id'])
                ->where('orgid', $post['orgid'])
                ->update($updateArray)) {
                throw new Exception("Couldn't delete record. Please try again.");
            }

            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }


    public static function getUnit($post)
    {
        try {
            $query = DB::table('units')
                ->select('id', 'unit_name')
                ->where('orgid', $post['orgid'])
                ->where('status', 'Y');

            // if (!empty($post['brandid'])) {
            //     $query->where('id', $post['brandid']);
            // }

            $result = $query
                ->orderBy('unit_name', 'asc')
                ->get();

            return $result;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
