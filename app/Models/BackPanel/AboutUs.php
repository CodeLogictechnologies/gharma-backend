<?php

namespace App\Models\BackPanel;

use App\Models\Common;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutUs extends Model
{
    use HasFactory;

    public static function updatedata($post)
    {
        try {
            $updateArray = [
                'aboutus_title' => $post['aboutus_title'],
                'introduction' => $post['introduction'],
                'mission' => $post['mission'],
                'vision' => $post['vision'],
                'goals' => $post['goals'],
            ];

            if (!empty($post['croppedImgIntroduction'])) {
                $fileName =  Common::uploadCroppedImage('aboutus', $post['croppedImgIntroduction']);
                if (!$fileName) {
                    return false;
                }
                $updateArray['img_introduction'] = $fileName;
            } else {
                if (!empty($post['img_introduction'])) {
                    $fileName = Common::uploadFile('aboutus', $post['img_introduction']);
                    if (!$fileName) {
                        return false;
                    }
                    $updateArray['img_introduction'] = $fileName;
                }
            }

            if (!empty($post['img_mission'])) {
                $fileName = Common::uploadFile('aboutus', $post['img_mission']);
                if (!$fileName) {
                    return false;
                }
                $updateArray['img_mission'] = $fileName;
            }

            if (!empty($post['img_founder'])) {
                $fileName = Common::uploadFile('aboutus', $post['img_founder']);
                if (!$fileName) {
                    return false;
                }
                $updateArray['img_founder'] = $fileName;
            }

            $updateArray['updated_at'] = Carbon::now();

            if (!AboutUs::where('id', 1)->update($updateArray)) {
                throw new Exception("Couldn't Save Records", 1);
            }

            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
