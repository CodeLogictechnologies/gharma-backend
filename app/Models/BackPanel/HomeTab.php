<?php

namespace App\Models\BackPanel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class HomeTab extends Model
{
    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'id',
        'tab_name',
        'tab_order',
        'icon_name',
        'bg_color',
        'category_id',
        'status',
        'orgid',
        'postedby',
        'updatedby',
    ];

    public static function list(array $post)
    {
        $limit  = (int) ($post['iDisplayLength'] ?? 15);
        $offset = (int) ($post['iDisplayStart']  ?? 0);
        $orgid  = session('orgid');

        $query = DB::table('home_tabs as ht')
            ->select('ht.id', 'ht.tab_name', 'ht.tab_order', 'ht.icon_name', 'ht.bg_color', 'ht.status', 'ht.created_at')
            ->where('ht.status', 'Y')
            ->where('ht.orgid', $orgid);

        $totalrecs     = DB::table('home_tabs')
            ->where('status', 'Y')
            ->where('orgid', $orgid)
            ->count();
        $filteredCount = (clone $query)->count();

        $query->orderBy('ht.tab_order', 'asc')
              ->orderBy('ht.created_at', 'desc');

        if ($limit > -1) {
            $query->offset($offset)->limit($limit);
        }

        $rows = $query->get();   // plain Collection, no extra keys

        // Attach category names for each tab
        $tabIds = $rows->pluck('id');

        $categoryMap = DB::table('home_tab_categories as htc')
            ->join('categories as c', 'c.id', '=', 'htc.category_id')
            ->whereIn('htc.home_tab_id', $tabIds)
            ->select('htc.home_tab_id', 'c.title')
            ->get()
            ->groupBy('home_tab_id');

        $rows = $rows->map(function ($row) use ($categoryMap) {
            $row->category_names = isset($categoryMap[$row->id])
                ? $categoryMap[$row->id]->pluck('title')->implode(', ')
                : '—';
            return $row;
        });

        return [
            'data'          => $rows,
            'totalrecs'     => $totalrecs,
            'filteredCount' => $filteredCount,
        ];
    }

    public static function saveData(array $post)
    {
        try {
            DB::beginTransaction();

            $tabId = $post['id'] ?? null;
            $orgid = session('orgid');

            if (!empty($tabId)) {
                // UPDATE — scoped to the current org so you can't edit another org's tab
                DB::table('home_tabs')
                    ->where('id', $tabId)
                    ->where('orgid', $orgid)
                    ->update([
                        'tab_name'   => $post['tab_name'],
                        'tab_order'  => $post['tab_order'] ?? 0,
                        'icon_name'  => $post['icon_name'] ?? null,
                        'bg_color'   => $post['bg_color']  ?? null,
                        'updatedby'  => $post['userid'],
                        'updated_at' => Carbon::now(),
                    ]);
            } else {
                // INSERT
                $tabId = (string) Str::uuid();
                DB::table('home_tabs')->insert([
                    'id'         => $tabId,
                    'tab_name'   => $post['tab_name'],
                    'tab_order'  => $post['tab_order'] ?? 0,
                    'icon_name'  => $post['icon_name'] ?? null,
                    'bg_color'   => $post['bg_color']  ?? null,
                    'status'     => 'Y',
                    'orgid'      => $orgid,
                    'postedby'   => $post['userid'],
                    'updatedby'  => $post['userid'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            // Sync categories pivot
            DB::table('home_tab_categories')->where('home_tab_id', $tabId)->delete();

            if (!empty($post['category_ids']) && is_array($post['category_ids'])) {
                $rows = [];
                foreach ($post['category_ids'] as $catId) {
                    $rows[] = [
                        'id'          => (string) Str::uuid(),
                        'home_tab_id' => $tabId,
                        'category_id' => $catId,
                        'created_at'  => Carbon::now(),
                        'updated_at'  => Carbon::now(),
                    ];
                }
                DB::table('home_tab_categories')->insert($rows);
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function getData(array $post)
    {
        $tab = DB::table('home_tabs')
            ->where('id', $post['id'])
            ->where('orgid', session('orgid'))
            ->first();

        if ($tab) {
            $tab->category_ids = DB::table('home_tab_categories')
                ->where('home_tab_id', $post['id'])
                ->pluck('category_id')
                ->toArray();
        }

        return $tab;
    }

    public static function deleteData(array $post)
    {
        try {
            DB::beginTransaction();
            DB::table('home_tabs')
                ->where('id', $post['id'])
                ->where('orgid', session('orgid'))
                ->update([
                    'status'     => 'N',
                    'updated_at' => Carbon::now(),
                ]);
            DB::table('home_tab_categories')->where('home_tab_id', $post['id'])->delete();
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}