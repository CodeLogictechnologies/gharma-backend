<?php

namespace App\Models\BackPanel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Fiscalyear extends Model
{
    use HasUuids;

    protected $table = 'fiscal_years';

    protected $fillable = [
        'code',
        'start_date',
        'end_date',
        'is_current',
        'status',
    ];

    protected static function booted()
    {
        // Whenever one fiscal year is marked current, unset it on all others
        static::saving(function (Fiscalyear $fy) {
            if ($fy->is_current === 'Y') {
                static::where('id', '!=', $fy->id)->update(['is_current' => 'N']);
            }
        });
    }


    public static function list($post)
    {
        try {
            $get = $post;

            $searchCode = strtolower(trim($get['sSearch_1'] ?? ''));

            $cond = "f.status = 'Y'";

            if (!empty($searchCode)) {
                $cond .= " AND LOWER(f.code) LIKE '%{$searchCode}%'";
            }

            $limit  = isset($get['iDisplayLength']) ? (int) $get['iDisplayLength'] : 10;
            $offset = isset($get['iDisplayStart']) ? (int) $get['iDisplayStart'] : 0;

            $totalRecords = Fiscalyear::from('fiscal_years as f')
                ->whereRaw($cond)
                ->count();

            $query = Fiscalyear::from('fiscal_years as f')
                ->select(
                    'f.id',
                    'f.start_date',
                    'f.end_date',
                    'f.code',
                    'f.is_current'
                )
                ->whereRaw($cond)
                ->orderByRaw('COALESCE(f.updated_at, f.created_at) DESC');

            if ($limit != -1) {
                $query->offset($offset)->limit($limit);
            }

            $result = $query->get();

            $ndata = collect($result);
            $ndata['totalrecs'] = $totalRecords;
            $ndata['totalfilteredrecs'] = $totalRecords;

            return [
                'data' => $result,
                'totalrecs' => $totalRecords,
                'totalfilteredrecs' => $totalRecords,
            ];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function saveData($post)
    {
        try {
            DB::beginTransaction();

            $id = $post['id'] ?? null;

            if ($id) {

                DB::table('fiscal_years')
                    ->where('id', $id)
                    ->update([
                        'start_date' => $post['start_date'],
                        'code'       => $post['code'],
                        'end_date'   => $post['end_date'],
                        'is_current' => $post['is_current'] ?? 'N',
                        'status'     => $post['status'] ?? 'Y',
                        'updated_at' => Carbon::now(),
                    ]);
            } else {

                DB::table('fiscal_years')->insert([
                    'id'         => (string) Str::uuid(),
                    'start_date' => $post['start_date'],
                    'code'       => $post['code'],
                    'end_date'   => $post['end_date'],
                    'is_current' => $post['is_current'] ?? 'N',
                    'status'     => $post['status'] ?? 'Y',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
