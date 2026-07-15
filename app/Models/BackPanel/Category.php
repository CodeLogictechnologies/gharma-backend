<?php

namespace App\Models\BackPanel;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

// class Category extends Model
// {
//     use HasFactory;

//     public $incrementing = false;
//     protected $keyType = 'string';

//     public static function saveData($post)
//     {
//         try {

//             $imageName = null;

//             //  Handle Image Upload
//             if (!empty($post['image'])) {
//                 $file = $post['image'];

//                 // Create unique name
//                 $imageName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

//                 // Move image to public folder
//                 $file->storeAs('categories', $imageName, 'public');
//             }

//             $dataArray = [
//                 'title' => $post['name'],
//                 'slug' => Str::slug($post['name']) . '-' . time(),
//                 'status' => 'Y',
//                 'orgid' => $post['orgid']
//             ];

//             // Save image if exists
//             if ($imageName) {
//                 $dataArray['image'] = $imageName;
//             }

//             if (!empty($post['id'])) {

//                 //  Update case
//                 $oldData = Category::find($post['id']);

//                 //  Delete old image from storage
//                 if ($imageName && $oldData && $oldData->image) {
//                     $oldPath = storage_path('app/public/categories/' . $oldData->image);
//                     if (File::exists($oldPath)) {
//                         File::delete($oldPath);
//                     }
//                 }
//                 $dataArray['updated_at'] = Carbon::now();

//                 if (!Category::where('id', $post['id'])->update($dataArray)) {
//                     throw new \Exception("Couldn't update Records");
//                 }
//             } else {

//                 $dataArray['id'] = (string) Str::uuid();

//                 $dataArray['created_at'] = Carbon::now();


//                 if (!Category::insert($dataArray)) {
//                     throw new \Exception("Couldn't Save Records");
//                 }
//             }

//             return true;
//         } catch (\Exception $e) {
//             throw $e;
//         }
//     }

//     //function to list team category
//     public static function list($post)
//     {
//         try {
//             $get = $post;

//             $sorting = !empty($get['order'][0]['dir']) ? $get['order'][0]['dir'] : 'asc';


//             foreach ($get['columns'] as $key => $value) {
//                 $get['columns'][$key]['search']['value'] = trim(strtolower(htmlspecialchars($value['search']['value'], ENT_QUOTES)));
//             }
//             $cond = " status = 'Y' ";

//             if (!empty($post['type']) && $post['type'] === "trashed") {
//                 $cond = " status = 'R'";
//             }

//             if ($get['columns'][1]['search']['value'])
//                 $cond .= " and lower(title) like '%" . $get['columns'][1]['search']['value'] . "%'";

//             $limit = 15;
//             $offset = 0;
//             if (!empty($get["length"]) && $get["length"]) {
//                 $limit = $get['length'];
//                 $offset = $get["start"];
//             }

//             $query = Category::selectRaw("(SELECT count(*) FROM categories WHERE {$cond} ) AS totalrecs, title,image, id as id")
//                 ->whereRaw($cond);

//             if ($limit > -1) {
//                 $result = $query->orderby('id', 'desc')->offset($offset)->limit($limit)->get();
//             } else {
//                 $result = $query->orderby('id', 'desc')->get();
//             }
//             if ($result) {
//                 $ndata = $result;
//                 $ndata['totalrecs'] = @$result[0]->totalrecs ? $result[0]->totalrecs : 0;
//                 $ndata['totalfilteredrecs'] = @$result[0]->totalrecs ? $result[0]->totalrecs : 0;
//             } else {
//                 $ndata = array();
//             }
//             return $ndata;
//         } catch (Exception $e) {
//             throw $e;
//         }
//     }

//     //restore 
//     public static function deleteCategory($post)
//     {
//         try {
//             $updateArray = [
//                 'status' => 'N',
//                 'updated_at' => Carbon::now(),
//             ];
//             if (!Category::where(['id' => $post['id']])->update($updateArray)) {
//                 throw new Exception("Couldn't Delete Data. Please try again", 1);
//             }
//             return true;
//         } catch (Exception $e) {
//             throw $e;
//         }
//     }

