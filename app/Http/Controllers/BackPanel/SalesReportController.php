<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\Report\Sales;
use App\Models\Refund;
use Illuminate\Http\Request;

class SalesReportController extends Controller
{
    // ── Main view (shell only — no data loaded here) ───────────
    public function index()
    {
        return view('backend.report.sales.index');
    }

    // ── AJAX data endpoint ─────────────────────────────────────
    public function data(Request $request)
    {
        $post               = $request->all();
        $post['orgid']      = session('orgid');
        $post['filter']     = $request->input('filter', 'month');
        $post['from_date']  = $request->input('from_date');
        $post['to_date']    = $request->input('to_date');
        $post['month']      = $request->input('month', now()->format('Y-m'));
        $post['year']       = $request->input('year', now()->year);

        $result = Sales::salesReport($post);

        return response()->json([
            'type'              => 'success',
            'summary'           => $result['summary'],
            'rows'              => $result['rows'],
            'trend'             => $result['trend'],
            'top_products'      => $result['top_products'],
            'payment_breakdown' => $result['payment_breakdown'],
        ]);
    }

    // ── Export CSV (controller streams file) ───────────────────
    public function exportExcel(Request $request)
    {
        $post               = $this->buildPost($request);
        $result             = Sales::salesReport($post);
        $rows               = $result['rows'];
        $s                  = $result['summary'];
        $filename           = 'sales_report_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($rows, $s) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['#', 'Order ID', 'Product', 'Qty', 'Unit Price', 'Total', 'Payment', 'Date', 'Status']);

            foreach ($rows as $i => $row) {
                fputcsv($handle, [
                    $i + 1,
                    $row->order_id,
                    $row->product_name,
                    $row->quantity,
                    number_format($row->price, 2),
                    number_format($row->total_price, 2),
                    $row->payment_method ?? 'N/A',
                    \Carbon\Carbon::parse($row->order_date)->format('d M Y'),
                    $row->status ?? 'N/A',
                ]);
            }

            // Summary footer
            fputcsv($handle, []);
            fputcsv($handle, ['', '', 'TOTAL REVENUE', '', '', number_format($s['total_sales'], 2), '', '', '']);
            fputcsv($handle, ['', '', 'TOTAL ORDERS',  $s['total_orders'], '', '', '', '', '']);
            fputcsv($handle, ['', '', 'TOTAL QTY',     $s['total_qty'], '', '', '', '', '']);
            fputcsv($handle, ['', '', 'AVG ORDER VALUE', '', '', number_format($s['avg_order_value'], 2), '', '', '']);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ── Export PDF (renders print blade, returned as HTML page) ─
    public function exportPdf(Request $request)
    {
        $post   = $this->buildPost($request);
        $result = Sales::salesReport($post);

        $html = view('backend.report.sales.print', [
            'result' => $result,
            'post'   => $post,
        ])->render();

        // If barryvdh/laravel-dompdf is installed, swap to:
        // return \PDF::loadHTML($html)->download('sales_report_' . now()->format('Ymd') . '.pdf');

        // Fallback: open print-ready HTML in new tab
        return response($html)->header('Content-Type', 'text/html');
    }

    // ── Shared filter builder ──────────────────────────────────
    private function buildPost(Request $request): array
    {
        return [
            'orgid'     => session('orgid'),
            'filter'    => $request->input('filter', 'month'),
            'from_date' => $request->input('from_date'),
            'to_date'   => $request->input('to_date'),
            'month'     => $request->input('month', now()->format('Y-m')),
            'year'      => $request->input('year', now()->year),
        ];
    }
}