<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Models\API\OrderMaster;
use App\Models\BackPanel\Invoice;
use App\Models\BackPanel\Order;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{

    public function index()
    {
        return view('backend.invoice.index');
    }

    public function list(Request $request)
    {
        $post = $request->all();
        $post['type'] = 'invoice';
        $post['orgid'] = session('orgid');

        $offset        = (int) ($request->input('iDisplayStart', 0));
        $data = Invoice::list($post);
        $i = 0;
        $array = [];
        $filtereddata = ($data["totalfilteredrecs"] > 0 ? $data["totalfilteredrecs"] : $data["totalrecs"]);
        $totalrecs = $data["totalrecs"];

        unset($data["totalfilteredrecs"]);
        unset($data["totalrecs"]);


        foreach ($data as $row) {
            $array[$i]["sno"]        = $offset + $i + 1;
            $array[$i]["username"]   = $row->username;
            $array[$i]["email"]      = $row->email;
            $array[$i]["invoicenumber"]      = $row->invoicenumber ?? 'Invoice Number Not Generate';
            $array[$i]["created_at"] = \Carbon\Carbon::parse($row->created_at)->format('M d, Y h:i A');
            $array[$i]["order_status"] = $row->order_status;


            $action = '
                        <a href="javascript:;" title="View Order" class="tooltipdiv viewOrder" style="color:green;" data-id="' . $row->id . '">
                        <i class="bx bx-show-alt"></i>
                        </a>
                        <a href="' . route('invoice.download', $row->id) . '" title="Download Invoice" class="tooltipdiv" style="color:#6366f1;" target="_blank">
                        <i class="bx bx-download"></i>
                        </a>
                        <a href="' . route('invoice.preview', $row->id) . '" title="Preview Invoice" class="tooltipdiv" style="color:#f59e0b;" target="_blank">
                        <i class="bx bx-file"></i>
                        </a>
                    ';

            $array[$i]["action"] = $action;
            $i++;
        }

        if (!$filtereddata) $filtereddata = 0;
        if (!$totalrecs)    $totalrecs    = 0;

        return json_encode([
            "recordsFiltered" => $filtereddata,
            "recordsTotal"    => $totalrecs,
            "data"            => $array
        ]);
    }

    public function download(string $id)
    {
        return $this->resolveInvoicePdf($id, 'attachment');
    }

    public function preview(string $id)
    {
        return $this->resolveInvoicePdf($id, 'inline');
    }

    /**
     * Shared logic for download/preview — generates the PDF (if needed),
     * persists the storage path to the invoices table, and returns it.
     */
    private function resolveInvoicePdf(string $id, string $disposition)
    {
        $post['orgid']  = session('orgid');
        $post['userid'] = session('userid');
        $post['id']     = $id;

        $order = Order::getDataInvoice($post);

        $checkInvoice = DB::table('invoices')
            ->where('ordermasterid', $id)
            ->where('orgid', $post['orgid'])
            ->first();

        if (empty($checkInvoice)) {
            $invoiceNumber = 'INV-' . date('Y') . '-' . strtoupper(Str::random(6));
            $storagePath   = 'pdf/invoice-' . $invoiceNumber . '.pdf';

            DB::table('invoices')->insert([
                'id'            => (string) Str::uuid(),
                'ordermasterid' => $order->id,
                'orgid'         => $post['orgid'],
                'invoicenumber' => $invoiceNumber,
                'storagepath'   => $storagePath,
                'postedby'      => auth()->id(),
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        } else {
            $invoiceNumber = $checkInvoice->invoicenumber;

            // Older rows may not have storagepath populated yet — backfill it.
            $storagePath = $checkInvoice->storagepath
                ?: 'pdf/invoice-' . $invoiceNumber . '.pdf';

            if (empty($checkInvoice->storagepath)) {
                DB::table('invoices')
                    ->where('id', $checkInvoice->id)
                    ->update([
                        'storagepath' => $storagePath,
                        // 'updatedby'   => auth()->id(),
                        'updated_at'  => now(),
                    ]);
            }
        }

        if (Storage::disk('public')->exists($storagePath)) {
            $pdfContent = Storage::disk('public')->get($storagePath);
        } else {
            $pdf = Pdf::loadView('backend.invoice.download', compact('order', 'invoiceNumber'))
                ->setPaper('a4', 'portrait');
            $pdfContent = $pdf->output();
            Storage::disk('public')->put($storagePath, $pdfContent);
        }

        return response($pdfContent, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', $disposition . '; filename="invoice-' . $order->id . '.pdf"');
    }
}