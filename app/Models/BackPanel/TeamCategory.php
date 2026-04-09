<?php

namespace App\Models\BackPanel;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Exception;
use Illuminate\Support\Str;

class TeamCategory extends Model
{
    use HasFactory;

    //function to show relation with member
    public function teamMember()
    {
        return $this->hasMany(TeamMember::class);
    }

    //function to save team category
    public static function saveData($post)
    {
        try {
            $dataArray = [
                'team_category' => $post['team_category'],
                'order_number' => $post['order_number'],
                'slug' =>  Str::slug($post['team_category']) . '-' . time(),
            ];

            if (!empty($post['id'])) {
                $dataArray['updated_at'] = Carbon::now();
                if (!TeamCategory::where('id', $post['id'])->update($dataArray)) {
                    throw new Exception("Couldn't update Records", 1);
                }
            } else {
                $dataArray['created_at'] = Carbon::now();
                if (!TeamCategory::insert($dataArray)) {
                    throw new Exception("Couldn't Save Records", 1);
                }
            }
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    //function to list team category
    public static function list($post)
    {
        try {
            $get = $post;

            $sorting = !empty($get['order'][0]['dir']) ? $get['order'][0]['dir'] : 'asc';

            $orderby = " order_number " . $sorting . "";

            if (!empty($get['order'][0]['column']) && $get['order'][0]['column'] == 6) {
                $orderby = " order_number " . $sorting . "";
            }

            foreach ($get['columns'] as $key => $value) {
                $get['columns'][$key]['search']['value'] = trim(strtolower(htmlspecialchars($value['search']['value'], ENT_QUOTES)));
            }
            $cond = " status = 'Y'";

            if (!empty($post['type']) && $post['type'] === "trashed") {
                $cond = " status = 'R'";
            }

            if ($get['columns'][1]['search']['value'])
                $cond .= " and lower(team_category) like '%" . $get['columns'][1]['search']['value'] . "%'";

            $limit = 15;
            $offset = 0;
            if (!empty($get["length"]) && $get["length"]) {
                $limit = $get['length'];
                $offset = $get["start"];
            }

            $query = TeamCategory::with('teamMember')->selectRaw("(SELECT count(*) FROM team_categories WHERE {$cond} ) AS totalrecs, team_category,order_number, id as id")
                ->whereRaw($cond);

            if ($limit > -1) {
                $result = $query->orderByRaw($orderby)->offset($offset)->limit($limit)->get();
            } else {
                $result = $query->orderByRaw($orderby)->get();
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
    public static function restoreData($post)
    {
        try {
            $updateArray = [
                'status' => 'Y',
                'updated_at' => Carbon::now(),
            ];
            if (!TeamCategory::where(['id' => $post['id']])->update($updateArray)) {
                throw new Exception("Couldn't Restore Data. Please try again", 1);
            }
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
