<?php

namespace App\Models\BackPanel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class SubSubCategory extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $keyType = 'string';

    // function to save sub sub category
    public static function saveData($post)
    {
        try {

            $imageName = null;

            //  Handle Image Upload
            if (!empty($post['image'])) {
                $file = $post['image'];

                // Create unique name
                $imageName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

                // Move image to public folder
                $file->storeAs('subsubcategories', $imageName, 'public');
            }

            $dataArray = [
                'title' => $post['title'],
                'slug' => Str::slug($post['title']) . '-' . time(),
                'status' => 'Y',
                'orgid' => $post['orgid'],
                'subcategory_id' => $post['subcategory_id'] ?? null,
            ];

            // Save image if exists
            if ($imageName) {
                $dataArray['image'] = $imageName;
            }

            if (!empty($post['id'])) {

                //  Update case
                $oldData = SubSubCategory::find($post['id']);

                // Delete old image if new uploaded
                if ($imageName && $oldData && $oldData->image) {
                    $oldPath = storage_path('app/public/subsubcategories/' . $oldData->image);
                    if (File::exists($oldPath)) {
                        File::delete($oldPath);
                    }
                }

                $dataArray['updated_at'] = Carbon::now();

                if (!SubSubCategory::where('id', $post['id'])->update($dataArray)) {
                    throw new \Exception("Couldn't update Records");
                }
            } else {

                $dataArray['id'] = (string) Str::uuid();

                $dataArray['created_at'] = Carbon::now();

                if (!SubSubCategory::insert($dataArray)) {
                    throw new \Exception("Couldn't Save Records");
                }
            }

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // function to list sub sub category
    public static function list($post)
    {
        try {
            $get = $post;

            foreach ($get['columns'] as $key => $value) {
                $get['columns'][$key]['search']['value'] = trim(strtolower(htmlspecialchars($value['search']['value'], ENT_QUOTES)));
            }

            $cond = " ss.status = 'Y'";

            // column 1 = Category, column 2 = Sub Category, column 3 = Sub Sub Category
            if ($get['columns'][1]['search']['value'])
                $cond .= " and lower(c.title) like '%" . $get['columns'][1]['search']['value'] . "%'";

            if ($get['columns'][2]['search']['value'])
                $cond .= " and lower(s.title) like '%" . $get['columns'][2]['search']['value'] . "%'";

            if ($get['columns'][3]['search']['value'])
                $cond .= " and lower(ss.title) like '%" . $get['columns'][3]['search']['value'] . "%'";

            $limit = 15;
            $offset = 0;
            if (!empty($get["length"]) && $get["length"]) {
                $limit = $get['length'];
                $offset = $get["start"];
            }

            $query = SubSubCategory::from('sub_sub_categories as ss')
                ->leftJoin('sub_categories as s', 's.id', '=', 'ss.subcategory_id')
                ->leftJoin('categories as c', 'c.id', '=', 's.category_id')
                ->selectRaw("
    (SELECT COUNT(*) FROM sub_sub_categories WHERE {$cond}) as totalrecs,
    ss.id,
    ss.title,
    ss.image,
    ss.subcategory_id,
    ss.created_at,
    ss.updated_at,
    s.title as subcategory_name,
    c.title as category_name
")
                ->whereRaw($cond);

            if ($limit > -1) {
                $result = $query->orderByRaw('COALESCE(ss.updated_at, ss.created_at) desc')->offset($offset)->limit($limit)->get();
            } else {
                $result = $query->orderByRaw('COALESCE(ss.updated_at, ss.created_at) desc')->get();
            }

            if ($result) {
                $ndata = $result;
                $ndata['totalrecs'] = @$result[0]->totalrecs ? $result[0]->totalrecs : 0;
                $ndata['totalfilteredrecs'] = @$result[0]->totalrecs ? $result[0]->totalrecs : 0;
            } else {
                $ndata = [];
            }

            return $ndata;
        } catch (Exception $e) {
            throw $e;
        }
    }

    // soft delete
    public static function deleteCategory($post)
    {
        try {
            $updateArray = [
                'status' => 'N',
                'updated_at' => Carbon::now(),
            ];
            if (!SubSubCategory::where(['id' => $post['id']])->update($updateArray)) {
                throw new Exception("Couldn't Delete Data. Please try again", 1);
            }
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Sub Category dropdown for the form — includes parent Category name
    // so the option reads like "Electronics > Mobile Phones"
    public static function getSubCategory($post)
    {
        try {
            $result = DB::table('sub_categories as s')
                ->leftJoin('categories as c', 'c.id', '=', 's.category_id')
                ->select('s.id', 's.title', 'c.title as category_title')
                ->where('s.orgid', $post['orgid'])
                ->where('s.status', 'Y')
                ->orderBy('c.title')
                ->orderBy('s.title')
                ->get();
            return $result;
        } catch (Exception $e) {
            throw $e;
        }
    }
}