//     public static function getCategory($post)
//     {
//         try {
//             $result = DB::table('categories')->select('id', 'title')->where('orgid', $post['orgid'])->where('status', 'Y')->get();

//             return $result;
//         } catch (Exception $e) {
//             throw $e;
//         }
//     }
// }

class Category extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType   = 'string';
    protected $table     = 'categories';

    // ─────────────────────────────────────────────────────────────────
    // Save (insert or update)
    // ─────────────────────────────────────────────────────────────────
    public static function saveData($post)
    {
        try {
            $imageName = null;

            if (!empty($post['image'])) {
                $file      = $post['image'];
                $imageName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $file->storeAs('categories', $imageName, 'public');
            }

            $dataArray = [
                'title'     => $post['name'],
                'slug'      => Str::slug($post['name']) . '-' . time(),
                'status'    => 'Y',
                'orgid'     => $post['orgid'],
                'parent_id' => !empty($post['parent_id']) ? $post['parent_id'] : null,
            ];

            if ($imageName) {
                $dataArray['image'] = $imageName;
            }

            if (!empty($post['id'])) {
                // Update
                $oldData = Category::find($post['id']);

                if ($imageName && $oldData && $oldData->image) {
                    $oldPath = storage_path('app/public/categories/' . $oldData->image);
                    if (File::exists($oldPath)) File::delete($oldPath);
                }

                $dataArray['updated_at'] = Carbon::now();
                if (!Category::where('id', $post['id'])->update($dataArray)) {
                    throw new \Exception("Couldn't update record.");
                }
            } else {
                // Insert
                $dataArray['id']         = (string) Str::uuid();
                $dataArray['created_at'] = Carbon::now();
                if (!Category::insert($dataArray)) {
                    throw new \Exception("Couldn't save record.");
                }
            }

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // List for DataTable (server-side)
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

            // Search by category name (column index 1)
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
                ->leftJoin('categories as p', 'p.id', '=', 'c.parent_id')
                ->selectRaw("
                    (SELECT COUNT(*) FROM categories WHERE {$cond}) AS totalrecs,
                    c.id,
                    c.title,
                    c.image,
                    c.parent_id,
                    p.title AS parent_name
                ")
                ->whereRaw($cond);

            $result = ($limit > -1)
                ? $query->orderByRaw('COALESCE(c.updated_at, c.created_at) DESC')->offset($offset)->limit($limit)->get()
                : $query->orderByRaw('COALESCE(c.updated_at, c.created_at) DESC')->get();

            $ndata                    = $result;
            $ndata['totalrecs']       = $result->isNotEmpty() ? $result[0]->totalrecs : 0;
            $ndata['totalfilteredrecs'] = $ndata['totalrecs'];

            return $ndata;
        } catch (Exception $e) {
            throw $e;
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // Soft delete (status = N)
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
    // Get all active categories for Parent dropdown
    // Excludes the category being edited (to prevent self-reference)
    // ─────────────────────────────────────────────────────────────────
    public static function getParentOptions($orgid, $excludeId = null)
    {
        try {
            $query = DB::table('categories')
                ->select('id', 'title')
                ->where('status', 'Y')
                ->where('orgid', $orgid)
                ->whereNull('parent_id'); // only top-level categories can be parents

            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }

            return $query->orderBy('title')->get();
        } catch (Exception $e) {
            throw $e;
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // Used by other controllers (SubCategory dropdown etc.)
    // ─────────────────────────────────────────────────────────────────
    public static function getCategory($post)
    {
        try {
            return DB::table('categories')
                ->select('id', 'title')
                ->where('orgid', $post['orgid'])
                ->where('status', 'Y')
                ->orderBy('title')
                ->get();
        } catch (Exception $e) {
            throw $e;
        }
    }
}
