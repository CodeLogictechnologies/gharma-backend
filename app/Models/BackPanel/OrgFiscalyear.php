<?php

namespace App\Models\BackPanel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrgFiscalYear extends Model
{
    use HasUuids;

    protected $table = 'organizations';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'logo',
        'org_status',
        'status',
        'current_fiscal_year_id',
        'created_at',
        'updated_at',
    ];

    /**
     * Get fiscal years list with checkbox for assignment
     */
    public static function listFiscalYears($post)
    {
        try {
            $orgId = $post['orgid'] ?? session('orgid');

            // Get organization's current fiscal year
            $org = DB::table('organizations')
                ->where('id', $orgId)
                ->select('current_fiscal_year_id')
                ->first();

            $currentFyId = $org->current_fiscal_year_id ?? null;

            $get = $post;

            $searchCode = strtolower(trim($get['sSearch_1'] ?? ''));

            $cond = "f.status = 'Y'";

            if (!empty($searchCode)) {
                $cond .= " AND LOWER(f.code) LIKE '%{$searchCode}%'";
            }

            $limit  = isset($get['iDisplayLength']) ? (int) $get['iDisplayLength'] : 10;
            $offset = isset($get['iDisplayStart']) ? (int) $get['iDisplayStart'] : 0;

            $totalRecords = DB::table('fiscal_years as f')
                ->whereRaw($cond)
                ->count();

            $query = DB::table('fiscal_years as f')
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

            return [
                'data' => $result,
                'totalrecs' => $totalRecords,
                'totalfilteredrecs' => $totalRecords,
                'current_fy_id' => $currentFyId,
            ];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Update organization's current fiscal year
     */
    public static function assignFiscalYear($post)
    {
        try {
            DB::beginTransaction();

            $orgId = $post['orgid'] ?? session('orgid');
            $fiscalYearId = $post['fiscal_year_id'] ?? null;

            if (empty($orgId)) {
                throw new \Exception('Organization ID is required.');
            }

            DB::table('organizations')
                ->where('id', $orgId)
                ->update([
                    'current_fiscal_year_id' => $fiscalYearId,
                    'updated_at' => Carbon::now(),
                ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}