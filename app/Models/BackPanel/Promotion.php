<?php

namespace App\Models\BackPanel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class Promotion extends Model
{
    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'id',
        'name',
        'image',
        'bg_color',
        'applies_to',
        'sort_order',
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

        $query = DB::table('promotions as p')
            ->select('p.id', 'p.name', 'p.image', 'p.bg_color', 'p.applies_to', 'p.sort_order', 'p.status', 'p.created_at')
            ->whereNull('p.deleted_at')
            ->where('p.orgid', $orgid);

        $totalrecs = DB::table('promotions')
            ->whereNull('deleted_at')
            ->where('orgid', $orgid)
            ->count();
        $filteredCount = (clone $query)->count();

        $query->orderBy('p.sort_order')->orderBy('p.created_at', 'desc');

        if ($limit > -1) {
            $query->offset($offset)->limit($limit);
        }

        $rows = $query->get();

        $promotionIds = $rows->pluck('id');

        $itemMap = DB::table('promotion_items as pi')
            ->join('items as i', 'i.id', '=', 'pi.item_id')
            ->whereIn('pi.promotion_id', $promotionIds)
            ->select('pi.promotion_id', 'i.title')
            ->get()
            ->groupBy('promotion_id');

        $categoryMap = DB::table('promotion_categories as pc')
            ->join('categories as c', 'c.id', '=', 'pc.category_id')
            ->whereIn('pc.promotion_id', $promotionIds)
            ->select('pc.promotion_id', 'c.title')
            ->get()
            ->groupBy('promotion_id');

        $rows = $rows->map(function ($row) use ($itemMap, $categoryMap) {
            $names = $row->applies_to === 'item'
                ? ($itemMap[$row->id] ?? collect())
                : ($categoryMap[$row->id] ?? collect());

            $row->target_names = $names->isNotEmpty() ? $names->pluck('title')->implode(', ') : '—';
            $row->image_url    = $row->image ? url('storage/promotions/' . $row->image) : null;

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

            $orgid = session('orgid');
            $id    = $post['id'] ?? null;

            $dataArray = [
                'name'       => $post['name'],
                'bg_color'   => $post['bg_color']   ?? null,
                'applies_to' => $post['applies_to'],
                'sort_order' => (int) ($post['sort_order'] ?? 0),
            ];

            $imageName = null;
            if (!empty($post['image']) && is_object($post['image'])) {
                $file      = $post['image'];
                $imageName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $file->storeAs('promotions', $imageName, 'public');
            }

            if (!empty($id)) {
                $old = DB::table('promotions')->where('id', $id)->where('orgid', $orgid)->whereNull('deleted_at')->first();

                if ($imageName && $old && $old->image) {
                    $oldPath = storage_path('app/public/promotions/' . $old->image);
                    if (File::exists($oldPath)) {
                        File::delete($oldPath);
                    }
                }

                if ($imageName) {
                    $dataArray['image'] = $imageName;
                }

                $dataArray['updatedby']  = $post['userid'];
                $dataArray['updated_at'] = Carbon::now();

                DB::table('promotions')->where('id', $id)->where('orgid', $orgid)->whereNull('deleted_at')->update($dataArray);
            } else {
                $id = (string) Str::uuid();

                $dataArray['id']         = $id;
                $dataArray['image']      = $imageName;
                $dataArray['status']     = 'Y';
                $dataArray['orgid']      = $orgid;
                $dataArray['postedby']   = $post['userid'];
                $dataArray['updatedby']  = $post['userid'];
                $dataArray['created_at'] = Carbon::now();
                $dataArray['updated_at'] = Carbon::now();

                DB::table('promotions')->insert($dataArray);
            }

            DB::table('promotion_items')->where('promotion_id', $id)->delete();
            DB::table('promotion_categories')->where('promotion_id', $id)->delete();

            if ($post['applies_to'] === 'item' && !empty($post['item_ids']) && is_array($post['item_ids'])) {
                $rows = [];
                foreach (array_unique($post['item_ids']) as $itemId) {
                    $rows[] = [
                        'id'           => (string) Str::uuid(),
                        'promotion_id' => $id,
                        'item_id'      => $itemId,
                        'created_at'   => Carbon::now(),
                        'updated_at'   => Carbon::now(),
                    ];
                }
                DB::table('promotion_items')->insert($rows);
            }

            if ($post['applies_to'] === 'category' && !empty($post['category_ids']) && is_array($post['category_ids'])) {
                $rows = [];
                foreach (array_unique($post['category_ids']) as $categoryId) {
                    $rows[] = [
                        'id'           => (string) Str::uuid(),
                        'promotion_id' => $id,
                        'category_id'  => $categoryId,
                        'created_at'   => Carbon::now(),
                        'updated_at'   => Carbon::now(),
                    ];
                }
                DB::table('promotion_categories')->insert($rows);
            }

            DB::commit();
            return $id;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function getData(array $post)
    {
        $promotion = DB::table('promotions')
            ->where('id', $post['id'])
            ->where('orgid', session('orgid'))
            ->whereNull('deleted_at')
            ->first();

        if ($promotion) {
            $promotion->item_ids = DB::table('promotion_items')
                ->where('promotion_id', $post['id'])
                ->pluck('item_id')
                ->toArray();

            $promotion->category_ids = DB::table('promotion_categories')
                ->where('promotion_id', $post['id'])
                ->pluck('category_id')
                ->toArray();

            $promotion->image_url = $promotion->image
                ? url('storage/promotions/' . $promotion->image)
                : null;
        }

        return $promotion;
    }

    public static function deleteData(array $post)
    {
        try {
            DB::beginTransaction();
            DB::table('promotions')
                ->where('id', $post['id'])
                ->where('orgid', session('orgid'))
                ->whereNull('deleted_at')
                ->update([
                    'deleted_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            DB::table('promotion_items')->where('promotion_id', $post['id'])->delete();
            DB::table('promotion_categories')->where('promotion_id', $post['id'])->delete();
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function toggleStatus(array $post)
    {
        $orgid = session('orgid');

        $promotion = DB::table('promotions')
            ->where('id', $post['id'])
            ->where('orgid', $orgid)
            ->whereNull('deleted_at')
            ->first();

        if (!$promotion) {
            throw new Exception('Promotion not found.');
        }

        $newStatus = $promotion->status === 'Y' ? 'N' : 'Y';

        DB::table('promotions')
            ->where('id', $post['id'])
            ->where('orgid', $orgid)
            ->update([
                'status'     => $newStatus,
                'updatedby'  => $post['userid'] ?? null,
                'updated_at' => Carbon::now(),
            ]);

        return $newStatus;
    }
}
