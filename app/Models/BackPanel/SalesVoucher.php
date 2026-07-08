<?php

namespace App\Models\BackPanel;

use Illuminate\Database\Eloquent\Model;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SalesVoucher extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    public static function list($post)
    {
        try {
            $cond     = "1=1";
            $bindings = [];

            if (!empty($post['sSearch_1'])) {
                $val        = strtolower(trim($post['sSearch_1']));
                $cond      .= " and lower(i.title) like ?";
                $bindings[] = "%{$val}%";
            }

            if (!empty($post['sSearch_2'])) {
                $val        = strtolower(trim($post['sSearch_2']));
                $cond      .= " and (lower(v.attribute) like ? or lower(v.value) like ?)";
                $bindings[] = "%{$val}%";
                $bindings[] = "%{$val}%";
            }

            $limit  = isset($post['iDisplayLength']) ? (int) $post['iDisplayLength'] : 15;
            $offset = isset($post['iDisplayStart'])  ? (int) $post['iDisplayStart']  : 0;

            $baseQuery = DB::table('order_details as od')
                ->join('order_masters as om', 'om.id', '=', 'od.ordermasterid')
                ->join('itemvariations as v', 'v.id', '=', 'od.variation_id')
                ->join('items as i', 'i.id', '=', 'v.item_id')
                ->where('om.orgid', $post['orgid']);

            $totalrecs = (clone $baseQuery)
                ->select('i.id')
                ->groupBy('i.id')
                ->get()
                ->count();

            $query = (clone $baseQuery)
                ->selectRaw("i.id, i.title, v.value, v.attribute, SUM(od.quantity) as quantity, SUM(od.price) as price")
                ->whereRaw($cond, $bindings)
                ->groupBy('i.id', 'i.title', 'v.attribute', 'v.value')
                ->orderBy('i.id');

            $filteredCount = DB::query()
                ->fromSub($query->clone(), 'grouped')
                ->count();

            $result = $limit > -1
                ? $query->offset($offset)->limit($limit)->get()
                : $query->get();

            return [
                'data'              => $result,
                'totalrecs'         => $totalrecs,
                'totalfilteredrecs' => $filteredCount,
            ];
        } catch (Exception $e) {
            throw $e;
        }
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

                $vatPercent = ($item->vat_status ?? 'N') === 'Y' ? (float) config('vat.taxable') : (float) config('vat.non-taxable');

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
                'voucher_date'          => $post['voucher_date'],
                'voucher_no'            => $post['voucher_no'],
                'customer_id'           => $post['customer_id'],
                'order_id'              => $post['order_id'] ?? null,
                'remarks'               => $post['remarks'] ?? null,
                'subtotal'              => round($subtotal, 2),
                'bill_discount_percent' => $billDiscountPercent,
                'bill_discount_amount'  => $billDiscountAmount,
                'taxable_amount'        => $taxableAmount,
                'vat_amount'            => round($totalVatAmount, 2),
                'total_amount'          => $totalAmount,
                'orgid'                 => $post['orgid'],
            ];

            if (!empty($post['id'])) {
                $voucherId = $post['id'];

                $header['updatedby']  = $post['userid'];
                $header['updated_at'] = Carbon::now();

                DB::table('sales_vouchers')->where('id', $voucherId)->update($header);
                DB::table('sales_voucher_items')->where('sales_voucher_id', $voucherId)->delete();
            } else {
                $voucherId = (string) Str::uuid();

                $header['id']         = $voucherId;
                $header['postedby']   = $post['userid'];
                $header['created_at'] = Carbon::now();
                $header['updated_at'] = Carbon::now();

                DB::table('sales_vouchers')->insert($header);
            }

            $itemRows = [];
            foreach ($lineData as $line) {
                $itemRows[] = [
                    'id'               => (string) Str::uuid(),
                    'orgid'            => $post['orgid'],
                    'sales_voucher_id' => $voucherId,
                    'item_id'          => $line['item_id'],
                    'variation_id'     => $line['variation_id'],
                    'unit'             => $line['unit'],
                    'qty'              => $line['qty'],
                    'unit_rate'        => $line['unit_rate'],
                    'amount'           => $line['amount'],
                    'vat_percent'      => $line['vat_percent'],
                    'vat_amount'       => $line['vat_amount'],
                    'net_amount'       => $line['net_amount'],
                    'created_at'       => Carbon::now(),
                    'updated_at'       => Carbon::now(),
                ];
            }
            DB::table('sales_voucher_items')->insert($itemRows);

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
            throw new Exception('Sales voucher ID is required.');
        }

        $voucher = DB::table('sales_vouchers as sv')
            ->join('users as u', 'u.id', '=', 'sv.customer_id')
            ->select('sv.*', 'u.name as customer_name')
            ->where('sv.id', $id)
            ->where('sv.orgid', $post['orgid'])
            ->first();

        if (!$voucher) {
            throw new Exception('Sales voucher not found.');
        }

        $voucher->items = DB::table('sales_voucher_items as svi')
            ->join('items as i', 'i.id', '=', 'svi.item_id')
            ->leftJoin('itemvariations as iv', 'iv.id', '=', 'svi.variation_id')
            ->select(
                'svi.*',
                'i.title as item_title',
                'iv.attribute as variation_attribute',
                'iv.value as variation_value'
            )
            ->where('svi.sales_voucher_id', $id)
            ->orderBy('svi.created_at')
            ->get();

        return $voucher;
    }

    public static function deleteDate($post)
    {
        try {
            $updated = DB::table('sales_vouchers')
                ->where('id', $post['id'])
                ->where('orgid', $post['orgid'])
                ->update([
                    'status'     => 'N',
                    'updatedby'  => $post['userid'],
                    'updated_at' => Carbon::now(),
                ]);

            if (!$updated) {
                throw new Exception("Couldn't delete record. Please try again", 1);
            }

            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /* ── Delivered orders for a customer without an existing sales voucher ── */
    public static function getCustomerOrders($post)
    {
        try {
            return DB::table('order_masters as om')
                ->select('om.id', 'om.created_at', 'om.order_master_total_price')
                ->where('om.orgid', $post['orgid'])
                ->where('om.userid', $post['customer_id'])
                ->where('om.order_status', 'Delivered')
                ->where('om.status', 'Y')
                ->whereNotExists(function ($query) use ($post) {
                    $query->select(DB::raw(1))
                        ->from('sales_vouchers as sv')
                        ->whereColumn('sv.order_id', 'om.id')
                        ->where('sv.status', 'Y')
                        ->when(!empty($post['exclude_voucher_id']), function ($q) use ($post) {
                            $q->where('sv.id', '!=', $post['exclude_voucher_id']);
                        });
                })
                ->orderBy('om.created_at', 'desc')
                ->get();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /* ── Sales vouchers for a customer without an existing sales return ── */
    public static function getCustomerVouchers($post)
    {
        try {
            return DB::table('sales_vouchers as sv')
                ->select('sv.id', 'sv.voucher_no', 'sv.voucher_date')
                ->where('sv.orgid', $post['orgid'])
                ->where('sv.customer_id', $post['customer_id'])
                ->where('sv.status', 'Y')
                ->whereNotExists(function ($query) use ($post) {
                    $query->select(DB::raw(1))
                        ->from('sales_return_vouchers as srv')
                        ->whereColumn('srv.against_voucher_id', 'sv.id')
                        ->where('srv.status', 'Y')
                        ->when(!empty($post['exclude_return_id']), function ($q) use ($post) {
                            $q->where('srv.id', '!=', $post['exclude_return_id']);
                        });
                })
                ->orderBy('sv.voucher_date', 'desc')
                ->get();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /* ── Line items of a given order (to auto-populate the sales form) ── */
    public static function getOrderItems($post)
    {
        try {
            return DB::table('order_details as od')
                ->join('order_masters as om', 'om.id', '=', 'od.ordermasterid')
                ->join('itemvariations as iv', 'iv.id', '=', 'od.variation_id')
                ->join('items as i', 'i.id', '=', 'iv.item_id')
                ->select(
                    'i.id as item_id',
                    'i.title as item_title',
                    'iv.id as variation_id',
                    'iv.attribute as variation_attribute',
                    'iv.value as variation_value',
                    'od.quantity as qty',
                    'od.price as unit_rate'
                )
                ->where('od.ordermasterid', $post['order_id'])
                ->where('om.orgid', $post['orgid'])
                ->where('od.status', 'Y')
                ->orderBy('od.created_at')
                ->get();
        } catch (Exception $e) {
            throw $e;
        }
    }
}