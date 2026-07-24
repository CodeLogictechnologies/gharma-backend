<?php

namespace App\Models\BackPanel;

use Illuminate\Database\Eloquent\Model;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BsdateController;

class PurchaseReturnVoucher extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    public static function list($post)
    {
        $orgid  = $post['orgid'] ?? '';
        $search = trim(strtolower($post['sSearch'] ?? ($post['search']['value'] ?? '')));

        $limit  = (int) ($post['iDisplayLength'] ?? 15);
        $offset = (int) ($post['iDisplayStart']  ?? 0);

        $itemAgg = DB::table('purchase_return_voucher_items')
            ->select(
                'purchase_return_voucher_id',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('COUNT(*) as item_count'),
                DB::raw('MAX(unit_rate) as single_rate'),
                DB::raw('MAX(excise_type) as single_excise_type'),
                DB::raw('MAX(excise_percentage) as single_excise_percentage'),
                DB::raw('MAX(excise_value) as single_excise_value')
            )
            ->groupBy('purchase_return_voucher_id');

        $query = DB::table('purchase_return_vouchers as prv')
            ->join('vendors as v', 'v.id', '=', 'prv.vendor_id')
            ->leftJoin('purchase_vouchers as pv', 'pv.id', '=', 'prv.against_voucher_id')
            ->leftJoinSub($itemAgg, 'prvi', 'prvi.purchase_return_voucher_id', '=', 'prv.id')
            ->select(
                'prv.id',
                'prv.debit_note_no',
                'prv.return_date',
                'prv.vat_amount',
                'prv.excise_amount',
                'prv.total_amount',
                'prv.return_status',
                'v.name as vendor_name',
                'v.tax_number as vendor_pan',
                'pv.voucher_no as against_voucher_no',
                'prvi.total_qty',
                'prvi.item_count',
                'prvi.single_rate',
                'prvi.single_excise_type',
                'prvi.single_excise_percentage',
                'prvi.single_excise_value'
            )
            ->where('prv.orgid', $orgid)
            ->where('prv.status', 'Y');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(prv.debit_note_no) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(v.name) LIKE ?', ["%{$search}%"]);
            });
        }

        $totalrecs = DB::table('purchase_return_vouchers')->where('orgid', $orgid)->where('status', 'Y')->count();
        $filteredCount = (clone $query)->count();

        $query->orderBy('prv.return_date', 'desc')->orderBy('prv.created_at', 'desc');

        if ($limit > -1) {
            $query->offset($offset)->limit($limit);
        }

        $result = $query->get();
        $result['totalrecs']         = $totalrecs;
        $result['totalfilteredrecs'] = $filteredCount;

        return $result;
    }

    // public static function saveData($post)
    // {
    //     try {
    //         DB::beginTransaction();

    //         $items = $post['items'] ?? [];
    //         if (empty($items)) {
    //             throw new Exception('At least one item is required.');
    //         }

    //         $subtotal = 0;
    //         $lineData = [];

    //         foreach ($items as $row) {
    //             if (empty($row['item_id']) || empty($row['qty']) || !isset($row['unit_rate']) || $row['unit_rate'] === '') {
    //                 continue;
    //             }

    //             $item = DB::table('items')->where('id', $row['item_id'])->first();
    //             if (!$item) {
    //                 throw new Exception('Selected item was not found.');
    //             }

    //             $qty      = (float) $row['qty'];
    //             $unitRate = (float) $row['unit_rate'];
    //             $amount   = round($qty * $unitRate, 2);

    //             $vatPercent = ($item->vat_status ?? 'N') === 'Y' ? (float) ($item->vat_percent ?? config('vat.default')) : (float) config('vat.non-taxable');

    //             $exciseType       = $item->excise_status === 'Y' ? $item->excise_type : null;
    //             $excisePercentage = $exciseType === 'percentage' ? (float) $item->excise_percentage : null;
    //             $exciseValue      = $exciseType === 'fixed' ? (float) $item->excise_value : null;

    //             $lineData[] = [
    //                 'item_id'           => $row['item_id'],
    //                 'variation_id'      => $row['variation_id'] ?? null,
    //                 'unit'              => $row['unit'] ?? null,
    //                 'qty'               => $qty,
    //                 'unit_rate'         => $unitRate,
    //                 'amount'            => $amount,
    //                 'vat_percent'       => $vatPercent,
    //                 'excise_type'       => $exciseType,
    //                 'excise_percentage' => $excisePercentage,
    //                 'excise_value'      => $exciseValue,
    //             ];

    //             $subtotal += $amount;
    //         }

    //         if (empty($lineData)) {
    //             throw new Exception('At least one valid item is required.');
    //         }

    //         $billDiscountPercent = isset($post['bill_discount_percent']) ? (float) $post['bill_discount_percent'] : 0;
    //         $billDiscountAmount  = round($subtotal * $billDiscountPercent / 100, 2);
    //         $preVatBase          = $subtotal - $billDiscountAmount;

    //         $totalVatAmount    = 0;
    //         $totalExciseAmount = 0;

    //         foreach ($lineData as &$line) {
    //             $lineShare = $subtotal > 0 ? ($line['amount'] / $subtotal) * $preVatBase : 0;

    //             $lineExciseAmount = 0;
    //             if ($line['excise_type'] === 'percentage') {
    //                 $lineExciseAmount = round($lineShare * $line['excise_percentage'] / 100, 2);
    //             } elseif ($line['excise_type'] === 'fixed') {
    //                 $lineExciseAmount = round($line['excise_value'] * $line['qty'], 2);
    //             }

    //             $lineTaxableForVat = $lineShare + $lineExciseAmount;
    //             $lineVatAmount     = round($lineTaxableForVat * $line['vat_percent'] / 100, 2);

    //             $line['taxable_share'] = round($lineTaxableForVat, 2);
    //             $line['vat_amount']    = $lineVatAmount;
    //             $line['excise_amount'] = $lineExciseAmount;
    //             $line['net_amount']    = round($lineTaxableForVat + $lineVatAmount, 2);

    //             $totalVatAmount    += $lineVatAmount;
    //             $totalExciseAmount += $lineExciseAmount;
    //         }
    //         unset($line);

    //         $taxableAmount = round($preVatBase + $totalExciseAmount, 2);
    //         $totalAmount   = round($taxableAmount + $totalVatAmount, 2);

    //         $vendor = DB::table('vendors')->where('id', $post['vendor_id'])->first();
    //         if (!$vendor) {
    //             throw new Exception('Selected vendor was not found.');
    //         }

    //         //  Auto generate debit note number, same pattern as PurchaseVoucher::voucher_no
    //         if (!empty($post['id'])) {
    //             // Editing an existing return — keep its original number
    //             $post['debit_note_no'] = DB::table('purchase_return_vouchers')
    //                 ->where('id', $post['id'])
    //                 ->value('debit_note_no');
    //         } else {
    //             // New return — generate the next number automatically
    //             $post['debit_note_no'] = self::getVoucherNumber($post);
    //         }

    //         $bsdate = new BsdateController;
    //         $return_date_eng = $bsdate->nep_to_eng($post['return_date']);
    //         $orgFiscalYearId = DB::table('organizations')
    //             ->where('id', $post['orgid'])
    //             ->value('current_fiscal_year_id');
    //         $header = [
    //             'return_date'           => $post['return_date'],
    //             'debit_note_no'         => $post['debit_note_no'],
    //             'vendor_id'             => $post['vendor_id'],
    //             'against_voucher_id'    => $post['against_voucher_id'] ?? null,
    //             'remarks'               => $post['remarks'] ?? null,
    //             'subtotal'              => round($subtotal, 2),
    //             'bill_discount_percent' => $billDiscountPercent,
    //             'bill_discount_amount'  => $billDiscountAmount,
    //             'taxable_amount'        => round($taxableAmount, 2),
    //             'vat_amount'            => round($totalVatAmount, 2),
    //             'excise_amount'         => round($totalExciseAmount, 2),
    //             'total_amount'          => $totalAmount,
    //             'return_date_eng'          => $return_date_eng,
    //             'orgid'                 => $post['orgid'],
    //             'fiscal_year_id'        => $orgFiscalYearId,
    //         ];
    //         if (!empty($post['id'])) {
    //             $voucherId = $post['id'];

    //             $header['updatedby']  = $post['userid'];
    //             $header['updated_at'] = Carbon::now();

    //             DB::table('purchase_return_vouchers')->where('id', $voucherId)->update($header);
    //             DB::table('purchase_return_voucher_items')->where('purchase_return_voucher_id', $voucherId)->delete();
    //         } else {
    //             $voucherId = (string) Str::uuid();

    //             $header['id']            = $voucherId;
    //             $header['return_status']  = 'Pending';
    //             $header['postedby']      = $post['userid'];
    //             $header['created_at']    = Carbon::now();
    //             $header['updated_at']    = Carbon::now();

    //             DB::table('purchase_return_vouchers')->insert($header);
    //         }

    //         $itemRows = [];
    //         foreach ($lineData as $line) {
    //             $itemRows[] = [
    //                 'id'                         => (string) Str::uuid(),
    //                 'orgid'                      => $post['orgid'],
    //                 'purchase_return_voucher_id' => $voucherId,
    //                 'item_id'                    => $line['item_id'],
    //                 'variation_id'               => $line['variation_id'],
    //                 'unit'                       => $line['unit'],
    //                 'qty'                        => $line['qty'],
    //                 'unit_rate'                  => $line['unit_rate'],
    //                 'amount'                     => $line['amount'],
    //                 'vat_percent'                => $line['vat_percent'],
    //                 'vat_amount'                 => $line['vat_amount'],
    //                 'excise_type'                => $line['excise_type'],
    //                 'excise_percentage'          => $line['excise_percentage'],
    //                 'excise_value'               => $line['excise_value'],
    //                 'excise_amount'              => $line['excise_amount'],
    //                 'net_amount'                 => $line['net_amount'],
    //                 'created_at'                 => Carbon::now(),
    //                 'updated_at'                 => Carbon::now(),
    //             ];
    //         }
    //         DB::table('purchase_return_voucher_items')->insert($itemRows);

    //         DB::commit();
    //         return true;
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         throw $e;
    //     }
    // }

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

                $vatPercent = ($item->vat_status ?? 'N') === 'Y' ? (float) ($item->vat_percent ?? config('vat.default')) : (float) config('vat.non-taxable');

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

            //  Auto generate debit note number, same pattern as PurchaseVoucher::voucher_no
            if (!empty($post['id'])) {
                // Editing an existing return — keep its original number
                $post['debit_note_no'] = DB::table('purchase_return_vouchers')
                    ->where('id', $post['id'])
                    ->value('debit_note_no');
            } else {
                // New return — generate the next number automatically
                $post['debit_note_no'] = self::getVoucherNumber($post);
            }

            $bsdate = new BsdateController;
            $return_date_eng = $bsdate->nep_to_eng($post['return_date']);

            $header = [
                'return_date'           => $post['return_date'],
                'debit_note_no'         => $post['debit_note_no'],
                'vendor_id'             => $post['vendor_id'],
                'against_voucher_id'    => $post['against_voucher_id'] ?? null,
                'remarks'               => $post['remarks'] ?? null,
                'subtotal'              => round($subtotal, 2),
                'bill_discount_percent' => $billDiscountPercent,
                'bill_discount_amount'  => $billDiscountAmount,
                'taxable_amount'        => round($taxableAmount, 2),
                'vat_amount'            => round($totalVatAmount, 2),
                'excise_amount'         => round($totalExciseAmount, 2),
                'total_amount'          => $totalAmount,
                'return_date_eng'       => $return_date_eng,
                'orgid'                 => $post['orgid'],
            ];

            if (!empty($post['id'])) {
                $voucherId = $post['id'];

                $header['updatedby']  = $post['userid'];
                $header['updated_at'] = Carbon::now();

                DB::table('purchase_return_vouchers')->where('id', $voucherId)->update($header);
                DB::table('purchase_return_voucher_items')->where('purchase_return_voucher_id', $voucherId)->delete();
            } else {
                $voucherId = (string) Str::uuid();

                $orgFiscalYearId = DB::table('organizations')
                    ->where('id', $post['orgid'])
                    ->value('current_fiscal_year_id');

                $header['id']             = $voucherId;
                $header['fiscal_year_id'] = $orgFiscalYearId;
                $header['return_status']  = 'Pending';
                $header['postedby']      = $post['userid'];
                $header['created_at']    = Carbon::now();
                $header['updated_at']    = Carbon::now();

                DB::table('purchase_return_vouchers')->insert($header);
            }

            $itemRows = [];
            foreach ($lineData as $line) {
                $itemRows[] = [
                    'id'                         => (string) Str::uuid(),
                    'orgid'                      => $post['orgid'],
                    'purchase_return_voucher_id' => $voucherId,
                    'item_id'                    => $line['item_id'],
                    'variation_id'               => $line['variation_id'],
                    'unit'                       => $line['unit'],
                    'qty'                        => $line['qty'],
                    'unit_rate'                  => $line['unit_rate'],
                    'amount'                     => $line['amount'],
                    'vat_percent'                => $line['vat_percent'],
                    'vat_amount'                 => $line['vat_amount'],
                    'excise_type'                => $line['excise_type'],
                    'excise_percentage'          => $line['excise_percentage'],
                    'excise_value'               => $line['excise_value'],
                    'excise_amount'              => $line['excise_amount'],
                    'net_amount'                 => $line['net_amount'],
                    'created_at'                 => Carbon::now(),
                    'updated_at'                 => Carbon::now(),
                ];
            }
            DB::table('purchase_return_voucher_items')->insert($itemRows);

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
            throw new Exception('Purchase return voucher ID is required.');
        }

        $voucher = DB::table('purchase_return_vouchers as prv')
            ->join('vendors as v', 'v.id', '=', 'prv.vendor_id')
            ->leftJoin('purchase_vouchers as pv', 'pv.id', '=', 'prv.against_voucher_id')
            ->select('prv.*', 'v.name as vendor_name', 'pv.voucher_no as against_voucher_no')
            ->where('prv.id', $id)
            ->where('prv.orgid', $post['orgid'])
            ->first();

        if (!$voucher) {
            throw new Exception('Purchase return voucher not found.');
        }

        $voucher->items = DB::table('purchase_return_voucher_items as prvi')
            ->join('items as i', 'i.id', '=', 'prvi.item_id')
            ->leftJoin('itemvariations as iv', 'iv.id', '=', 'prvi.variation_id')
            ->select(
                'prvi.*',
                'i.title as item_title',
                'i.product_code as item_product_code',
                'iv.attribute as variation_attribute',
                'iv.value as variation_value',
                'iv.product_code as variation_product_code'
            )
            ->where('prvi.purchase_return_voucher_id', $id)
            ->orderBy('prvi.created_at')
            ->get();

        return $voucher;
    }

    public static function deleteDate($post)
    {
        try {
            $voucher = DB::table('purchase_return_vouchers')
                ->where('id', $post['id'])
                ->where('orgid', $post['orgid'])
                ->first();

            if (!$voucher) {
                throw new Exception("Couldn't delete record. Please try again", 1);
            }

            $updated = DB::table('purchase_return_vouchers')
                ->where('id', $post['id'])
                ->where('orgid', $post['orgid'])
                ->update([
                    'status'        => 'N',
                    // Free up the debit_note_no for reuse: the (orgid, debit_note_no)
                    // unique index applies to all rows regardless of status, so a
                    // soft-deleted row would otherwise permanently block that number.
                    'debit_note_no' => $voucher->debit_note_no . '-DEL-' . substr($post['id'], 0, 8),
                    'updatedby'     => $post['userid'],
                    'updated_at'    => Carbon::now(),
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
            $updated = DB::table('purchase_return_vouchers')
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

    // public static function getVoucherNumber($post)
    // {
    //     try {

    //         $lastVoucher = DB::table('purchase_return_vouchers')
    //             ->where('orgid', $post['orgid'])
    //             ->where('status', 'Y')
    //             ->orderByDesc('debit_note_no')
    //             ->first();

    //         if (!$lastVoucher) {
    //             return 1;
    //         }

    //         return $lastVoucher->debit_note_no + 1;
    //     } catch (\Exception $e) {
    //         throw $e;
    //     }
    // }
    public static function getVoucherNumber($post)
    {
        try {
            $max = DB::table('purchase_return_vouchers')
                ->where('orgid', $post['orgid'])
                ->where('status', 'Y')
                ->max(DB::raw('debit_note_no::integer'));

            return $max ? $max + 1 : 1;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
