<?php

namespace VideMe\Ffmpegconversion;

//use VideMe\Datacraft\model\PostgreSQL;
use VideMe\Datacraft\nad;
use VideMe\Ffmpegconversion\FileSteward;
use VideMe\Ffmpegconversion\PsqlFfmpeg;

class LogConversion extends \VideMe\Datacraft\log\log
{
    public function pgSetTask($pgSetTask)
    {
        //echo "\r\n<hr>setEvent setEvent<br>";
        //print_r($setEvent);
        $welcome = new NADFFMpeg();
        if (empty($pgSetTask['task_id'])) $pgSetTask['task_id'] = $welcome->trueRandom();
        //echo "\r\n<hr>pgSetTask task_id:<br>";
        //print_r($pgSetTask['task_id']);
        $pg = new PsqlFfmpeg();
        $trueTask = $pg->pgPaddingItems($pgSetTask); // TODO: dubble NOOO
        return $pg->pgAddData($pg->table_tasks, $trueTask);
    }
    public function pgGetTaskById($pgGetTaskById)
    {
        $pg = new PsqlFfmpeg();
        return $pg->pgOneDataByColumn([
            'table' => $pg->table_tasks,
            'find_column' => 'task_id',
            'find_value' => $pgGetTaskById['task_id']]);
    }
    public function taskChangeData($currentTask, $newData)
    {
        //echo "\n\rtaskChangeData newData\n\r";
        //print_r($newData);
        //return true;
        $pg = new PsqlFfmpeg();
        //$itemNew = array_merge($itemOld, $itemTemp);
        //$itemTrue = $pg->pgPaddingItems($itemNew);
        try {
            $res = $pg->pgUpdateDataArray($pg->table_tasks, $newData, ['task_id' => $currentTask['task_id']]);
            return $res;
        } catch (Exception $e) {
            $this->taskChangeStatus($currentTask, "error");
            return false;
        }
    }
    public function taskChangeStatus($currentTask, $newStatus)
    {
        //echo "\n\rtaskChangeStatus currentTask\n\r";
        //print_r($currentTask);
        //return true;
        $pg = new PsqlFfmpeg();
        $currentTask["task_status"] = $newStatus;
        $resUpdateDocument = $pg->pgUpdateData($pg->table_tasks,
            'task_status',
            $currentTask['task_status'],
            'task_id',
            $currentTask['task_id']);
        //echo "\n\rtaskChangeStatus resUpdateDocument\n\r";
        //print_r($resUpdateDocument);
    }

