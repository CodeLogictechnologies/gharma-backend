<?php

namespace App\Models\BackPanel;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VariationAttribute extends Model
{
    public $incrementing = false;
    protected $keyType   = 'string';
    protected $table     = 'variation_attributes';

    protected $fillable = ['name', 'status', 'orgid', 'postedby', 'updatedby'];

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
                'name'   => $post['name'],
                'orgid'  => $post['orgid'],
                'status' => 'Y',
            ];

            if (!empty($post['id'])) {
                $dataArray['updatedby']  = $post['updatedby'] ?? null;
                $dataArray['updated_at'] = Carbon::now();

                if (!VariationAttribute::where('id', $post['id'])->update($dataArray)) {
                    throw new Exception("Couldn't update record");
                }
            } else {
                // Restore soft-deleted record if one exists with same orgid+name
                $softDeleted = VariationAttribute::where('orgid', $post['orgid'])
                    ->where('name', $post['name'])
                    ->where('status', 'N')
                    ->first();

                if ($softDeleted) {
                    $restoreArray = [
                        'status'     => 'Y',
                        'updatedby'  => $post['updatedby'] ?? null,
                        'updated_at' => Carbon::now(),
                    ];
                    if (!VariationAttribute::where('id', $softDeleted->id)->update($restoreArray)) {
                        throw new Exception("Couldn't save record");
                    }
                } else {
                    $dataArray['id']         = (string) Str::uuid();
                    $dataArray['postedby']   = $post['postedby'] ?? null;
                    $dataArray['created_at'] = Carbon::now();

                    if (!VariationAttribute::insert($dataArray)) {
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
                $cond .= " and lower(name) like '%" . $get['columns'][1]['search']['value'] . "%'";
            }

            $limit  = 15;
            $offset = 0;
            if (!empty($get['length']) && $get['length']) {
                $limit  = $get['length'];
                $offset = $get['start'];
            }

            $query = VariationAttribute::selectRaw("(SELECT count(*) FROM variation_attributes WHERE {$cond}) AS totalrecs, name, id")
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

            if (!VariationAttribute::where('id', $post['id'])
            ->where('orgid', $post['orgid'])
            ->update($updateArray)) {
                throw new Exception("Couldn't delete record. Please try again.");
            }

            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * To fetch variation attributes
     *
     * @param [type] $post
     * @return void
     */
    public static function fetchVariationAttributes($post)
    {
        try {
            return DB::table('variation_attributes')
                ->select('id', 'name')
                ->where('orgid', $post['orgid'])
                ->where('status', 'Y')
                ->orderBy('name')
                ->get();
        } catch (Exception $e) {
            throw $e;
        }
    }
}
