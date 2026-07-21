<?php

namespace App\Models\BackPanel;

use Illuminate\Database\Eloquent\Model;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BsdateController;

class SalesReturnVoucher extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    public static function list($post)
    {
        $orgid  = $post['orgid'] ?? '';
        $search = trim(strtolower($post['sSearch'] ?? ($post['search']['value'] ?? '')));

        $limit  = (int) ($post['iDisplayLength'] ?? 15);
        $offset = (int) ($post['iDisplayStart']  ?? 0);

        $itemAgg = DB::table('sales_return_voucher_items')
            ->select(
                'sales_return_voucher_id',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('COUNT(*) as item_count'),
                DB::raw('MAX(unit_rate) as single_rate')
            )
            ->groupBy('sales_return_voucher_id');

        $query = DB::table('sales_return_vouchers as srv')
            ->join('users as u', 'u.id', '=', 'srv.customer_id')
            ->leftJoin('order_masters as sv', 'sv.id', '=', 'srv.against_voucher_id')
            ->leftJoinSub($itemAgg, 'srvi', 'srvi.sales_return_voucher_id', '=', 'srv.id')
            ->select(
                'srv.id',
                'srv.credit_note_no',
                'srv.return_date',
                'srv.vat_amount',
                'srv.total_amount',
                'srv.return_status',
                'u.name as customer_name',
                'sv.voucher_number as against_voucher_no',
                'srvi.total_qty',
                'srvi.item_count',
                'srvi.single_rate'
            )
            ->where('srv.orgid', $orgid)
            ->where('srv.status', 'Y');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(srv.credit_note_no) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(u.name) LIKE ?', ["%{$search}%"]);
            });
        }

        $totalrecs = DB::table('sales_return_vouchers')->where('orgid', $orgid)->where('status', 'Y')->count();
        $filteredCount = (clone $query)->count();

        $query->orderBy('srv.return_date', 'desc')->orderBy('srv.created_at', 'desc');

        if ($limit > -1) {
            $query->offset($offset)->limit($limit);
        }

        $result = $query->get();
        $result['totalrecs']         = $totalrecs;
        $result['totalfilteredrecs'] = $filteredCount;

        return $result;
    }

    public static function saveData($post)
    {
        try {
            DB::beginTransaction();

            $items = $post['items'] ?? [];
            if (empty($items)) {
                throw new Exception('At least one item is required.');
            }

            $subtotal = 0;
            $lineData = [];


            $bsdate = new BsdateController;
            $sales_retrun_vouchers_date_eng = $bsdate->nep_to_eng($post['return_date']);

            foreach ($items as $row) {
                if (empty($row['item_id']) || empty($row['qty']) || !isset($row['unit_rate']) || $row['unit_rate'] === '') {
                    continue;
                }

                $item = DB::table('items')->where('id', $row['item_id'])->first();
                if (!$item) {
                    throw new Exception('Selected item was not found.');
                }

                $qty      = (float) $row['qty'];
                $unitRate = (float) $row['unit_rate'];
                $amount   = round($qty * $unitRate, 2);

                $vatPercent = ($item->vat_status ?? 'N') === 'Y' ? (float) ($item->vat_percent ?? config('vat.default')) : (float) config('vat.non-taxable');

                $lineData[] = [
                    'item_id'      => $row['item_id'],
                    'variation_id' => $row['variation_id'] ?? null,
                    'unit'         => $row['unit'] ?? null,
                    'qty'          => $qty,
                    'unit_rate'    => $unitRate,
                    'amount'       => $amount,
                    'vat_percent'  => $vatPercent,
                ];

                $subtotal += $amount;
            }

            if (empty($lineData)) {
                throw new Exception('At least one valid item is required.');
            }

            $billDiscountPercent = isset($post['bill_discount_percent']) ? (float) $post['bill_discount_percent'] : 0;
            $billDiscountAmount  = round($subtotal * $billDiscountPercent / 100, 2);
            $preVatBase          = $subtotal - $billDiscountAmount;

            $totalVatAmount = 0;

            foreach ($lineData as &$line) {
                $lineShare = $subtotal > 0 ? ($line['amount'] / $subtotal) * $preVatBase : 0;
                $lineVatAmount = round($lineShare * $line['vat_percent'] / 100, 2);

                $line['vat_amount'] = $lineVatAmount;
                $line['net_amount'] = round($lineShare + $lineVatAmount, 2);

                $totalVatAmount += $lineVatAmount;
            }
            unset($line);

            $taxableAmount = round($preVatBase, 2);
            $totalAmount   = round($taxableAmount + $totalVatAmount, 2);

            $customer = DB::table('users')->where('id', $post['customer_id'])->first();
            if (!$customer) {
                throw new Exception('Selected customer was not found.');
            }

            $header = [
                'return_date'           => $post['return_date'],
                'credit_note_no'        => $post['credit_note_no'],
                'customer_id'           => $post['customer_id'],
                'against_voucher_id'    => $post['against_voucher_id'] ?? null,
                'remarks'               => $post['remarks'] ?? null,
                'subtotal'              => round($subtotal, 2),
                'bill_discount_percent' => $billDiscountPercent,
                'bill_discount_amount'  => $billDiscountAmount,
                'sales_retrun_vouchers_date_eng'  => $sales_retrun_vouchers_date_eng,
                'taxable_amount'        => $taxableAmount,
                'vat_amount'            => round($totalVatAmount, 2),
                'total_amount'          => $totalAmount,
                'orgid'                 => $post['orgid'],
            ];

            if (!empty($post['id'])) {
                $voucherId = $post['id'];

                $header['updatedby']  = $post['userid'];
                $header['updated_at'] = Carbon::now();

                DB::table('sales_return_vouchers')->where('id', $voucherId)->update($header);
                DB::table('sales_return_voucher_items')->where('sales_return_voucher_id', $voucherId)->delete();
            } else {
                $voucherId = (string) Str::uuid();

                $header['id']            = $voucherId;
                $header['return_status']  = 'Pending';
                $header['postedby']      = $post['userid'];
                $header['created_at']    = Carbon::now();
                $header['updated_at']    = Carbon::now();

                DB::table('sales_return_vouchers')->insert($header);
            }

            $itemRows = [];
            foreach ($lineData as $line) {
                $itemRows[] = [
                    'id'                       => (string) Str::uuid(),
                    'orgid'                    => $post['orgid'],
                    'sales_return_voucher_id'  => $voucherId,
                    'item_id'                  => $line['item_id'],
                    'variation_id'             => $line['variation_id'],
                    'unit'                     => $line['unit'],
                    'qty'                      => $line['qty'],
                    'unit_rate'                => $line['unit_rate'],
                    'amount'                   => $line['amount'],
                    'vat_percent'              => $line['vat_percent'] ?? null,
                    'vat_amount'               => $line['vat_amount'] ?? null,
                    'net_amount'               => $line['net_amount'] ?? null,
                    'created_at'               => Carbon::now(),
                    'updated_at'               => Carbon::now(),
                ];
            }
            DB::table('sales_return_voucher_items')->insert($itemRows);

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function getData($post)
    {
        $id = $post['id'] ?? null;
        if (empty($id)) {
            throw new Exception('Sales return voucher ID is required.');
        }

        $voucher = DB::table('sales_return_vouchers as srv')
            ->join('users as u', 'u.id', '=', 'srv.customer_id')
            ->leftJoin('order_masters as sv', 'sv.id', '=', 'srv.against_voucher_id')
            ->select('srv.*', 'u.name as customer_name', 'sv.voucher_number as against_voucher_no')
            ->where('srv.id', $id)
            ->where('srv.orgid', $post['orgid'])
            ->first();

        if (!$voucher) {
            throw new Exception('Sales return voucher not found.');
        }

        $voucher->items = DB::table('sales_return_voucher_items as srvi')
            ->join('items as i', 'i.id', '=', 'srvi.item_id')
            ->leftJoin('itemvariations as iv', 'iv.id', '=', 'srvi.variation_id')
            ->select(
                'srvi.*',
                'i.title as item_title',
                'i.product_code as item_product_code',
                'iv.attribute as variation_attribute',
                'iv.value as variation_value',
                'iv.product_code as variation_product_code'
            )
            ->where('srvi.sales_return_voucher_id', $id)
            ->orderBy('srvi.created_at')
            ->get();

        return $voucher;
    }

    public static function deleteDate($post)
    {
        try {
            $voucher = DB::table('sales_return_vouchers')
                ->where('id', $post['id'])
                ->where('orgid', $post['orgid'])
                ->first();

            if (!$voucher) {
                throw new Exception("Couldn't delete record. Please try again", 1);
            }

            $updated = DB::table('sales_return_vouchers')
                ->where('id', $post['id'])
                ->where('orgid', $post['orgid'])
                ->update([
                    'status'         => 'N',
                    // Free up the credit_note_no for reuse: the (orgid, credit_note_no)
                    // unique index applies to all rows regardless of status, so a
                    // soft-deleted row would otherwise permanently block that number.
                    'credit_note_no' => $voucher->credit_note_no . '-DEL-' . substr($post['id'], 0, 8),
                    'updatedby'      => $post['userid'],
                    'updated_at'     => Carbon::now(),
                ]);

            if (!$updated) {
                throw new Exception("Couldn't delete record. Please try again", 1);
            }

            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function updateStatus($post)
    {
        try {
            $updated = DB::table('sales_return_vouchers')
                ->where('id', $post['id'])
                ->where('orgid', $post['orgid'])
                ->where('status', 'Y')
                ->update([
                    'return_status' => $post['return_status'],
                    'updatedby'     => $post['userid'],
                    'updated_at'    => Carbon::now(),
                ]);

            if (!$updated) {
                throw new Exception("Couldn't update status. Please try again", 1);
            }

            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }


    public static function generateUniqueVoucherNo($post)
    {
        try {
            $lastVoucher = DB::table('sales_return_vouchers')
                ->select('credit_note_no')
                ->where('orgid', $post['orgid'])
                ->where('status', 'Y')
                ->orderByDesc('credit_note_no')
                ->first();

            if (!$lastVoucher) {
                return 1;
            }
            return $lastVoucher->credit_note_no + 1;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
