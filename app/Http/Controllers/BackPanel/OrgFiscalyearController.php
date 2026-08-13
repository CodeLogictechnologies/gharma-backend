<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\OrgFiscalYear;
use App\Models\BackPanel\Fiscalyear;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Database\QueryException;

class OrgFiscalYearController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('view.org-fiscalyear')) {
            abort(403);
        }

        return view('backend.orgfiscalyrs.index');
    }

    public function list(Request $request)
    {
        try {
            if (!auth()->user()->can('view.org-fiscalyear')) {
                throw new Exception('You do not have permission to view this data.');
            }

            $post          = $request->all();
            $post['orgid'] = session('orgid');
            $data          = OrgFiscalYear::listFiscalYears($post);

            $i            = 0;
            $array        = [];
            $totalrecs    = $data["totalrecs"];
            $filtereddata = ($data["totalfilteredrecs"] > 0 ? $data["totalfilteredrecs"] : $totalrecs);
            $currentFyId  = $data["current_fy_id"];

            foreach ($data["data"] as $row) {
                $array[$i]["sno"]        = $i + 1;
                $array[$i]["code"]       = $row->code ?? '-';
                $array[$i]["start_date"] = $row->start_date ?? '-';
                $array[$i]["end_date"]   = $row->end_date ?? '-';

                // Checkbox for selecting fiscal year - single select
                $checked = ($row->id === $currentFyId) ? 'checked' : '';
                $array[$i]["checkbox"] = '
                    <div class="form-check d-flex justify-content-center">
                        <input class="form-check-input fy-checkbox" type="checkbox" 
                            name="fiscal_year_id" 
                            value="' . $row->id . '" 
                            data-code="' . htmlspecialchars($row->code) . '"
                            ' . $checked . '>
                    </div>
                ';

                $i++;
            }

            if (!$filtereddata) $filtereddata = 0;
            if (!$totalrecs)    $totalrecs    = 0;
        } catch (QueryException $e) {
            $array = [];
            $totalrecs = 0;
            $filtereddata = 0;
        } catch (Exception $e) {
            $array = [];
            $totalrecs = 0;
            $filtereddata = 0;
        }

        return json_encode([
            "recordsFiltered" => $filtereddata,
            "recordsTotal"    => $totalrecs,
            "data"            => $array,
        ]);
    }

    // public function assign(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'fiscal_year_id' => 'nullable|string|exists:fiscal_years,id',
    //         ]);

    //         $post = $request->all();
    //         $post['orgid'] = session('orgid');

    //         OrgFiscalYear::assignFiscalYear($post);

    //         // Get fiscal year code for message
    //         $fyCode = null;
    //         if (!empty($post['fiscal_year_id'])) {
    //             $fy = Fiscalyear::find($post['fiscal_year_id']);
    //             $fyCode = $fy ? $fy->code : null;
    //         }

    //         $message = $fyCode
    //             ? "Fiscal year {$fyCode} assigned successfully."
    //             : "Fiscal year unassigned successfully.";

    //         return response()->json([
    //             'status'  => true,
    //             'type'    => 'success',
    //             'message' => $message,
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::error('OrgFiscalYear assign error: ' . $e->getMessage());

    //         return response()->json([
    //             'status'  => false,
    //             'type'    => 'error',
    //             'message' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function assign(Request $request)
    {
        try {
            if (!auth()->user()->can('edit.org-fiscalyear')) {
                throw new Exception('You do not have permission to perform this action.');
            }

            $request->validate([
                'fiscal_year_id' => 'nullable|string|exists:fiscal_years,id',
            ]);

            $post = $request->all();
            $orgId = session('orgid');

            // Update organization table
            DB::table('organizations')
                ->where('id', $orgId)
                ->update([
                    'current_fiscal_year_id' => $post['fiscal_year_id'] ?? null,
                    'updated_at' => now(),
                ]);

            // Update session immediately
            if (!empty($post['fiscal_year_id'])) {
                $fy = DB::table('fiscal_years')
                    ->where('id', $post['fiscal_year_id'])
                    ->select('id', 'code')
                    ->first();

                session([
                    'fiscal_year_id'   => $fy->id,
                    'fiscal_year_code' => $fy->code,
                ]);
            } else {
                session([
                    'fiscal_year_id'   => null,
                    'fiscal_year_code' => null,
                ]);
            }

            $message = !empty($post['fiscal_year_id'])
                ? "Fiscal year {$fy->code} assigned successfully."
                : "Fiscal year unassigned successfully.";

            return response()->json([
                'status'  => true,
                'type'    => 'success',
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            Log::error('OrgFiscalYear assign error: ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'type'    => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
