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

            // ════════════════════════════════════════
            // UPDATE
            // ════════════════════════════════════════
            if (!empty($post['id'])) {

                $masterId = $post['id'];

                $dataArray['updatedby']  = Auth::id();
                $dataArray['updated_at'] = Carbon::now();

                DB::table('wholesaler_prices')->where('id', $masterId)->update($dataArray);

                // ── Update / Insert Price Details ─────────────────────────
                if (!empty($post['wholesaleDet'])) {

                    foreach ($post['wholesaleDet'] as $detail) {

                        // Skip completely empty rows
                        if (
                            empty($detail['min_qty']) &&
                            empty($detail['max_qty']) &&
                            empty($detail['price'])
                        ) {
                            continue;
                        }

                        $row = [
                            'wholesalermasterid' => $masterId,
                            'min_qty'            => $detail['min_qty']  ?? 0,
                            'max_qty'            => $detail['max_qty']  ?? 0,
                            'price'              => $detail['price']    ?? 0,
                            'updated_at'         => Carbon::now(),
                            'updatedby'          => Auth::id(),
                        ];

                        if (!empty($detail['wholesaler_price_details_id'])) {
                            // Existing row — update
                            DB::table('wholesaler_price_details')
                                ->where('id', $detail['wholesaler_price_details_id'])
                                ->update($row);
                        } else {
                            // New row added during edit — insert
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

                        $detailRows[] = [
                            'id'                 => (string) Str::uuid(),
                            'wholesalermasterid' => $masterId,
                            'min_qty'            => $detail['min_qty']  ?? 0,
                            'max_qty'            => $detail['max_qty']  ?? 0,
                            'price'              => $detail['price']    ?? 0,
                            'orgid'              => $post['orgid']      ?? null,
                            'postedby'           => Auth::id(),
                            'created_at'         => Carbon::now(),
                            'updated_at'         => Carbon::now(),
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
        // Sanitise DataTable column search values
        $columns = $post['columns'] ?? [];
        foreach ($columns as &$col) {
            $col['search']['value'] = trim(strtolower(
                htmlspecialchars($col['search']['value'] ?? '', ENT_QUOTES)
            ));
        }
        unset($col);

        // ── Base condition ─────────────────────────────────────────────────
        $conditions = ["wp.status = 'Y'"];
        $orgid      = $post['orgid'] ?? null;

        if ($orgid) {
            $conditions[] = "wp.orgid = '{$orgid}'";
        }

        // Column[1] search → item name
        if (!empty($columns[1]['search']['value'])) {
            $val          = $columns[1]['search']['value'];
            $conditions[] = "lower(i.title) like '%{$val}%'";
        }

        // Column[2] search → variation name
        if (!empty($columns[2]['search']['value'])) {
            $val          = $columns[2]['search']['value'];
            $conditions[] = "lower(iv.value) like '%{$val}%'";
        }

        $where  = implode(' AND ', $conditions);
        $limit  = (int) ($post['length'] ?? 15);
        $offset = (int) ($post['start']  ?? 0);

        // ── Total records (master rows only, no detail join) ───────────────
        $totalrecs = DB::table('wholesaler_prices as wp')
            ->join('items as i',        'i.id',   '=', 'wp.itemid')
            ->join('itemvariations as iv', 'iv.id', '=', 'wp.variation_id')
            ->whereRaw($where)
            ->count();

        // ── Main query — group by master so details don't multiply rows ────
        $query = DB::table('wholesaler_prices as wp')
            ->join('items as i',               'i.id',   '=', 'wp.itemid')
            ->join('itemvariations as iv',     'iv.id',  '=', 'wp.variation_id')
            ->selectRaw("
            wp.id,
            i.title,
            iv.value AS variation_name,
            wp.status,
            wp.created_at,
            (SELECT MIN(wd.min_qty) FROM wholesaler_price_details wd WHERE wd.wholesalermasterid = wp.id) AS min_qty,
            (SELECT MAX(wd.max_qty) FROM wholesaler_price_details wd WHERE wd.wholesalermasterid = wp.id) AS max_qty,
            (SELECT MIN(wd.price)   FROM wholesaler_price_details wd WHERE wd.wholesalermasterid = wp.id) AS min_price,
            (SELECT MAX(wd.price)   FROM wholesaler_price_details wd WHERE wd.wholesalermasterid = wp.id) AS max_price,
            (SELECT COUNT(*)        FROM wholesaler_price_details wd WHERE wd.wholesalermasterid = wp.id) AS detail_count
        ")
            ->whereRaw($where);

        // ── Filtered count (before pagination) ────────────────────────────
        $filteredCount = (clone $query)->count();

        // ── Pagination ────────────────────────────────────────────────────
        $query->orderBy('wp.id', 'desc');

        if ($limit > -1) {
            $query->offset($offset)->limit($limit);
        }

        $result = $query->get();

        $result['totalrecs']         = $totalrecs;
        $result['totalfilteredrecs'] = $filteredCount;

        return $result;
    }
}