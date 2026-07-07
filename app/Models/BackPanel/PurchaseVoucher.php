<?php

namespace App\Models\BackPanel;

use Illuminate\Database\Eloquent\Model;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PurchaseVoucher extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    public static function list($post)
    {
        $orgid  = $post['orgid'] ?? '';
        $search = trim(strtolower($post['sSearch'] ?? ($post['search']['value'] ?? '')));

        $limit  = (int) ($post['iDisplayLength'] ?? 15);
        $offset = (int) ($post['iDisplayStart']  ?? 0);

        $itemAgg = DB::table('purchase_voucher_items')
            ->select(
                'purchase_voucher_id',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('COUNT(*) as item_count'),
                DB::raw('MAX(unit_rate) as single_rate'),
                DB::raw('MAX(excise_type) as single_excise_type'),
                DB::raw('MAX(excise_percentage) as single_excise_percentage'),
                DB::raw('MAX(excise_value) as single_excise_value')
            )
            ->groupBy('purchase_voucher_id');

        $query = DB::table('purchase_vouchers as pv')
            ->join('vendors as v', 'v.id', '=', 'pv.vendor_id')
            ->leftJoinSub($itemAgg, 'pvi', 'pvi.purchase_voucher_id', '=', 'pv.id')
            ->select(
                'pv.id',
                'pv.voucher_no',
                'pv.voucher_date',
                'pv.purchase_type',
                'pv.vat_amount',
                'pv.total_amount',
                'v.name as vendor_name',
                'v.tax_number as vendor_pan',
                'pvi.total_qty',
                'pvi.item_count',
                'pvi.single_rate',
                'pvi.single_excise_type',
                'pvi.single_excise_percentage',
                'pvi.single_excise_value'
            )
            ->where('pv.orgid', $orgid)
            ->where('pv.status', 'Y');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(pv.voucher_no) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(v.name) LIKE ?', ["%{$search}%"]);
            });
        }

        $totalrecs = DB::table('purchase_vouchers')->where('orgid', $orgid)->where('status', 'Y')->count();
        $filteredCount = (clone $query)->count();

        $query->orderBy('pv.voucher_date', 'desc')->orderBy('pv.created_at', 'desc');

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

                $exciseType       = $item->excise_status === 'Y' ? $item->excise_type : null;
                $excisePercentage = $exciseType === 'percentage' ? (float) $item->excise_percentage : null;
                $exciseValue      = $exciseType === 'fixed' ? (float) $item->excise_value : null;

                $lineData[] = [
                    'item_id'           => $row['item_id'],
                    'variation_id'      => $row['variation_id'] ?? null,
                    'unit'              => $row['unit'] ?? null,
                    'qty'               => $qty,
                    'unit_rate'         => $unitRate,
                    'amount'            => $amount,
                    'vat_percent'       => $vatPercent,
                    'excise_type'       => $exciseType,
                    'excise_percentage' => $excisePercentage,
                    'excise_value'      => $exciseValue,
                    'mrp'               => isset($row['mrp']) && $row['mrp'] !== '' ? (float) $row['mrp'] : null,
                ];

                $subtotal += $amount;
            }

            if (empty($lineData)) {
                throw new Exception('At least one valid item is required.');
            }

            $billDiscountPercent = isset($post['bill_discount_percent']) ? (float) $post['bill_discount_percent'] : 0;
            $billDiscountAmount  = round($subtotal * $billDiscountPercent / 100, 2);
            $preVatBase          = $subtotal - $billDiscountAmount;

            $totalVatAmount    = 0;
            $totalExciseAmount = 0;

            foreach ($lineData as &$line) {
                $lineShare = $subtotal > 0 ? ($line['amount'] / $subtotal) * $preVatBase : 0;

                $lineExciseAmount = 0;
                if ($line['excise_type'] === 'percentage') {
                    $lineExciseAmount = round($lineShare * $line['excise_percentage'] / 100, 2);
                } elseif ($line['excise_type'] === 'fixed') {
                    $lineExciseAmount = round($line['excise_value'] * $line['qty'], 2);
                }

                $lineTaxableForVat = $lineShare + $lineExciseAmount;
                $lineVatAmount     = round($lineTaxableForVat * $line['vat_percent'] / 100, 2);

                $line['taxable_share'] = round($lineTaxableForVat, 2);
                $line['vat_amount']    = $lineVatAmount;
                $line['excise_amount'] = $lineExciseAmount;
                $line['net_amount']    = round($lineTaxableForVat + $lineVatAmount, 2);

                $totalVatAmount    += $lineVatAmount;
                $totalExciseAmount += $lineExciseAmount;
            }
            unset($line);

            $taxableAmount = round($preVatBase + $totalExciseAmount, 2);
            $totalAmount   = round($taxableAmount + $totalVatAmount, 2);

            $vendor = DB::table('vendors')->where('id', $post['vendor_id'])->first();
            if (!$vendor) {
                throw new Exception('Selected vendor was not found.');
            }

            $header = [
                'voucher_date'           => $post['voucher_date'],
                'voucher_no'             => $post['voucher_no'],
                'purchase_type'          => $post['purchase_type'],
                'vendor_id'              => $post['vendor_id'],
                'pan'                    => $vendor->tax_number,
                'remarks'                => $post['remarks'] ?? null,
                'subtotal'               => round($subtotal, 2),
                'bill_discount_percent'  => $billDiscountPercent,
                'bill_discount_amount'   => $billDiscountAmount,
                'taxable_amount'         => round($taxableAmount, 2),
                'vat_amount'             => round($totalVatAmount, 2),
                'excise_amount'          => round($totalExciseAmount, 2),
                'total_amount'           => $totalAmount,
                'orgid'                  => $post['orgid'],
            ];

            if (!empty($post['id'])) {
                $voucherId = $post['id'];

                $header['updatedby']  = $post['userid'];
                $header['updated_at'] = Carbon::now();

                DB::table('purchase_vouchers')->where('id', $voucherId)->update($header);
                DB::table('purchase_voucher_items')->where('purchase_voucher_id', $voucherId)->delete();
            } else {
                $voucherId = (string) Str::uuid();

                $header['id']         = $voucherId;
                $header['postedby']   = $post['userid'];
                $header['created_at'] = Carbon::now();
                $header['updated_at'] = Carbon::now();

                DB::table('purchase_vouchers')->insert($header);
            }

            $itemRows = [];
            foreach ($lineData as $line) {
                $itemRows[] = [
                    'id'                   => (string) Str::uuid(),
                    'orgid'                => $post['orgid'],
                    'purchase_voucher_id'  => $voucherId,
                    'item_id'              => $line['item_id'],
                    'variation_id'         => $line['variation_id'],
                    'unit'                 => $line['unit'],
                    'qty'                  => $line['qty'],
                    'unit_rate'            => $line['unit_rate'],
                    'amount'               => $line['amount'],
                    'vat_percent'          => $line['vat_percent'],
                    'vat_amount'           => $line['vat_amount'],
                    'excise_type'          => $line['excise_type'],
                    'excise_percentage'    => $line['excise_percentage'],
                    'excise_value'         => $line['excise_value'],
                    'excise_amount'        => $line['excise_amount'],
                    'mrp'                  => $line['mrp'],
                    'net_amount'           => $line['net_amount'],
                    'created_at'           => Carbon::now(),
                    'updated_at'           => Carbon::now(),
                ];
            }
            DB::table('purchase_voucher_items')->insert($itemRows);

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
            throw new Exception('Purchase voucher ID is required.');
        }

        $voucher = DB::table('purchase_vouchers as pv')
            ->join('vendors as v', 'v.id', '=', 'pv.vendor_id')
            ->select('pv.*', 'v.name as vendor_name')
            ->where('pv.id', $id)
            ->where('pv.orgid', $post['orgid'])
            ->first();

        if (!$voucher) {
            throw new Exception('Purchase voucher not found.');
        }

        $voucher->items = DB::table('purchase_voucher_items as pvi')
            ->join('items as i', 'i.id', '=', 'pvi.item_id')
            ->leftJoin('itemvariations as iv', 'iv.id', '=', 'pvi.variation_id')
            ->select(
                'pvi.*',
                'i.title as item_title',
                'iv.attribute as variation_attribute',
                'iv.value as variation_value'
            )
            ->where('pvi.purchase_voucher_id', $id)
            ->orderBy('pvi.created_at')
            ->get();

        return $voucher;
    }

    public static function deleteDate($post)
    {
        try {
            $updated = DB::table('purchase_vouchers')
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

    public static function getVendorVouchers($post)
    {
        try {
            return DB::table('purchase_vouchers as pv')
                ->select('pv.id', 'pv.voucher_no', 'pv.voucher_date')
                ->where('pv.orgid', $post['orgid'])
                ->where('pv.vendor_id', $post['vendor_id'])
                ->where('pv.status', 'Y')
                ->whereNotExists(function ($query) use ($post) {
                    $query->select(DB::raw(1))
                        ->from('purchase_return_vouchers as prv')
                        ->whereColumn('prv.against_voucher_id', 'pv.id')
                        ->where('prv.status', 'Y')
                        ->when(!empty($post['exclude_return_id']), function ($q) use ($post) {
                            $q->where('prv.id', '!=', $post['exclude_return_id']);
                        });
                })
                ->orderBy('pv.voucher_date', 'desc')
                ->get();
        } catch (Exception $e) {
            throw $e;
        }
    }
}