    public function pgGetLastTask() // TODO: why?
    {
        $pg = new PsqlFfmpeg();
        return $pg->pgOneDataByColumn([
            'table' => $pg->table_tasks,
            'find_column' => 'task_status',
            'find_value' => 'awaiting']);
    }
    public function pgSchedulerWork()
    {
        $welcome = new NADFFMpeg();
        $pg = new PsqlFfmpeg();
       // $s3 = new S3();
       // $sendmail = new sendmail();
        $fs = new FileSteward();
        $ffmpegConv = new FfmpegConv();

        //ini_set("memory_limit", "600M");
        ini_set("memory_limit", "300M");
        //ini_set('max_execution_time', 300); //300 seconds = 5 minutes
        ini_set('max_execution_time', 3600); //300 seconds = 5 minutes
        /* Get task */
        $lastTask = $this->pgGetLastTask();
        echo "\n\rpgSchedulerWork last task start\n";
        print_r($lastTask);
        //exit;
        /* Do work */

        if (!empty($lastTask)) {

            if ($lastTask["task_type"] == 'fileUploadVideo') { // v4
                $this->fileUploadVideo($lastTask);
            }
            /* ****************************************** */
            //if ($lastTask["task_type"] == 'fileUploadVideo') {
            if ($lastTask["task_type"] == 'fileUploadVideo_force_mp4') {
                $this->toFile(['service' => 'file', 'type' => '', 'text' => 'fileUploadVideo_force_mp4 start ' . $lastTask['task_item_id']]);
                $this->toFile(['service' => 'file', 'type' => '', 'text' => 'fileUploadVideo_force_mp4 taskChangeStatus task_item_id ' . $lastTask['task_item_id']]);

                $this->taskChangeStatus($lastTask, "taskChangeStatus"); // !!!
                $video_info = $ffmpegConv->getVideoInfo($welcome->nadtemp . $lastTask["file"]);

                $ratio = $video_info['width'] / $video_info['height'];
                $newHeight = 240;
                $newWidthRound = round($newHeight * $ratio); // TODO: https://www.php.net/manual/ru/function.round.php
                //$newWidthRound = round($newHeight * $ratio, 0, PHP_ROUND_HALF_EVEN);
                //$newWidth = $newHeight * $ratio;
                if ($newWidthRound % 2 == 1) {
                    // odd or even
                    $newWidthRound = $newWidthRound - 1;
                }

                $bandwidth = $ffmpegConv->sizeToBandwidth(['height' => $video_info['height']]);


                $param = ['RESOLUTION_X' => $video_info['width'],
                    'RESOLUTION_Y' => $video_info['height'],
                    'BANDWIDTH' => $bandwidth];
                //$path_parts = pathinfo($lastTask["file"]);

                //rename($welcome->nadtemp . $lastTask["file"], $welcome->nadtemp . $path_parts['filename'] . '-240.' . $path_parts['extension']);
                //$fileToHls = $fs->fileToHlsAny($welcome->nadtemp . $path_parts['filename'] . '-240.' . $path_parts['extension'], $param);
                //$ffmpegConv->fileToMP4($welcome->nadtemp . $lastTask["file"], $param);

                rename($welcome->nadtemp . $lastTask["file"], $welcome->nadtemp . $lastTask["file"] . '_source');
                //copy($welcome->nadtemp . $lastTask["file"], $welcome->nadtemp . $lastTask["file"] . '_source');

                //$ffmpegConv->fileToMP4_Only($welcome->nadtemp . $lastTask["file"]);
                $ffmpegConv->fileToMP4_Only($welcome->nadtemp . $lastTask["file"] . '_source');
                //rename($welcome->nadtemp . $path_parts['filename'] . '-240.' . $path_parts['extension'], $welcome->nadtemp . $lastTask["file"]);
                //$video_info_new = $ffmpegConv->getVideoInfo($welcome->nadtemp . $path_parts['filename'] . '-2400.ts');
                //$video_average_bit_rate = $ffmpegConv->getAverageBitRateTS($welcome->nadtemp . $path_parts['filename'] . '-240');
                //$video_info = $ffmpegConv->getVideoInfo($welcome->nadtemp . $lastTask["task_item_id"] . '-' . $video_info['height'] . '.mp4');
                //$video_info = $ffmpegConv->getVideoInfo($welcome->nadtemp . $lastTask["task_item_id"] . '_force.mp4');
                $video_info = $ffmpegConv->getVideoInfo($welcome->nadtemp . $lastTask["task_item_id"] . '.mp4');
                //$video_info = $ffmpegConv->getVideoInfo($welcome->nadtemp . $lastTask["task_item_id"] . '-240.' . $path_parts['extension']);
                //$video_info['video_duration'] = $ffmpegConv->getVideoDuration($welcome->nadtemp . $lastTask["task_item_id"] . '-' . $video_info['height'] . '.mp4');
                //$video_info['video_duration'] = $ffmpegConv->getVideoDuration($welcome->nadtemp . $lastTask["task_item_id"] . '_force.mp4');
                $video_info['video_duration'] = $ffmpegConv->getVideoDuration($welcome->nadtemp . $lastTask["task_item_id"] . '.mp4');
                //$video_info['video_duration'] = $ffmpegConv->getVideoDuration($welcome->nadtemp . $lastTask["task_item_id"] . '-240.' . $path_parts['extension']);


                echo "\n\rpgSchedulerWork fileUploadVideoMP4_force getVideoDuration\n\r";
                print_r($video_info);
                if ($video_info['video_duration'] > 0) {
                    //$lastTask['file_size_done'] = filesize($welcome->nadtemp . $lastTask["file"]);
                    //$lastTask['video_duration'] = $fileToHls['video_duration'];
                    //echo "\n\rpgSchedulerWork fileUploadVideo end\n";
                    //print_r($lastTask);
                    //$path_parts = pathinfo($lastTask["file"]);
                    /*$fs->compMultiM3U8(['item_id' => $path_parts['filename'],
                        'BANDWIDTH' => $bandwidth,
                        'RESOLUTION_X' => $newWidthRound,
                        'RESOLUTION_Y' => $newHeight]);
                    $fileToHls[] = $path_parts['filename'] . ".m3u8";*/

                    //$fileToS3 = $fs->fileToS3(json_encode($fileToHls));

                    /* Update item src ********************************   */
                    //$itemInfo= $pg->pgGetItemFullInfo($lastTask["task_item_id"]);
                    /*$itemTrue = $pg->pgOneDataByColumn([
                        'table' => $pg->table_items,
                        'find_column' => 'item_id',
                        'find_value' => $lastTask["task_item_id"]]);
                    //echo "\n\rpgSchedulerWork fileUploadVideoMP4_240 itemInfo\n\r";
                    //print_r($itemInfo);
                    //$itemTrue = $pg->pgPaddingItems($itemInfo);
                    $itemTrue['src'] = json_encode([0 => $lastTask["task_item_id"] . '-240.mp4']);
                    //$itemTrue['src'] = json_encode([0 => $lastTask["task_item_id"] . '-240.' . $path_parts['extension']]);
                    echo "\n\rpgSchedulerWork fileUploadVideoMP4_240 itemTrue\n\r";
                    print_r($itemTrue);
                    $pg->pgUpdateDataArray($pg->table_items, $itemTrue, ['item_id' => $lastTask["task_item_id"]]);*/

                    /* Change task ********************************   */
                    $this->toFile(['service' => 'file', 'type' => '', 'text' => 'fileUploadVideo_force_mp4 taskChangeData fileUploadVideo awaiting ' . $lastTask['task_item_id']]);

                    $this->taskChangeData($lastTask, [
                        "task_id" => $lastTask['task_id'],
                        //"task_type" => "fileUploadVideo_test",
                        "task_type" => "fileUploadVideo",
                        "task_status" => "awaiting",
                        //"file_size_start" => $file->size,
                        //"fileSizeDone" => "",
                        //=="access" => $lastTask['access'],
                        //"file" => $lastTask["task_item_id"] . '-' . $video_info['height'] . '.mp4',
                        //"file" => $lastTask["task_item_id"] . '_force.mp4',
                        "file" => $lastTask["task_item_id"] . '.mp4',
                        //"file_type" => $path_parts['extension'],
                        //=="task_item_id" => $lastTask['task_item_id'],
                        //'video_duration' => $fileToHls['video_duration'],
                        //$welcome->file => $file->name . $type; //<---,
                        //$welcome->file => $file->name . $this->get_file_type($file_path); //<---,
                        //=='title' => $lastTask['title'],
                        //=='content' => $lastTask['content'],
                        //=='type' => 'video',
                        //=='album_id' => $lastTask['album_id'],
                        //=='owner_id' => $lastTask['owner_id']//,
                        //'data_json' => json_encode([0 => $lastTask["task_item_id"] . '-240.mp4'])
                    ]);
                    /* Change task ********************************   */
                    //$this->taskChangeStatus($lastTask, "fileSendToS3");
                } else {
                    // Convert failure
                    echo "\npgSchedulerWork fileUploadVideo failure\n\r";
                    //$this->taskChangeStatus($lastTask, "error");
                    //exit();
                    $this->taskAddAttempt($lastTask);
                }
            }
            /* ****************************************** */
            if ($lastTask["task_type"] == 'fileUploadVideo240') {
                //$this->is_alone_work(); // TODO: why for?
                $this->toFile(['service' => 'file', 'type' => '', 'text' => 'fileUploadVideo240 start ' . $lastTask['task_item_id']]);
                $this->toFile(['service' => 'file', 'type' => '', 'text' => 'fileUploadVideo240 taskChangeStatus worked ' . $lastTask['task_item_id']]);
                $this->taskChangeStatus($lastTask, "worked"); // !!!
                $video_info = $ffmpegConv->getVideoInfo($welcome->nadtemp . $lastTask["file"]);

                $ratio = $video_info['width'] / $video_info['height'];
                $newHeight = 240;
                $newWidthRound = round($newHeight * $ratio);
                //$newWidthRound = round($newHeight * $ratio, 0, PHP_ROUND_HALF_EVEN);
                if ($newWidthRound % 2 == 1) {
                    // odd or even
                    $newWidthRound = $newWidthRound - 1;
                }
                //$newWidth = $newHeight * $ratio;

                $bandwidth = $ffmpegConv->sizeToBandwidth(['height' => $newHeight]);


                $param = ['RESOLUTION_X' => $newWidthRound,
                    'RESOLUTION_Y' => $newHeight,
                    'BANDWIDTH' => $bandwidth];
                $path_parts = pathinfo($lastTask["file"]);

                rename($welcome->nadtemp . $lastTask["file"], $welcome->nadtemp . $path_parts['filename'] . '-240.' . $path_parts['extension']);
                $fileToHls = $fs->fileToHlsAny($welcome->nadtemp . $path_parts['filename'] . '-240.' . $path_parts['extension'], $param);
                rename($welcome->nadtemp . $path_parts['filename'] . '-240.' . $path_parts['extension'], $welcome->nadtemp . $lastTask["file"]);
                /*if (!empty($fileToHls['error'])) {
                }*/
                //$video_info_new = $ffmpegConv->getVideoInfo($welcome->nadtemp . $path_parts['filename'] . '-2400.ts');
                //$video_average_bit_rate = $ffmpegConv->getAverageBitRateTS($welcome->nadtemp . $path_parts['filename'] . '-240');


                echo "\n\rpgSchedulerWork fileUploadVideo fileToHls\n\r";
                print_r($fileToHls);
                //if ($fileToHls['video_duration'] > 0) { // < --- webm NO duration
                if ($fileToHls[0] == $path_parts['filename'] . '-2400.ts') {
                    $this->toFile(['service' => 'file', 'type' => '', 'text' => 'fileUploadVideo240 2400.ts ' . $lastTask['task_item_id']]);

                    //$lastTask['file_size_done'] = filesize($welcome->nadtemp . $lastTask["file"]);
                    //$lastTask['video_duration'] = $fileToHls['video_duration'];
                    //echo "\n\rpgSchedulerWork fileUploadVideo end\n";
                    //print_r($lastTask);
                    //$path_parts = pathinfo($lastTask["file"]);
                    $fs->compMultiM3U8(['item_id' => $lastTask["task_item_id"],
                        'BANDWIDTH' => $bandwidth,
                        'RESOLUTION_X' => $newWidthRound,
                        'RESOLUTION_Y' => $newHeight]);
                    $fileToHls[] = $path_parts['filename'] . ".m3u8";

                    //$fileToS3 = $fs->fileToS3(json_encode($fileToHls));

                    /* Change task ********************************   */
                    $this->toFile(['service' => 'file', 'type' => '', 'text' => 'fileUploadVideo240 taskChangeData fileSendToS3Only ' . $lastTask['task_item_id']]);

                    $this->taskChangeData($lastTask, [
                        "task_id" => $lastTask['task_id'],
                        "task_type" => "fileSendToS3Only",
                        "task_status" => "awaiting",
                        //"file_size_start" => $file->size,
                        //"fileSizeDone" => "",
                        "access" => $lastTask['access'],
                        //"file" => $file->name,
                        //"file_type" => $path_parts['extension'],
                        "task_item_id" => $lastTask['task_item_id'],
                        'video_duration' => $fileToHls['video_duration'],
                        //$welcome->file => $file->name . $type; //<---,
                        //$welcome->file => $file->name . $this->get_file_type($file_path); //<---,
                        'title' => $lastTask['title'],
                        'content' => $lastTask['content'],
                        'type' => 'video',
                        'album_id' => $lastTask['album_id'],
                        'owner_id' => $lastTask['owner_id'],
                        'data_json' => json_encode($fileToHls)
                    ]);

                    //$this->taskChangeStatus($lastTask, "fileSendToS3");
                } else {
                    // Convert failure
                    echo "\n************************* pgSchedulerWork fileUploadVideo failure ------------- \n\r";
                    //$this->taskChangeStatus($lastTask, "error");
                    //exit();
                    $this->taskAddAttempt($lastTask);
                }
                /* Change task ********************************   */
                /* set new task ********************************
                convert to MP4 240*/
                $this->toFile(['service' => 'file', 'type' => '', 'text' => 'fileUploadVideo240 pgSetTask fileUploadVideoMP4_240 ' . $lastTask['task_item_id']]);

                $this->pgSetTask([
                    "task_type" => "fileUploadVideoMP4_240",
                    "task_status" => "awaiting",
                    //"file_size_start" => $file->size,
                    //"fileSizeDone" => "",
                    "access" => $lastTask['access'],
                    "file" => $lastTask['file'],
                    "file_type" => $lastTask['file_type'],
                    "task_item_id" => $lastTask['task_item_id'],
                    //$welcome->file => $file->name . $type; //<---,
                    //$welcome->file => $file->name . $this->get_file_type($file_path); //<---,
                    'title' => $lastTask['title'],
                    'content' => $lastTask['content'],
                    'type' => 'video',
                    'album_id' => $lastTask['album_id'],
                    'cover_upload' => $lastTask['cover_upload'],
                    'parent_id' => $lastTask['parent_id']
                    //'owner_id' => $lastTask['owner_id'],
                    //$welcome->videoDuration => ""
                ]);
            }
            /* ****************************************** */
            /* ****************************************** */
            if ($lastTask["task_type"] == 'fileUploadVideoMP4_240') {
                $this->toFile(['service' => 'file', 'type' => '', 'text' => 'fileUploadVideoMP4_240 start ' . $lastTask['task_item_id']]);
                $this->toFile(['service' => 'file', 'type' => '', 'text' => 'fileUploadVideoMP4_240 taskChangeStatus worked ' . $lastTask['task_item_id']]);

                $this->taskChangeStatus($lastTask, "worked"); // !!!
                $video_info = $ffmpegConv->getVideoInfo($welcome->nadtemp . $lastTask["file"]);

                $ratio = $video_info['width'] / $video_info['height'];
                $newHeight = 240;
                $newWidthRound = round($newHeight * $ratio);
                //$newWidthRound = round($newHeight * $ratio, 0, PHP_ROUND_HALF_EVEN);
                //$newWidth = $newHeight * $ratio;
                if ($newWidthRound % 2 == 1) {
                    // odd or even
                    $newWidthRound = $newWidthRound - 1;
                }

                $bandwidth = $ffmpegConv->sizeToBandwidth(['height' => $newHeight]);


                $param = ['RESOLUTION_X' => $newWidthRound,
                    'RESOLUTION_Y' => $newHeight,
                    'BANDWIDTH' => $bandwidth];
                //$path_parts = pathinfo($lastTask["file"]);

                //rename($welcome->nadtemp . $lastTask["file"], $welcome->nadtemp . $path_parts['filename'] . '-240.' . $path_parts['extension']);
                //$fileToHls = $fs->fileToHlsAny($welcome->nadtemp . $path_parts['filename'] . '-240.' . $path_parts['extension'], $param);
                $ffmpegConv->fileToMP4($welcome->nadtemp . $lastTask["file"], $param);
                //rename($welcome->nadtemp . $path_parts['filename'] . '-240.' . $path_parts['extension'], $welcome->nadtemp . $lastTask["file"]);
                //$video_info_new = $ffmpegConv->getVideoInfo($welcome->nadtemp . $path_parts['filename'] . '-2400.ts');
                //$video_average_bit_rate = $ffmpegConv->getAverageBitRateTS($welcome->nadtemp . $path_parts['filename'] . '-240');
                $video_info = $ffmpegConv->getVideoInfo($welcome->nadtemp . $lastTask["task_item_id"] . '-240.mp4');
                //$video_info = $ffmpegConv->getVideoInfo($welcome->nadtemp . $lastTask["task_item_id"] . '_force-240.mp4'); // <--------------------------------------------------------
                //$video_info = $ffmpegConv->getVideoInfo($welcome->nadtemp . $lastTask["task_item_id"] . '-240.' . $path_parts['extension']);
                $video_info['video_duration'] = $ffmpegConv->getVideoDuration($welcome->nadtemp . $lastTask["task_item_id"] . '-240.mp4');
                //$video_info['video_duration'] = $ffmpegConv->getVideoDuration($welcome->nadtemp . $lastTask["task_item_id"] . '_force-240.mp4'); // <-------------------------------------------
                //$video_info['video_duration'] = $ffmpegConv->getVideoDuration($welcome->nadtemp . $lastTask["task_item_id"] . '-240.' . $path_parts['extension']);


                echo "\n\rpgSchedulerWork fileUploadVideoMP4_240 getVideoDuration\n\r";
                print_r($video_info);
                if ($video_info['video_duration'] > 0) {
                    //$lastTask['file_size_done'] = filesize($welcome->nadtemp . $lastTask["file"]);
                    //$lastTask['video_duration'] = $fileToHls['video_duration'];
                    //echo "\n\rpgSchedulerWork fileUploadVideo end\n";
                    //print_r($lastTask);
                    //$path_parts = pathinfo($lastTask["file"]);
                    /*$fs->compMultiM3U8(['item_id' => $path_parts['filename'],
                        'BANDWIDTH' => $bandwidth,
                        'RESOLUTION_X' => $newWidthRound,
                        'RESOLUTION_Y' => $newHeight]);
                    $fileToHls[] = $path_parts['filename'] . ".m3u8";*/

                    //$fileToS3 = $fs->fileToS3(json_encode($fileToHls));

                    /* Update item src ********************************   */
                    //$itemInfo= $pg->pgGetItemFullInfo($lastTask["task_item_id"]);
                    $itemTrue = $pg->pgOneDataByColumn([
                        'table' => $pg->table_items,
                        'find_column' => 'item_id',
                        'find_value' => $lastTask["task_item_id"]]);
                    //echo "\n\rpgSchedulerWork fileUploadVideoMP4_240 itemInfo\n\r";
                    //print_r($itemInfo);
                    //$itemTrue = $pg->pgPaddingItems($itemInfo);
                    $itemTrue['src'] = json_encode([0 => $lastTask["task_item_id"] . '-240.mp4']);
                    //$itemTrue['src'] = json_encode([0 => $lastTask["task_item_id"] . '-240.' . $path_parts['extension']]);
                    echo "\n\rpgSchedulerWork fileUploadVideoMP4_240 itemTrue\n\r";
                    print_r($itemTrue);
                    $this->toFile(['service' => 'file', 'type' => '', 'text' => 'fileUploadVideoMP4_240 pgUpdateDataArray ' . $lastTask['task_item_id']]);

                    $pg->pgUpdateDataArray($pg->table_items, $itemTrue, ['item_id' => $lastTask["task_item_id"]]);

                    /* Change task ********************************   */
                    $this->toFile(['service' => 'file', 'type' => '', 'text' => 'fileUploadVideoMP4_240 taskChangeData fileSendToS3Only awaiting ' . $lastTask['task_item_id']]);

                    $this->taskChangeData($lastTask, [
                        "task_id" => $lastTask['task_id'],
                        "task_type" => "fileSendToS3Only",
                        "task_status" => "awaiting",
                        //"file_size_start" => $file->size,
                        //"fileSizeDone" => "",
                        "access" => $lastTask['access'],
                        //"file" => $file->name,
                        //"file_type" => $path_parts['extension'],
                        "task_item_id" => $lastTask['task_item_id'],
                        //'video_duration' => $fileToHls['video_duration'],
                        //$welcome->file => $file->name . $type; //<---,
                        //$welcome->file => $file->name . $this->get_file_type($file_path); //<---,
                        'title' => $lastTask['title'],
                        'content' => $lastTask['content'],
                        'type' => 'video',
                        'album_id' => $lastTask['album_id'],
                        'owner_id' => $lastTask['owner_id'],
                        'data_json' => json_encode([0 => $lastTask["task_item_id"] . '-240.mp4'])
                    ]);
                    /* Change task ********************************   */
                    //$this->taskChangeStatus($lastTask, "fileSendToS3");
                    /* set new task ******************************** convert to fileCreate_pre_video_image_sprite*/
//                    $this->toFile(['service' => 'file', 'type' => '', 'text' => 'fileUploadVideoMP4_240 pgSetTask fileCreate_pre_video_image_sprite ' . $lastTask['task_item_id']]);

/*                    $this->pgSetTask([
                        "task_type" => "fileCreate_pre_video_image_sprite",
                        "task_status" => "awaiting",
                        //"file_size_start" => $file->size,
                        //"fileSizeDone" => "",
                        //"access" => $lastTask['access'],
                        "file" => $lastTask['file'],
                        //"file_type" => $lastTask['file_type'],
                        "task_item_id" => $lastTask['task_item_id'],
                        "cover_upload" => $lastTask['cover_upload'],
                        'parent_id' => $lastTask['parent_id']
                        //$welcome->file => $file->name . $type; //<---,
                        //$welcome->file => $file->name . $this->get_file_type($file_path); //<---,
                        //'title' => $lastTask['title'],
                        //'content' => $lastTask['content'],
                        //'type' => 'video',
                        //'album_id' => $lastTask['album_id'],
                        //'owner_id' => $lastTask['owner_id'],
                        //$welcome->videoDuration => ""
                    ]);*/

                    /* ****************************************** */
                } else {
                    // Convert failure
                    echo "\npgSchedulerWork fileUploadVideo failure\n\r";
                    //$this->taskChangeStatus($lastTask, "error");
                    //exit();
                    $this->taskAddAttempt($lastTask);
                }
            }
            /* ****************************************** */

            /* ****************************************** */
            if ($lastTask["task_type"] == 'fileSendToS3') {
                $this->toFile(['service' => 'file', 'type' => '', 'text' => 'fileSendToS3 start ' . $lastTask['task_item_id']]);

                echo "\n\rpgSchedulerWork fileSendToS3 start\n\r";
                print_r($lastTask);
                $this->toFile(['service' => 'file', 'type' => '', 'text' => 'fileSendToS3 taskChangeStatus worked ' . $lastTask['task_item_id']]);

                $this->taskChangeStatus($lastTask, "worked"); // !!!
                $fileToS3 = $fs->fileToS3($lastTask);
                echo "\n\rpgSchedulerWork fileSendToS3 fileToS3\n\r";
                print_r($fileToS3);
                if ($fileToS3) {

                    // Если файл видео попал в AWS
                    $dataItems['item_id'] = $lastTask['task_item_id'];
                    $dataItems['owner_id'] = $lastTask['owner_id'];
                    $dataItems['access'] = $lastTask['access'];
                    $dataItems['type'] = $lastTask['type'];
                    $dataItems['title'] = $lastTask['title'];
                    $dataItems['content'] = $lastTask['content'];
                    //$dataItems['category'] = $lastTask['category'];
                    //$dataItems['sign_id'] = $lastTask['sign_id'];
                    $dataItems['video_duration'] = $lastTask['video_duration'];
                    $dataItems['width'] = $lastTask['width'];
                    $dataItems['height'] = $lastTask['height'];
                    //$dataItems['status'] = $lastTask['status'];
                    $dataItems['cover'] = $lastTask['cover'];
                    //$dataItems['body'] = $lastTask['body'];
                    $dataItems['tags'] = $lastTask['tags'];
                    //$dataItems['created_at'] = $lastTask['created_at'];
                    echo "\n\rpgSchedulerWork dataItems pre addToItems\n\r";
                    print_r($dataItems);
                    $this->toFile(['service' => 'file', 'type' => '', 'text' => 'fileSendToS3 addToItems ' . $lastTask['task_item_id']]);

                    $resFileAdd = $welcome->addToItems($dataItems);
                    //$resFileAdd = $welcome->addToItems($lastTask);
                    // TODO: Поставить проверку выпонения
                    echo "\n\rpgSchedulerWork resFileAdd\n\r";
                    print_r($resFileAdd);
                    $this->taskChangeStatus($lastTask, "success");

                } else {
                    // Convert failure
                    echo "\npgSchedulerWork fileSendToS3 failure\n\r";
                    //$this->taskChangeStatus($lastTask, "error");
                    //exit();
                    $this->taskAddAttempt($lastTask);
                }
            }
            /* ****************************************** */
            if ($lastTask["task_type"] == 'fileSendToS3Only') {
                $this->toFile(['service' => 'file', 'type' => '', 'text' => 'fileSendToS3Only start ' . $lastTask['task_item_id']]);

                echo "\n\rpgSchedulerWork fileSendToS3Only start\n\r";
                print_r($lastTask);
                $this->toFile(['service' => 'file', 'type' => '', 'text' => 'fileSendToS3Only taskChangeStatus worked ' . $lastTask['task_item_id']]);

                $this->taskChangeStatus($lastTask, "worked"); // !!!
                $fileToS3 = $fs->fileToS3NoJPG($lastTask);
                if ($fileToS3) {
                    $this->toFile(['service' => 'file', 'type' => '', 'text' => 'fileSendToS3Only taskChangeStatus success ' . $lastTask['task_item_id']]);

                    $this->taskChangeStatus($lastTask, "success");
                    echo "\n\rpgSchedulerWork fileSendToS3Only fileToS3\n\r";
                    print_r($fileToS3);
                    $fs->fileRemoveNoJPG($lastTask); // TODO: remove

                } else {
                    // Convert failure
                    echo "\npgSchedulerWork fileSendToS3Only failure\n\r";
                    //$this->taskChangeStatus($lastTask, "error");
                    //exit();
                    $this->taskAddAttempt($lastTask);
                }
            }
            /* ****************************************** */
            if ($lastTask["task_type"] == 'delete_old_file') {
                $this->toFile(['service' => 'file', 'type' => '', 'text' => 'delete_old_file start ' . $lastTask['task_item_id']]);

                echo "\n\rpgSchedulerWork delete_old_file\n\r";
                print_r($lastTask);
                $this->toFile(['service' => 'file', 'type' => '', 'text' => 'delete_old_file taskChangeStatus worked ' . $lastTask['task_item_id']]);

                $this->taskChangeStatus($lastTask, "worked"); // !!!
                $fs->fileRemoveFromRedisAddArray($lastTask);
                //if ($fileToS3_pre) {
                $this->toFile(['service' => 'file', 'type' => '', 'text' => 'delete_old_file taskChangeStatus success ' . $lastTask['task_item_id']]);

                $this->taskChangeStatus($lastTask, "success");
                /* Change task ********************************   */
                /*} else {
                    // Convert failure
                    echo "\npgSchedulerWork delete_old_file failure\n\r";
                    //$this->taskChangeStatus($lastTask, "error");
                    //exit();
                    $this->taskAddAttempt($lastTask);
                }*/
            }
            /* ****************************************** */
        } else {
            echo 'empty task';
        }
    }
    public function fileUploadVideo($lastTask)
    {
        $welcome = new NADFFMpeg();
        //$pg = new PsqlFfmpeg();
        //$s3 = new S3();
        //$sendmail = new sendmail();
        $fs = new FileSteward();
        $ffmpegConv = new FfmpegConv();
        $this->is_alone_work();
        $this->toFile(['service' => 'file', 'type' => '', 'text' => 'fileUploadVideo start ' . $lastTask["file"]]);
        //if ($lastTask["task_type"] == 'fileUploadVideo_test') { // v4
        echo "\n\rlog fileUploadVideo\n\r";
        echo "\n\rpgSchedulerWork fileUploadVideo start\n\r";
        print_r($lastTask);

//error_reporting(0); // Turn off error reporting
        error_reporting(E_ALL ^ E_DEPRECATED); // Report all errors

        $this->taskChangeStatus($lastTask, "worked"); // !!!
        $video_info = $ffmpegConv->getVideoInfo($welcome->getNadtemp() . $lastTask["file"]);
        if (empty($video_info['height'])) {
            //$this->taskChangeStatus($lastTask, "error");
            //exit('fileUploadVideo error');
            $this->taskAddAttempt($lastTask);
        }
        if (empty($video_info['video_bitrate'])) { //fileUploadVideo_force_mp4
            $this->toFile(['service' => 'file', 'type' => '', 'text' => 'fileUploadVideo taskChangeData fileUploadVideo_force_mp4 ' . $lastTask["file"]]);
            $this->taskChangeData($lastTask, [ // TODO: why here?
                //"task_id" => $lastTask['task_id'],
                "task_type" => "fileUploadVideo_force_mp4",
                "task_status" => "awaiting",
                //"file_size_start" => $file->size,
                //"fileSizeDone" => "",
                //"access" => $lastTask['access'],
                //"file" => $file->name,
                //"file_type" => $path_parts['extension'],
                //"task_item_id" => $lastTask['task_item_id'],
                //'video_duration' => $fileToHls['video_duration'],
                //'width' => $video_info['width'],
                //'height' => $video_info['height'],
                //$welcome->file => $file->name . $type; //<---,
                //$welcome->file => $file->name . $this->get_file_type($file_path); //<---,
                //'title' => $lastTask['title'],
                //'content' => $lastTask['content'],
                //'type' => 'video',
                //'album_id' => $lastTask['album_id'],
                //'owner_id' => $lastTask['owner_id'],
                //'converted' => '1',
                //'data_json' => json_encode($fileToHls)
            ]);
            echo "\n\r======================================================\n\r";
            echo "\n\rfile not found: \n\r";
            echo $welcome->getNadtemp() . $lastTask["file"];
            /*$this->toFile(['service' => 'fileTo_pre_video_image_sprite', 'type' => 'error', 'text' => 'fileUploadVideo no video_bitrate: ' . $welcome->nadtemp . $lastTask["file"]]);
            $sendmail->SendStaffAlert(['message' => "file not found: " . $welcome->nadtemp . $lastTask["file"]]);*/
            echo "\n\r======================================================\n\r";
            exit('fileUploadVideo no video_bitrate');

        }
        if ($video_info['height'] > 239) { // TODO: why? WHY?
            echo "\n\rpgSchedulerWork video_size height > 240\n\r";
            $path_parts = pathinfo($lastTask["file"]);
            rename($welcome->getNadtemp() . $lastTask["file"], $welcome->getNadtemp() . $path_parts['filename'] . '-' . $video_info['height'] . '.' . $path_parts['extension']);
            //$fileToHls = $fs->fileToHls($welcome->nadtemp . $lastTask["file"]);
            $fileToHls = $fs->fileToHls($welcome->getNadtemp() . $path_parts['filename'] . '-' . $video_info['height'] . '.' . $path_parts['extension'], $lastTask);
            $fileToHls[] = $path_parts['filename'] . ".m3u8"; // <---------------------------------------- important

            rename($welcome->getNadtemp() . $path_parts['filename'] . '-' . $video_info['height'] . '.' . $path_parts['extension'], $welcome->getNadtemp() . $lastTask["file"]);
            echo "\n\rpgSchedulerWork fileUploadVideo fileToHls\n\r";
            print_r($fileToHls);
            //if ($fileToHls['video_duration'] > 0) {
            //echo "\n\rpgSchedulerWork array_key_exists\n\r";
            //echo $path_parts['filename'] . '-' . $video_info['height'] . '0.ts';
            //if (array_key_exists($path_parts['filename'] . '-' . $video_info['height'] . '0.ts', $fileToHls)) {
            if ($fileToHls[0] == $path_parts['filename'] . '-' . $video_info['height'] . '0.ts') {
                //$lastTask['file_size_done'] = filesize($welcome->nadtemp . $lastTask["file"]);
                //$lastTask['video_duration'] = $fileToHls['video_duration'];
                //echo "\n\rpgSchedulerWork fileUploadVideo end\n";
                //print_r($lastTask);
                // rename jpg
                rename($welcome->getNadtemp() . $path_parts['filename'] . '-' . $video_info['height'] . '.jpg', $welcome->getNadtemp() . $path_parts['filename'] . '.jpg');

                $path_parts = pathinfo($lastTask["file"]);
                $fs->compMultiM3U8Start(['item_id' => $path_parts['filename']]);

                $fs->compMultiM3U8(['item_id' => $lastTask["task_item_id"],
                    'BANDWIDTH' => $video_info['video_bitrate'],
                    'RESOLUTION_X' => $video_info['width'],
                    'RESOLUTION_Y' => $video_info['height']]);

                /* change task ********************************
                    converted true */
                $this->toFile(['service' => 'file', 'type' => '', 'text' => 'fileUploadVideo taskChangeData add video_duration ' . $fileToHls['video_duration'] . ' ', $lastTask['task_item_id']]);

                $this->taskChangeData($lastTask, [ // TODO: why here?
                    //"task_id" => $lastTask['task_id'],
                    //"task_type" => "fileSendToS3",
                    //"task_status" => "awaiting",
                    //"file_size_start" => $file->size,
                    //"fileSizeDone" => "",
                    //"access" => $lastTask['access'],
                    //"file" => $file->name,
                    //"file_type" => $path_parts['extension'],
                    //"task_item_id" => $lastTask['task_item_id'],
                    'video_duration' => $fileToHls['video_duration'],
                    'width' => $video_info['width'],
                    'height' => $video_info['height'],
                    //$welcome->file => $file->name . $type; //<---,
                    //$welcome->file => $file->name . $this->get_file_type($file_path); //<---,
                    //'title' => $lastTask['title'],
                    //'content' => $lastTask['content'],
                    //'type' => 'video',
                    //'album_id' => $lastTask['album_id'],
                    //'owner_id' => $lastTask['owner_id'],
                    'converted' => '1',
                    'data_json' => json_encode($fileToHls)
                ]);
                /* Change task ********************************   */

                $currentTask = $this->pgGetTaskById($lastTask);
                echo "\n\rpgSchedulerWork fileUploadVideo currentTask\n\r";
                print_r($currentTask);
                if (!empty($currentTask['status']) and ($currentTask['status'] == 'published' or $currentTask['status'] == 'draft')) {
                    $this->toFile(['service' => 'file', 'type' => '', 'text' => 'fileUploadVideo status ' . $currentTask['status']]);
                    /* change task ********************************
                    upload to aws */
                    $this->toFile(['service' => 'file', 'type' => '', 'text' => 'fileUploadVideo taskChangeData fileSendToS3 ' . $lastTask['task_item_id']]);

                    $this->taskChangeData($lastTask, [
                        "task_id" => $lastTask['task_id'],
                        "task_type" => "fileSendToS3",
                        "task_status" => "awaiting",
                        //"file_size_start" => $file->size,
                        //"fileSizeDone" => "",
                        "access" => $currentTask['access'],
                        //"file" => $file->name,
                        //"file_type" => $path_parts['extension'],
                        "task_item_id" => $lastTask['task_item_id'],
                        'video_duration' => $fileToHls['video_duration'],
                        //$welcome->file => $file->name . $type; //<---,
                        //$welcome->file => $file->name . $this->get_file_type($file_path); //<---,
                        'title' => $currentTask['title'],
                        'content' => $currentTask['content'],
                        'type' => 'video',
                        'album_id' => $currentTask['album_id'],
                        'owner_id' => $lastTask['owner_id'],
                        'data_json' => json_encode($fileToHls)
                    ]);
                    /* Change task ********************************   */
                }
                /* WEBM error ******************************************************************************/
                //if (!empty($currentTask['file_type']) and $currentTask['file_type'] == 'webm') {
                /* change task ********************************
                upload to aws and create items posts*/
                /*$this->taskChangeData($lastTask, [
                    "task_id" => $lastTask['task_id'],
                    "task_type" => "fileSendToS3",
                    "task_status" => "awaiting",
                    //"file_size_start" => $file->size,
                    //"fileSizeDone" => "",
                    "access" => $currentTask['access'],
                    //"file" => $file->name,
                    //"file_type" => $path_parts['extension'],
                    "task_item_id" => $lastTask['task_item_id'],
                    'video_duration' => $fileToHls['video_duration'],
                    //$welcome->file => $file->name . $type; //<---,
                    //$welcome->file => $file->name . $this->get_file_type($file_path); //<---,
                    'title' => $currentTask['title'],
                    'content' => $currentTask['content'],
                    'type' => 'video',
                    'album_id' => $currentTask['album_id'],
                    'owner_id' => $lastTask['owner_id'],
                    'data_json' => json_encode($fileToHls)
                ]);*/
                //}
                /* WEBM error ******************************************************************************/

                /* set new task ********************************
                convert to 240*/
                $this->toFile(['service' => 'file', 'type' => '', 'text' => 'fileUploadVideo pgSetTask fileUploadVideo240 ' . $lastTask['task_item_id']]);

                $this->pgSetTask([
                    "task_type" => "fileUploadVideo240",
                    "task_status" => "awaiting",
                    //"file_size_start" => $file->size,
                    //"fileSizeDone" => "",
                    "access" => $lastTask['access'],
                    "file" => $lastTask['file'],
                    "file_type" => $lastTask['file_type'],
                    "task_item_id" => $lastTask['task_item_id'],
                    //$welcome->file => $file->name . $type; //<---,
                    //$welcome->file => $file->name . $this->get_file_type($file_path); //<---,
                    'title' => $lastTask['title'],
                    'content' => $lastTask['content'],
                    'type' => 'video',
                    'album_id' => $lastTask['album_id'],
                    'cover_upload' => $lastTask['cover_upload'],
                    'parent_id' => $lastTask['task_id']
                    //'owner_id' => $lastTask['owner_id'],
                    //$welcome->videoDuration => ""
                ]);
                //$this->taskChangeStatus($lastTask, "fileSendToS3");
            } else {
                // Convert failure
                echo "\npgSchedulerWork fileUploadVideo failure\n\r";
                //$this->taskChangeStatus($lastTask, "error");
                //exit();
                $this->taskAddAttempt($lastTask);
            }
        } else {
            $this->toFile(['service' => 'file', 'type' => '', 'text' => 'fileUploadVideo error height < 239!!! ' . $lastTask["file"]]);
        }
    }
    public
    function is_alone_work()
    {
        $resTasksWorked = $this->pgGetTasksWorked_fileUploadVideo();
        if (!empty($resTasksWorked)) exit (print_r("NOT empty resTasksWorked fileUploadVideo" . $resTasksWorked));
    }
    public
    function taskAddAttempt($lastTask)
    {
        //echo "\n\rtaskChangeStatus currentTask\n\r";
        //print_r($currentTask);
        if (!$lastTask["attempt"] or $lastTask["attempt"] = '') $lastTask["attempt"] = 1;

        if ($lastTask["attempt"] < 4) {
            $pg = new PsqlFfmpeg();
            $lastTask["attempt"] = intval($lastTask["attempt"]) + 1;
            $pg->pgUpdateData($pg->table_tasks,
                'attempt',
                $lastTask["attempt"],
                'task_id',
                $lastTask['task_id']);
            $this->toFile(['service' => 'file', 'type' => '', 'text' => 'function taskAddAttempt add attempt ' . $lastTask["attempt"] . ' ' . $lastTask["file"]]);
            return true;
        } else {
            $this->toFile(['service' => 'file', 'type' => '', 'text' => 'taskAddAttempt error attempt ' . $lastTask["attempt"] . ' ' . $lastTask["file"]]);
            $this->taskChangeStatus($lastTask, "error");
            exit($lastTask["task_type"]);
        }
    }
    public function pgGetTasksWorked_fileUploadVideo()
    {
        $pg = new PsqlFfmpeg();
        return $pg->pgGetTasksWorked_fileUploadVideo();
    }

