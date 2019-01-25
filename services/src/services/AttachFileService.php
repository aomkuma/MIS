<?php

namespace App\Service;

use App\Model\AttachFile;
use App\Model\DataRows;
use App\Model\DataSheets;
use Illuminate\Database\Capsule\Manager as DB;

class AttachFileService {

    public static function searchAttachFile($keyword) {
        return AttachFile::where('file_name', 'LIKE', DB::raw("'%" . $keyword . "%'"))
                        ->orWhere('display_name', 'LIKE', DB::raw("'%" . $keyword . "%'"))
                        ->get();
    }

    public static function getAttachFiles($parent_id, $page_type, $condition = []) {
        return AttachFile::where('parent_id', $parent_id)
                        ->where('page_type', $page_type)
                        ->where(function($query) use ($condition) {
                            if (!empty($condition['keyword'])) {
                                $query->where('file_name', 'LIKE', DB::raw("'%" . $condition['keyword'] . "%'"));
                                $query->orWhere('file_code', 'LIKE', DB::raw("'%" . $condition['keyword'] . "%'"));
                                $query->orWhere('display_name', 'LIKE', DB::raw("'%" . $condition['keyword'] . "%'"));
                            }
                        })
                        ->orderBy('order_no', 'ASC')
                        ->get()->toArray();
    }

    public static function getAttachFilesWithLanguage($parent_id, $page_type, $language, $condition = []) {
        return AttachFile::where('parent_id', $parent_id)
                        ->where('page_type', $page_type)
                        ->where(function($query) use ($condition, $language) {
                            if (!empty($condition['keyword'])) {
                                $query->where('file_name', 'LIKE', DB::raw("'%" . $condition['keyword'] . "%'"));
                                $query->orWhere('file_code', 'LIKE', DB::raw("'%" . $condition['keyword'] . "%'"));
                            }
                            if (!empty($language)) {
                                $query->where('file_language', $language);
                            }
                        })
                        ->orderBy('order_no', 'ASC')
                        ->get()->toArray();
    }

    public static function addAttachFiles($AttachFile) {

        $model = new AttachFile;

        // $model->fill($AttachFile);
        $model->menu_id = $AttachFile['menu_id'];
        $model->display_name = $AttachFile['display_name'];
        $model->parent_id = $AttachFile['parent_id'];
        $model->page_type = $AttachFile['page_type'];
        $model->file_language = $AttachFile['file_language'];
        $model->file_name = $AttachFile['file_name'];
        $model->file_code = $AttachFile['file_code'];
        $model->file_path = $AttachFile['file_path'];
        $model->content_type = $AttachFile['content_type'];
        $model->file_size = $AttachFile['file_size'];
        $model->order_no = $AttachFile['order_no'];
        $model->save();
    }

    public static function updateAttachFiles($AttachFile, $data) {
        $model = AttachFile::where('year', $data['Year'])
                ->where('month', $data['Month'])
                ->first();
        if (empty($model)) {
            $model = new AttachFile;
        }
        // $model->fill($AttachFile);
//            $model->menu_id = $AttachFile['menu_id'];
//            $model->display_name = $AttachFile['display_name'];
//            $model->parent_id = $AttachFile['parent_id'];
//            $model->page_type = $AttachFile['page_type'];
//            $model->file_language = $AttachFile['file_language'];
        $model->file_name = $AttachFile['file_name'];
//            $model->file_code = $AttachFile['file_code'];
        $model->file_path = $AttachFile['file_path'];
//            $model->content_type = $AttachFile['content_type'];
//            $model->file_size = $AttachFile['file_size'];
//            $model->order_no = $AttachFile['order_no'];
        $model->year = $data['Year'];
        $model->month = $data['Month'];
        $model->date = $data['date'];
        $model->save();
    }

    public static function removeAttachFile($id) {
        return AttachFile::find($id)->delete();
    }

    public static function getList() {
        return AttachFile::orderBy("modify", 'ASC')
                        ->get();
    }

    public static function saverow($sheetid, $data) {
        $row = new DataRows();

        $row->sheet_id = $sheetid;
        $row->positiontype = $data['positiontype'];
        $row->department = $data['department'];
        $row->director = $data['director'];
        $row->lv1 = $data['lv1'];
        $row->lv2 = $data['lv2'];
        $row->lv3 = $data['lv3'];
        $row->lv4 = $data['lv4'];
        $row->lv5 = $data['lv5'];
        $row->lv6 = $data['lv6'];
        $row->lv7 = $data['lv7'];
        $row->lv8 = $data['lv8'];
        $row->lv9 = $data['lv9'];
        $row->lv10 = $data['lv10'];
        $row->summary = $data['summary'];
        $row->save();
    }

    public static function savesheet($attid, $data) {
        $sheet = new DataSheets();
        $sheet->name = $data['name'];
        $sheet->attach_id = $sheetid;
        $sheet->save();
    }

}
