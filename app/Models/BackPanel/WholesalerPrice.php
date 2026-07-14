<?php

namespace App\Models\BackPanel;

use Illuminate\Database\Eloquent\Model;
use Exception;
use App\Models\Common;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class WholesalerPrice extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    public static function saveData($post)
    {
        try {
            DB::beginTransaction();

            $dataArray = [
                'itemid'       => $post['itemid'],
                'variation_id' => $post['variationid'],
                'postedby'     => Auth::id(),
                'orgid'        => $post['orgid'] ?? null,
            ];

            $duplicate = DB::table('wholesaler_prices')
                ->where('itemid', $post['itemid'])
                ->where('variation_id', $post['variationid']);

            if (!empty($post['id'])) {
                $duplicate->where('id', '!=', $post['id']);
            }

            if ($duplicate->exists()) {
                throw new Exception('Price for the selected product and variation already exists.');
            }

            $getData = DB::table('items')
                ->where('id', $post['itemid'])
                ->select('vat_status', 'excise_status', 'excise_type', 'excise_percentage', 'excise_value', 'vat_percent')
                ->first();

            $exciseStatus     = $getData->excise_status ?? 'N';
            $exciseType       = $getData->excise_type ?? null;
            $excisePercentage = (float) ($getData->excise_percentage ?? 0);
            $exciseValue      = (float) ($getData->excise_value ?? 0);

            $vatStatus  = $getData->vat_status ?? 'N';
            $vatPercent = (float) ($getData->vat_percent ?? 0);

            $calcExciseVat = function (float $price) use ($exciseStatus, $exciseType, $excisePercentage, $exciseValue, $vatStatus, $vatPercent) {
                $excise = 0;
                if ($exciseStatus == 'Y') {
                    $excise = ($exciseType === 'percentage')
                        ? ($price * $excisePercentage) / 100
                        : $exciseValue;
                }

                $subTotal = $price + $excise;
                $vat = ($vatStatus == 'Y') ? ($subTotal * $vatPercent) / 100 : 0;

                $priceAfterExciseVat = $subTotal + $vat;

                return [
                    'before' => round($price, 2),
                    'after'  => round($priceAfterExciseVat, 2),
                ];
            };

            if (!empty($post['id'])) {

                $masterId = $post['id'];

                $dataArray['updatedby']  = Auth::id();
                $dataArray['updated_at'] = Carbon::now();

                DB::table('wholesaler_prices')->where('id', $masterId)->update($dataArray);

                if (!empty($post['wholesaleDet'])) {

                    foreach ($post['wholesaleDet'] as $detail) {

                        if (
                            empty($detail['min_qty']) &&
                            empty($detail['max_qty']) &&
                            empty($detail['price'])
                        ) {
                            continue;
                        }

                        $tierPrice = (float) ($detail['price'] ?? 0);
                        $calc      = $calcExciseVat($tierPrice);

                        $row = [
                            'wholesalermasterid'      => $masterId,
                            'min_qty'                 => $detail['min_qty']  ?? 0,
                            'max_qty'                 => $detail['max_qty']  ?? 0,
                            'price'                   => $tierPrice,
                            'price_before_excise_tax' => $calc['before'],
                            'price_after_excise_tax'  => $calc['after'],
                            'updated_at'              => Carbon::now(),
                            'updatedby'               => Auth::id(),
                        ];

                        if (!empty($detail['wholesaler_price_details_id'])) {
                            DB::table('wholesaler_price_details')
                                ->where('id', $detail['wholesaler_price_details_id'])
                                ->update($row);
                        } else {
                            $row['id']         = (string) Str::uuid();
                            $row['created_at'] = Carbon::now();
                            $row['postedby']   = Auth::id();
                            $row['orgid']      = $post['orgid'] ?? null;

                            DB::table('wholesaler_price_details')->insert($row);
                        }
                    }
                }

                // ════════════════════════════════════════
                // INSERT
                // ════════════════════════════════════════
            } else {

                $masterId = (string) Str::uuid();

                $dataArray['id']         = $masterId;
                $dataArray['created_at'] = Carbon::now();

                $inserted = DB::table('wholesaler_prices')->insert($dataArray);

                if (!$inserted) {
                    throw new Exception("Couldn't save wholesaler price.");
                }

                // ── Insert Price Details ──────────────────────────────────
                if (!empty($post['wholesaleDet'])) {

                    $detailRows = [];

                    foreach ($post['wholesaleDet'] as $detail) {

                        // Skip completely empty rows
                        if (
                            empty($detail['min_qty']) &&
                            empty($detail['max_qty']) &&
                            empty($detail['price'])
                        ) {
                            continue;
                        }

                        $tierPrice = (float) ($detail['price'] ?? 0);
                        $calc      = $calcExciseVat($tierPrice);

                        $detailRows[] = [
                            'id'                      => (string) Str::uuid(),
                            'wholesalermasterid'      => $masterId,
                            'min_qty'                 => $detail['min_qty']  ?? 0,
                            'max_qty'                 => $detail['max_qty']  ?? 0,
                            'price'                   => $tierPrice,
                            'orgid'                   => $post['orgid']      ?? null,
                            'price_before_excise_tax' => $calc['before'],
                            'price_after_excise_tax'  => $calc['after'],
                            'postedby'                => Auth::id(),
                            'created_at'              => Carbon::now(),
                            'updated_at'              => Carbon::now(),
                        ];
                    }

                    if (!empty($detailRows)) {
                        $detailInserted = DB::table('wholesaler_price_details')->insert($detailRows);

                        if (!$detailInserted) {
                            throw new Exception("Couldn't save price details.");
                        }
                    }
                }
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    public static function list(array $post)
    {
        $columns = $post['columns'] ?? [];

        // ── Base conditions (keep as array) ────────────────────
        $conditions = ["wp.status = 'Y'", "i.is_wholesale = 'Y'"];
        $orgid      = $post['orgid'] ?? null;

        if ($orgid) {
            $conditions[] = "wp.orgid = '{$orgid}'";
        }

        // ── Column search (1.9 format) ─────────────────────────
        if (!empty($post['sSearch_1']) && is_string($post['sSearch_1'])) {
            $val          = strtolower(trim($post['sSearch_1']));
            $conditions[] = "lower(i.title) like '%{$val}%'";   // ← [] not .=
        }

        if (!empty($post['sSearch_2']) && is_string($post['sSearch_2'])) {
            $val          = strtolower(trim($post['sSearch_2']));
            $conditions[] = "lower(iv.value) like '%{$val}%'";  // ← [] not .=
        }

        // ── 1.10 format fallback ───────────────────────────────
        if (!empty($columns[1]['search']['value'])) {
            $val          = strtolower(trim((string) $columns[1]['search']['value']));
            $conditions[] = "lower(i.title) like '%{$val}%'";
        }

        if (!empty($columns[2]['search']['value'])) {
            $val          = strtolower(trim((string) $columns[2]['search']['value']));
            $conditions[] = "lower(iv.value) like '%{$val}%'";
        }

        // ── Build where string from array ──────────────────────
        $where  = implode(' AND ', $conditions);   // ← now works correctly
        $limit  = (int) ($post['iDisplayLength'] ?? $post['length'] ?? 15);
        $offset = (int) ($post['iDisplayStart']  ?? $post['start']  ?? 0);

        // ── Total records ──────────────────────────────────────
        $totalrecs = DB::table('wholesaler_prices as wp')
            ->join('items as i',           'i.id',  '=', 'wp.itemid')
            ->join('itemvariations as iv', 'iv.id', '=', 'wp.variation_id')
            ->whereRaw($where)
            ->count();

        // ── Main query ─────────────────────────────────────────
        $query = DB::table('wholesaler_prices as wp')
            ->join('items as i',           'i.id',  '=', 'wp.itemid')
            ->join('itemvariations as iv', 'iv.id', '=', 'wp.variation_id')
            ->selectRaw("
            wp.id,
            i.title,
            iv.value  AS variation_name,
            wp.status,
            wp.created_at,
            (SELECT MIN(wd.min_qty) FROM wholesaler_price_details wd WHERE wd.wholesalermasterid = wp.id) AS min_qty,
            (SELECT MAX(wd.max_qty) FROM wholesaler_price_details wd WHERE wd.wholesalermasterid = wp.id) AS max_qty,
            (SELECT MIN(wd.price)   FROM wholesaler_price_details wd WHERE wd.wholesalermasterid = wp.id) AS min_price,
            (SELECT MAX(wd.price)   FROM wholesaler_price_details wd WHERE wd.wholesalermasterid = wp.id) AS max_price,
            (SELECT COUNT(*)        FROM wholesaler_price_details wd WHERE wd.wholesalermasterid = wp.id) AS detail_count
        ")
            ->whereRaw($where);

        // ── Filtered count ─────────────────────────────────────
        $filteredCount = (clone $query)->count();

        // ── Sort + paginate ────────────────────────────────────
        $query->orderBy('wp.id', 'desc');

        if ($limit > -1) {
            $query->offset($offset)->limit($limit);
        }

        $result = $query->get();

        $result['totalrecs']         = $totalrecs;
        $result['totalfilteredrecs'] = $filteredCount;

        return $result;
    }

    public static function getData($post)
    {
        try {

            $master = DB::table('wholesaler_prices as wp')
                ->join('items as i',           'i.id',  '=', 'wp.itemid')
                ->join('itemvariations as iv', 'iv.id', '=', 'wp.variation_id')
                ->where('wp.id', $post['id'])
                ->select(
                    'wp.id',
                    'wp.status',
                    'vat_status',
                    'excise_status',
                    'excise_type',
                    'excise_percentage',
                    'excise_value',
                    'vat_percent',
                    'wp.created_at',
                    'i.title as itemname',
                    'iv.value as variationname',
                    'wp.itemid',
                    'wp.variation_id'
                )
                ->first();

            if (!$master) {
                return null;
            }

            $details = DB::table('wholesaler_price_details')
                ->where('wholesalermasterid', $master->id)
                ->select(
                    'id as wholesaler_price_details_id',
                    'min_qty',
                    'max_qty',
                    'price',
                    'price_before_excise_tax',
                    'price_after_excise_tax'
                )
                ->get()
                ->map(fn($d) => (array) $d)
                ->toArray();

            return [
                'id'                       => $master->id,
                'itemid'                   => $master->itemid,
                'itemname'                 => $master->itemname,
                'variation_id'             => $master->variation_id,
                'variationname'            => $master->variationname,
                'status'                   => $master->status,
                'created_at'               => $master->created_at,

                'vat_status'               => $master->vat_status,
                'vat_percent'              => $master->vat_percent,
                'excise_status'            => $master->excise_status,
                'excise_type'              => $master->excise_type,
                'excise_percentage'        => $master->excise_percentage,
                'excise_value'             => $master->excise_value,

                'wholesaler_price_details' => $details,
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function deleteData($id)
    {
        try {
            DB::beginTransaction();

            DB::table('wholesaler_price_details')
                ->where('wholesalermasterid', $id)
                ->delete();

            $deleted = DB::table('wholesaler_prices')
                ->where('id', $id)
                ->delete();

            if (!$deleted) {
                throw new Exception('Record not found or already deleted.');
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
