<?php

namespace App\Models\BackPanel;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class Category extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType   = 'string';
    protected $table     = 'categories';

    // 0 = Category, 1 = Subcategory, 2 = Sub-subcategory
    const MAX_LEVEL = 2;

    // ─────────────────────────────────────────────────────────────────
    // walk parent_id chain to compute depth (no schema change needed)
    // ─────────────────────────────────────────────────────────────────
    public static function levelOf($id)
    {
        $level   = 0;
        $guard   = 0;
        $current = DB::table('categories')->select('parent_id')->where('id', $id)->first();

        while ($current && $current->parent_id && $guard < 10) {
            $level++;
            $current = DB::table('categories')->select('parent_id')->where('id', $current->parent_id)->first();
            $guard++;
        }

        return $level;
    }

    // prevent picking your own child/grandchild as your parent (would loop)
    private static function isDescendantOf($candidateParentId, $id)
    {
        $current = $candidateParentId;
        $guard   = 0;
        while ($current && $guard < 10) {
            if ($current == $id) return true;
            $row     = DB::table('categories')->select('parent_id')->where('id', $current)->first();
            $current = $row->parent_id ?? null;
            $guard++;
        }
        return false;
    }

    // ─────────────────────────────────────────────────────────────────
    // Save (insert or update) — unchanged except for the depth-limit check
    // ─────────────────────────────────────────────────────────────────
    // public static function saveData($post)
    // {
    //     try {
    //         $imageName = null;

    //         if (!empty($post['image'])) {
    //             $file      = $post['image'];
    //             $imageName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
    //             $file->storeAs('categories', $imageName, 'public');
    //         }

    //         $parentId = !empty($post['parent_id']) ? $post['parent_id'] : null;

    //         // block going past Sub-subcategory, and block circular parenting
    //         if ($parentId) {
    //             $newLevel = self::levelOf($parentId) + 1;
    //             if ($newLevel > self::MAX_LEVEL) {
    //                 throw new \Exception('Cannot nest categories more than 3 levels deep.');
    //             }
    //             if (!empty($post['id']) && self::isDescendantOf($parentId, $post['id'])) {
    //                 throw new \Exception('Cannot select a sub-category as its own parent.');
    //             }
    //         }


    //         $dataArray = [
    //             'title'     => $post['name'],
    //             'slug'      => Str::slug($post['name']) . '-' . time(),
    //             'status'    => 'Y',
    //             'orgid'     => $post['orgid'],
    //             'parent_id' => $parentId,
    //         ];

    //         if ($imageName) {
    //             $dataArray['image'] = $imageName;
    //         }

    //         if (!empty($post['id'])) {
    //             $oldData = Category::find($post['id']);

    //             if ($imageName && $oldData && $oldData->image) {
    //                 $oldPath = storage_path('app/public/categories/' . $oldData->image);
    //                 if (File::exists($oldPath)) File::delete($oldPath);
    //             }

    //             $dataArray['updated_at'] = Carbon::now();
    //             if (!Category::where('id', $post['id'])->update($dataArray)) {
    //                 throw new \Exception("Couldn't update record.");
    //             }
    //         } else {
    //             $dataArray['id']         = (string) Str::uuid();
    //             $dataArray['created_at'] = Carbon::now();
    //             if (!Category::insert($dataArray)) {
    //                 throw new \Exception("Couldn't save record.");
    //             }
    //         }

    //         return true;
    //     } catch (\Exception $e) {
    //         throw $e;
    //     }
    // }
    // ─────────────────────────────────────────────────────────────────
    // Walk the parent_id chain all the way to the root and return the
    // 1-based level (1 = Category, 2 = Subcategory, 3 = Sub-subcategory)
    // ─────────────────────────────────────────────────────────────────
    // public static function levelOf($id)
    // {
    //     $level   = 1;
    //     $guard   = 0;
    //     $current = DB::table('categories')->select('parent_id')->where('id', $id)->first();

    //     while ($current && $current->parent_id && $guard < 10) {
    //         $level++;
    //         $current = DB::table('categories')->select('parent_id')->where('id', $current->parent_id)->first();
    //         $guard++;
    //     }

    //     return $level;
    // }

    public static function saveData($post)
    {
        try {
            $imageName = null;

            if (!empty($post['image'])) {
                $file      = $post['image'];
                $imageName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $file->storeAs('categories', $imageName, 'public');
            }

            $hasCategory    = !empty($post['category_id']);
            $hasSubcategory = !empty($post['subcategory_id']);

            if (!$hasCategory && !$hasSubcategory) {
                $level         = 1;
                $parentId      = null;
                $subcategoryId = null;
            } elseif ($hasCategory && !$hasSubcategory) {
                $level         = 2;
                $parentId      = $post['category_id'];
                $subcategoryId = null;
            } else {
                $level         = 3;
                $parentId      = $post['subcategory_id'];     //  direct parent is the subcategory
                $subcategoryId = $post['subcategory_id'];
            }

            $dataArray = [
                'title'          => $post['name'],
                'slug'           => Str::slug($post['name']) . '-' . time(),
                'status'         => 'Y',
                'orgid'          => $post['orgid'],
                'level'          => $level,
                'parent_id'      => $parentId,
                'sub_category_id' => $subcategoryId,           //  matches actual column
            ];

            if ($imageName) {
                $dataArray['image'] = $imageName;
            }

            if (!empty($post['id'])) {
                $oldData = Category::find($post['id']);

                if ($imageName && $oldData && $oldData->image) {
                    $oldPath = storage_path('app/public/categories/' . $oldData->image);
                    if (File::exists($oldPath)) File::delete($oldPath);
                }

                $dataArray['updated_at'] = Carbon::now();
                if (!Category::where('id', $post['id'])->update($dataArray)) {
                    throw new \Exception("Couldn't update record.");
                }
                return $post['id'];
            } else {
                $newId = (string) Str::uuid();
                $dataArray['id']         = $newId;
                $dataArray['created_at'] = Carbon::now();
                if (!Category::insert($dataArray)) {
                    throw new \Exception("Couldn't save record.");
                }

                return $newId;                   // ← was: (nothing, fell through to `return true;` below)
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // List — adds "level" plus "top_category_id" / "sub_category_id"
    // so the edit form can correctly re-populate the two cascading
    // dropdowns (Category + Sub Category) for any item, regardless
    // of whether it's a Category, Subcategory, or Sub-subcategory.
    // ─────────────────────────────────────────────────────────────────
    public static function list($post)
    {
        try {
            $get = $post;

            foreach ($get['columns'] as $key => $value) {
                $get['columns'][$key]['search']['value'] =
                    trim(strtolower(htmlspecialchars($value['search']['value'], ENT_QUOTES)));
            }

            $cond = "c.status = 'Y' AND c.orgid = '" . addslashes($post['orgid']) . "'";

            if (!empty($get['columns'][1]['search']['value'])) {
                $cond .= " AND LOWER(c.title) LIKE '%" . $get['columns'][1]['search']['value'] . "%'";
            }

            $limit  = 10;
            $offset = 0;
            if (!empty($get['length'])) {
                $limit  = (int) $get['length'];
                $offset = (int) $get['start'];
            }

            $query = Category::from('categories as c')
                ->leftJoin('categories as p1', 'p1.id', '=', 'c.parent_id')      // direct parent
                ->leftJoin('categories as p2', 'p2.id', '=', 'p1.parent_id')     // grandparent
                ->selectRaw("
    (SELECT COUNT(*) FROM categories AS cc WHERE " . str_replace('c.', 'cc.', $cond) . ") AS totalrecs,
                    c.id,
                    c.title,
                    c.image,
                    c.parent_id,
                    p1.title AS parent_name,
                    CASE
                        WHEN c.parent_id IS NULL THEN 0
                        WHEN p1.parent_id IS NULL THEN 1
                        ELSE 2
                    END AS level,
                    -- top-level Category id to preselect in the Category dropdown
                    CASE
                        WHEN c.parent_id IS NULL THEN c.id
                        WHEN p1.parent_id IS NULL THEN c.parent_id
                        ELSE p1.parent_id
                    END AS top_category_id,
                    -- Sub Category id to preselect in the Sub Category dropdown
                    -- (only applies when this row is itself a sub-subcategory)
                    CASE
                        WHEN c.parent_id IS NULL THEN NULL
                        WHEN p1.parent_id IS NULL THEN NULL
                        ELSE c.parent_id
                    END AS sub_category_id
                ")
                ->whereRaw($cond);

            $result = ($limit > -1)
                ? $query->orderByRaw('COALESCE(c.updated_at, c.created_at) DESC')->offset($offset)->limit($limit)->get()
                : $query->orderByRaw('COALESCE(c.updated_at, c.created_at) DESC')->get();

            $ndata                      = $result;
            $ndata['totalrecs']         = $result->isNotEmpty() ? $result[0]->totalrecs : 0;
            $ndata['totalfilteredrecs'] = $ndata['totalrecs'];

            return $ndata;
        } catch (Exception $e) {
            throw $e;
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // Soft delete — unchanged
    // ─────────────────────────────────────────────────────────────────
    public static function deleteCategory($post)
    {
        try {
            if (!Category::where('id', $post['id'])
                ->where('orgid', $post['orgid'])
                ->update([
                    'status'     => 'N',
                    'updated_at' => Carbon::now(),
                ])) {
                throw new Exception("Couldn't delete record.");
            }
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // NEW: Top-level categories only (parent_id IS NULL) — powers the
    // first "Category" dropdown. $excludeId lets edit mode hide itself.
    // ─────────────────────────────────────────────────────────────────
    public static function getTopLevelCategories($orgid, $excludeId = null)
    {
        try {
            return DB::table('categories')
                ->select('id', 'title')
                ->where('status', 'Y')
                ->where('orgid', $orgid)
                ->whereNull('parent_id')
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->orderBy('title')
                ->get();
        } catch (Exception $e) {
            throw $e;
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // NEW: Direct children of a given category — powers the second
    // "Sub Category" dropdown, populated via AJAX once a Category is
    // chosen. $excludeId lets edit mode hide itself.
    // ─────────────────────────────────────────────────────────────────
    public static function getChildrenOf($orgid, $parentId, $excludeId = null)
    {
        try {
            if (empty($parentId)) {
                return collect();
            }

            return DB::table('categories')
                ->select('id', 'title')
                ->where('status', 'Y')
                ->where('orgid', $orgid)
                ->where('parent_id', $parentId)
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->orderBy('title')
                ->get();
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Kept for backward compatibility (no longer used by the new cascading UI)
    public static function getParentOptions($orgid, $excludeId = null)
    {
        try {
            $rows = DB::table('categories')
                ->select('id', 'title', 'parent_id')
                ->where('status', 'Y')
                ->where('orgid', $orgid)
                ->orderBy('title')
                ->get();

            $byId    = $rows->keyBy('id');
            $options = [];

            foreach ($rows as $row) {
                if ($excludeId && $row->id == $excludeId) continue;

                $level                 = 0;
                $walk                  = $row;
                $guard                 = 0;
                $isDescendantOfExclude = false;

                while ($walk->parent_id && $guard < 10) {
                    if ($excludeId && $walk->parent_id == $excludeId) {
                        $isDescendantOfExclude = true;
                    }
                    $level++;
                    $walk = $byId->get($walk->parent_id);
                    if (!$walk) break;
                    $guard++;
                }

                if ($isDescendantOfExclude) continue;
                if ($level > 1) continue;

                $options[] = (object) [
                    'id'    => $row->id,
                    'title' => str_repeat('— ', $level) . $row->title,
                ];
            }

            return collect($options);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function getCategory($post)
    {
        try {
            return DB::table('categories')
                ->select('id', 'title')
                ->where('orgid', $post['orgid'])
                ->where('status', 'Y')
                ->whereNull('parent_id')
                ->orderBy('title')
                ->get();
        } catch (Exception $e) {
            throw $e;
        }
    }
}
