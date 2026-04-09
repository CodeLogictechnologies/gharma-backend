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
    protected $keyType = 'string';

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
                $file->move(public_path('uploads/categories'), $imageName);
            }

            $dataArray = [
                'title' => $post['name'],
                'slug' => Str::slug($post['name']) . '-' . time(),
                'status' => 1,
                'orgid' => $post['orgid']
            ];

            // Save image if exists
            if ($imageName) {
                $dataArray['image'] = $imageName;
            }

            if (!empty($post['id'])) {

                // ✅ Update case
                $oldData = Category::find($post['id']);

                // Delete old image if new uploaded
                if ($imageName && $oldData && $oldData->image) {
                    $oldPath = public_path('uploads/categories/' . $oldData->image);
                    if (File::exists($oldPath)) {
                        File::delete($oldPath);
                    }
                }

                $dataArray['updated_at'] = Carbon::now();

                if (!Category::where('id', $post['id'])->update($dataArray)) {
                    throw new \Exception("Couldn't update Records");
                }
            } else {

                // ✅ Insert case
                $dataArray['id'] = (string) Str::uuid();

                $dataArray['created_at'] = Carbon::now();


                if (!Category::insert($dataArray)) {
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

            $sorting = !empty($get['order'][0]['dir']) ? $get['order'][0]['dir'] : 'asc';


            foreach ($get['columns'] as $key => $value) {
                $get['columns'][$key]['search']['value'] = trim(strtolower(htmlspecialchars($value['search']['value'], ENT_QUOTES)));
            }
            $cond = " status = 'Y' ";

            if (!empty($post['type']) && $post['type'] === "trashed") {
                $cond = " status = 'R'";
            }

            if ($get['columns'][1]['search']['value'])
                $cond .= " and lower(title) like '%" . $get['columns'][1]['search']['value'] . "%'";

            $limit = 15;
            $offset = 0;
            if (!empty($get["length"]) && $get["length"]) {
                $limit = $get['length'];
                $offset = $get["start"];
            }

            $query = Category::selectRaw("(SELECT count(*) FROM categories WHERE {$cond} ) AS totalrecs, title,image, id as id")
                ->whereRaw($cond);

            if ($limit > -1) {
                $result = $query->orderby('id', 'desc')->offset($offset)->limit($limit)->get();
            } else {
                $result = $query->orderby('id', 'desc')->get();
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
                'status' => 0,
                'updated_at' => Carbon::now(),
            ];
            if (!Category::where(['id' => $post['id']])->update($updateArray)) {
                throw new Exception("Couldn't Delete Data. Please try again", 1);
            }
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function getCategory($post)
    {
        try {
            $result = DB::table('categories')->select('id', 'title')->where('orgid', $post['orgid'])->where('status', 'Y')->get();

            return $result;
        } catch (Exception $e) {
            throw $e;
        }
    }
}