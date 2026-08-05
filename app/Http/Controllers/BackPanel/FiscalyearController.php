<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\Fiscalyear;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Database\QueryException;

class FiscalyearController extends Controller
{
    public function index()
    {
        return view('backend.fiscalyrs.index');
    }

    public function list(Request $request)
    {
        try {
            $post          = $request->all();
            $post['orgid'] = session('orgid');
            $data          = Fiscalyear::list($post);

            $i            = 0;
            $array        = [];
            $totalrecs    = $data["totalrecs"];
            $filtereddata = ($data["totalfilteredrecs"] > 0 ? $data["totalfilteredrecs"] : $totalrecs);

            foreach ($data["data"] as $row) {
                $array[$i]["sno"]     = $i + 1;
                $array[$i]["code"]    = $row->code ?? '-';
                $array[$i]["start_date"]   = $row->start_date ?? '-';
                $array[$i]["end_date"] = $row->end_date ?? '-';
                $array[$i]["is_current"]   = $row->is_current ?? '-';

                $action  = '';
                $action .= '<a href="javascript:;" title="Edit"   class="tooltipdiv editfiscalyear  btn-edit-fy " style="color:#696cff;font-size:18px;" data-id="' . $row->id . '"><i class="bx bx-edit-alt"></i></a> ';
                $action .= '<a href="javascript:;" title="Delete" class="tooltipdiv deletefiscalyear btn-delete-fy" style="color:red;font-size:18px;"   data-id="' . $row->id . '"><i class="bx bx-trash"></i></a>';
                $array[$i]["action"] = $action;

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

    public function form(Request $request)
    {
        try {
            $fiscalyear = $request->id ? Fiscalyear::findOrFail($request->id) : null;

            return view('backend.fiscalyrs.form', compact('fiscalyear'));
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Fiscal year not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Fiscalyear form error: ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'Unable to load the form. Please try again.',
            ], 500);
        }
    }

    public function save(Request $request)
    {
        try {
            $request->validate([
                'start_date' => 'required|string',
                'end_date'   => 'required|string',
                'is_current' => 'nullable|in:Y,N',
                'status'     => 'nullable|in:Y,N',
            ]);

            $startYear = explode('-', $request->start_date)[0] ?? '';
            $endYear   = explode('-', $request->end_date)[0] ?? '';

            if (strlen($startYear) < 3 || strlen($endYear) < 3) {
                return response()->json([
                    'status'  => false,
                    'type'    => 'error',
                    'message' => 'Invalid start or end date format.',
                ], 422);
            }

            if ($request->end_date <= $request->start_date) {
                return response()->json([
                    'status'  => false,
                    'type'    => 'error',
                    'message' => 'End date must be after start date.',
                ], 422);
            }

            // Compute code server-side; never trust client-supplied code_preview
            $code = substr($startYear, -3) . '/' . substr($endYear, -3);

            if (empty($code)) {
                return response()->json([
                    'status'  => false,
                    'type'    => 'error',
                    'message' => 'Unable to generate fiscal year code.',
                ], 422);
            }

            $post = $request->all();
            $post['code'] = $code;

            // $exists = Fiscalyear::where('code', $code)
            //     ->when($request->id, fn($q) => $q->where('id', '!=', $request->id))
            //     ->exists();

            // if ($exists) {
            //     return response()->json([
            //         'status'  => false,
            //         'type'    => 'error',
            //         'message' => "Fiscal year {$code} already exists.",
            //     ], 422);
            // }

            $exists = Fiscalyear::where('code', $code)
                ->when($request->id, fn($q) => $q->where('id', '!=', $request->id))
                ->exists();

            if ($exists) {
                return response()->json([
                    'status'  => false,
                    'type'    => 'error',
                    'message' => "Fiscal year {$code} already exists.",
                ], 422);
            }

            $dateExists = Fiscalyear::where('start_date', $request->start_date)
                ->where('end_date', $request->end_date)
                ->when($request->id, fn($q) => $q->where('id', '!=', $request->id))
                ->exists();

            if ($dateExists) {
                return response()->json([
                    'status'  => false,
                    'type'    => 'error',
                    'message' => 'A fiscal year with this start and end date already exists.',
                ], 422);
            }

            Fiscalyear::saveData($post);

            return response()->json([
                'status'  => true,
                'type'    => 'success',
                'message' => 'Fiscal year saved successfully.',
            ]);
        } catch (QueryException $e) {
            Log::error('Fiscalyear save DB error: ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'type'    => 'error',
                'message' => 'A error occurred while saving. Please try again.',
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'type'    => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        try {
            $deleted = Fiscalyear::where('id', $request->id)->delete();

            if (!$deleted) {
                return response()->json([
                    'type'    => 'error',
                    'message' => 'Fiscal year not found.',
                ], 404);
            }

            return response()->json([
                'type'    => 'success',
                'message' => 'Fiscal year deleted successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Fiscalyear delete error: ' . $e->getMessage());

            return response()->json([
                'type'    => 'error',
                'message' => 'Unable to delete fiscal year. Please try again.',
            ], 500);
        }
    }

    public function view(Request $request)
    {
        try {
            $fiscalyear = Fiscalyear::findOrFail($request->id);

            return response()->json($fiscalyear);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Fiscal year not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Fiscalyear view error: ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'Unable to load fiscal year details.',
            ], 500);
        }
    }
}
