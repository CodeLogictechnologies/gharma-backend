<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\Report\Inventory;
use Illuminate\Http\Request;

class InventoryReportController extends Controller
{
    // ── Main view (shell only) ─────────────────────────────────
    public function index()
    {
        return view('backend.report.inventory.index');
    }

    // ── AJAX data endpoint ─────────────────────────────────────
    public function data(Request $request)
    {
        $post          = $request->all();
        $post['orgid'] = session('orgid');

        $rows = Inventory::getData($post);


        $total        = $rows->count();
        $inStock      = $rows->filter(fn($r) => $r->available_qty > $r->threshold)->count();
        $lowStock     = $rows->filter(fn($r) => $r->available_qty > 0 && $r->available_qty <= $r->threshold)->count();
        // dd($lowStock);
        $outOfStock   = $rows->filter(fn($r) => $r->available_qty <= 0)->count();
        $totalSold    = $rows->sum('sold_qty');

        return response()->json([
            'type' => 'success',
            'summary' => [
                'total'       => $total,
                'in_stock'    => $inStock,
                'low_stock'   => $lowStock,
                'out_of_stock' => $outOfStock,
                'total_sold'  => $totalSold,
            ],
            'rows' => $rows->values(),
        ]);
    }

    // ── Shared post builder ────────────────────────────────────
    private function buildPost(Request $request): array
    {
        return [
            'orgid'  => session('orgid'),
        ];
    }

    // ── Export CSV ─────────────────────────────────────────────
    public function exportExcel(Request $request)
    {
        $post = $this->buildPost($request);
        $rows = Inventory::getData($post);

        $filename = 'inventory_report_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['#', 'Product Name', 'Total Stock', 'Available Qty', 'Sold Qty', 'Threshold', 'Status']);

            foreach ($rows as $i => $row) {
                if ($row->available_qty <= 0) {
                    $status = 'Out of Stock';
                } elseif ($row->available_qty <= $row->threshold) {
                    $status = 'Low Stock';
                } else {
                    $status = 'In Stock';
                }

                fputcsv($handle, [
                    $i + 1,
                    $row->product_name,
                    number_format($row->stock, 2),
                    number_format($row->available_qty, 2),
                    $row->sold_qty,
                    $row->threshold,
                    $status,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ── Export PDF (print blade) ───────────────────────────────
    public function exportPdf(Request $request)
    {
        $post   = $this->buildPost($request);
        $rows   = Inventory::getData($post);

        $summary = [
            'total'        => $rows->count(),
            'in_stock'     => $rows->filter(fn($r) => $r->available_qty > $r->threshold)->count(),
            'low_stock'    => $rows->filter(fn($r) => $r->available_qty > 0 && $r->available_qty <= $r->threshold)->count(),
            'out_of_stock' => $rows->filter(fn($r) => $r->available_qty <= 0)->count(),
            'total_sold'   => $rows->sum('sold_qty'),
        ];

        $html = view('backend.report.inventory.print', compact('rows', 'summary'))->render();

        // If barryvdh/laravel-dompdf installed:
        // return \PDF::loadHTML($html)->download('inventory_report_' . now()->format('Ymd') . '.pdf');

        return response($html)->header('Content-Type', 'text/html');
    }
}