    public function fileToHls($fileToHls, $lastTask)
    {
        $this->log->toFile(['service' => 'file', 'type' => '', 'text' => 'fileToHls start ' . $lastTask["file"]]);

        echo "\n\rFileSteward fileToHls\n";
        print_r($fileToHls);
        //exit;
        $this->ffmpegConv->fileToHls($fileToHls, $lastTask);
        $u = $this->welcome->get_m3u8_video_segment($fileToHls);
        $path_parts = pathinfo($fileToHls);
        $u[] = $path_parts['filename'] . ".m3u8";
        $u['video_duration'] = $this->ffmpegConv->getVideoDuration($fileToHls);
        echo "\n\rFileSteward u\n";
        print_r($u);
        // TODO: add check
        $this->log->toFile(['service' => 'file', 'type' => '', 'text' => 'fileToHls return ' . $lastTask["file"]]);
        return $u;
    }
    public function pgGetMyTask($pgGetMyTask)
    {
        if (!empty($pgGetMyTask['user_id'])) {
            $pg = new PsqlFfmpeg();
            /*return $pg->pgDataByColumn([
                'table' => $pg->table_tasks,
                'find_column' => 'owner_id',
                'find_value' => $pgGetMyTask['user_id']]);*/
            return $pg->pgGetMyTasks($pgGetMyTask);
            /*$task = $pg->pgGetMyTasks($pgGetMyTask);
            $task['percent'] = file_get_contents($task['task_item_id'] . '.txt');
            return $task;*/
        } else {
            //header('Location: https://vide.me/VictorLustig.html');
            return false;
        }
    }
}