<?php


namespace App\Models\BackPanel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class Brand extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    //function to save team category

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
                $file->move(public_path('uploads/brands'), $imageName);
            }

            $dataArray = [
                'name' => $post['name'],
                'description' => $post['description'],
                'slug' => Str::slug($post['name']) . '-' . time(),
                'status' => 'Y',
                'orgid' => $post['orgid'],
                'postedby' => $post['userid']
            ];

            // Save image if exists
            if ($imageName) {
                $dataArray['logo'] = $imageName;
            }

            if (!empty($post['id'])) {

                // ✅ Update case
                $oldData = Brand::find($post['id']);

                // Delete old image if new uploaded
                if ($imageName && $oldData && $oldData->image) {
                    $oldPath = public_path('uploads/brands/' . $oldData->image);
                    if (File::exists($oldPath)) {
                        File::delete($oldPath);
                    }
                }

                $dataArray['updated_at'] = Carbon::now();
                $dataArray['updatedby'] = $post['userid'];

                if (!Brand::where('id', $post['id'])->update($dataArray)) {
                    throw new \Exception("Couldn't update Records");
                }
            } else {

                $dataArray['id'] = (string) Str::uuid();

                $dataArray['created_at'] = Carbon::now();

                if (!Brand::insert($dataArray)) {
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

            // Sanitize search inputs
            foreach ($get['columns'] as $key => $value) {
                $get['columns'][$key]['search']['value'] = trim(strtolower(htmlspecialchars($value['search']['value'], ENT_QUOTES)));
            }

            $orgid = $post['orgid'];

            // Build WHERE condition as string
            $cond = "status = 'Y' and orgid = '{$orgid}'"; // ✅ wrap UUID in quotes
            if (!empty($post['type']) && $post['type'] === "trashed") {
                $cond = "status = 'R' and orgid = '{$orgid}'";
            }

            if (!empty($get['columns'][1]['search']['value'])) {
                $search = $get['columns'][1]['search']['value'];
                $cond .= " and lower(name) like '%{$search}%'";
            }

            $limit = !empty($get["length"]) ? (int)$get['length'] : 15;
            $offset = !empty($get["start"]) ? (int)$get["start"] : 0;

            // Use selectRaw with fully built string
            $query = Brand::selectRaw("(SELECT count(*) FROM brands WHERE {$cond}) AS totalrecs, name, logo, description, id")
                ->whereRaw($cond);

            if ($limit > -1) {
                $result = $query->orderBy('id', 'desc')->offset($offset)->limit($limit)->get();
            } else {
                $result = $query->orderBy('id', 'desc')->get();
            }

            if ($result) {
                $ndata = $result;
                $ndata['totalrecs'] = @$result[0]->totalrecs ?? 0;
                $ndata['totalfilteredrecs'] = @$result[0]->totalrecs ?? 0;
            } else {
                $ndata = [];
            }

            return $ndata;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    //restore 
    public static function deletBrand($post)
    {
        try {
            $updateArray = [
                'status' => 'N',
                'updated_at' => Carbon::now(),
            ];
            if (!Brand::where(['id' => $post['id']])->update($updateArray)) {
                throw new Exception("Couldn't Delete Data. Please try again", 1);
            }
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function getBrand($post)
    {
        try {
            $result = DB::table('brands')->select('id', 'name')->where('orgid', $post['orgid'])->where('status', 1)->get();

            return $result;
        } catch (Exception $e) {
            throw $e;
        }
    }
}