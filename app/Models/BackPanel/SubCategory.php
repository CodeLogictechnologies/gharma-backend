<?php

namespace App\Models\BackPanel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class SubCategory extends Model
{
    use HasFactory;


    public $incrementing = false;
    protected $keyType = 'string';


    //function to save sub category
    public static function saveData($post)
    {
        try {

            $imageName = null;

            // ✅ Handle Image Upload
            if (!empty($post['image'])) {
                $file = $post['image'];

                // Create unique name
                $imageName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

                // Move image to public folder
                $file->move(public_path('uploads/subcategories'), $imageName);
            }

            $dataArray = [
                'title' => $post['title'],
                'slug' => Str::slug($post['title']) . '-' . time(),
                'status' => 1,
                'orgid' => $post['orgid']
            ];

            // Save image if exists
            if ($imageName) {
                $dataArray['image'] = $imageName;
            }

            if (!empty($post['id'])) {

                // ✅ Update case
                $oldData = SubCategory::find($post['id']);

                // Delete old image if new uploaded
                if ($imageName && $oldData && $oldData->image) {
                    $oldPath = public_path('uploads/subcategories/' . $oldData->image);
                    if (File::exists($oldPath)) {
                        File::delete($oldPath);
                    }
                }

                $dataArray['updated_at'] = Carbon::now();

                if (!SubCategory::where('id', $post['id'])->update($dataArray)) {
                    throw new \Exception("Couldn't update Records");
                }
            } else {

                $dataArray['id'] = (string) Str::uuid();

                $dataArray['created_at'] = Carbon::now();

                if (!SubCategory::insert($dataArray)) {
                    throw new \Exception("Couldn't Save Records");
                }
            }

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    //function to list team category
    public static function list($post)
    {
        try {
            $get = $post;


            foreach ($get['columns'] as $key => $value) {
                $get['columns'][$key]['search']['value'] = trim(strtolower(htmlspecialchars($value['search']['value'], ENT_QUOTES)));
            }
            $cond = " s.status = 1";


            if ($get['columns'][1]['search']['value'])
                $cond .= " and lower(s.title) like '%" . $get['columns'][1]['search']['value'] . "%'";

            if ($get['columns'][2]['search']['value'])
                $cond .= " and lower(s.title) like '%" . $get['columns'][2]['search']['value'] . "%'";

            $limit = 15;
            $offset = 0;
            if (!empty($get["length"]) && $get["length"]) {
                $limit = $get['length'];
                $offset = $get["start"];
            }

            $query = SubCategory::from('sub_categories as s')
                ->selectRaw("
        (SELECT COUNT(*) FROM sub_categories WHERE {$cond}) as totalrecs,
        s.id,
        s.title,
        s.image
    ")
                ->whereRaw($cond);

            if ($limit > -1) {
                $result = $query->orderby('s.id', 'desc')->offset($offset)->limit($limit)->get();
            } else {
                $result = $query->orderby('s.id', 'desc')->get();
            }
            if ($result) {
                $ndata = $result;
                $ndata['totalrecs'] = @$result[0]->totalrecs ? $result[0]->totalrecs : 0;
                $ndata['totalfilteredrecs'] = @$result[0]->totalrecs ? $result[0]->totalrecs : 0;
            } else {
                $ndata = array();
            }
            return $ndata;
        } catch (Exception $e) {
            throw $e;
        }
    }

    //restore 
    public static function deleteCategory($post)
    {
        try {
            $updateArray = [
                'status' => 'N',
                'updated_at' => Carbon::now(),
            ];
            if (!SubCategory::where(['id' => $post['id']])->update($updateArray)) {
                throw new Exception("Couldn't Delete Data. Please try again", 1);
            }
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function getCategory()
    {
        try {
            $result = DB::table('categories')->select('title', 'id')->where('status', 1)->get();

            return $result;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function getSubCategory($post)
    {
        try {
            $result = DB::table('sub_categories')->select('id', 'title')->where('orgid', $post['orgid'])->where('status', 1)->get();
            return $result;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
