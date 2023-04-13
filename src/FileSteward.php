<?php
/**
 * Created by PhpStorm.
 * User: sergey
 * Date: 13.03.18
 * Time: 22:20
 */

namespace VideMe\Ffmpegconversion;
//use FfmpegConv;
use log;
//use nad;
//use S3;
//use sendmail;

use VideMe\Datacraft\nad;
//use VideMe\Ffmpegconversion\LogConversion;
use VideMe\Ffmpegconversion\LogConversion;
use VideMe\Ffmpegconversion\FfmpegConv;
use VideMe\Ffmpegconversion\S3Ffmpeg;


//error_reporting(0); // Turn off error reporting
//error_reporting(E_ALL ^ E_DEPRECATED); // Report all errors

class FileSteward
{
    public function __construct()
    {
        $this->welcome = new NADFFMpeg();
        $this->log = new LogConversion();
        $this->ffmpegConv = new FfmpegConv();
        $this->s3 = new S3Ffmpeg();
        //$this->sendmail = new sendmail();
    }

    public function setFfmpegPath($setFfmpegPath)
    {
        $this->ffmpegConv->setFfmpegPath($setFfmpegPath);
    }

    public function setFfprobePath($setFfprobePath)
    {
        $this->ffmpegConv->setFfprobePath($setFfprobePath);
    }

    public function fileToHls_S3($fileToHls_S3, $lastTask) // TODO: recreate
    {
        echo "\n\rFileSteward fileToHls_S3\n";
        print_r($fileToHls_S3);
        $this->ffmpegConv->fileToHls($fileToHls_S3, $lastTask);
        $u = $this->welcome->get_m3u8_video_segment($fileToHls_S3);
        $path_parts = pathinfo($fileToHls_S3);
        $u[] = $path_parts['filename'] . ".m3u8";
        echo "\n\rFileSteward u\n";
        print_r($u);

        foreach ($u as $key => $val) {
            //echo "\n\r foreach $key: \n\r";
            //echo "\n\r foreach $val: \n\r";
            $fullNewFilename = $this->welcome->nadtemp . $val;
            $resMp4ToS3 = $this->s3->uploadFile([
                "file" => $fullNewFilename,
                "name" => $val
            ]);
        }

        $resJpgToS3 = $this->s3->uploadImage([ // TODO: Remove
            "file" => $path_parts['filename'] . '.jpg',
            /*"name" => $path_parts['filename']*/
        ]);
        echo "\n\rFileSteward fileToHls_S3 resJpgToS3\n";
        print_r($resJpgToS3);
        //$ffpegConvRes = $this->ffmpegConv->getVideoDuration($fileToHls_S3); // not work with webM
        /*$ffpegConvRes = $this->ffmpegConv->getVideoDuration($path_parts['dirname'] . '/' . $path_parts['filename'] . '.m3u8');
        //$ffpegConvRes = $this->ffmpegConv->getVideoDuration($this->welcome->nadtemp . $path_parts['filename'] . ".m3u8");
        //$ffpegConvRes = 20;
        echo "\n\rFileSteward fileToHls_S3 getVideoDuration\n";
        print_r($ffpegConvRes);

        return $ffpegConvRes;*/
        $u['video_duration'] = $this->ffmpegConv->getVideoDuration($fileToHls_S3);
        echo "\n\rFileSteward u\n";
        print_r($u);
        // TODO: add check
        return $u;
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

    public function fileToHls240($fileToHls240) // TODO: remove
    {
        echo "\n\rFileSteward fileToHls240\n\r";
        print_r($fileToHls240);
        //exit;
        $this->ffmpegConv->fileToHls240($fileToHls240);
        $u = $this->welcome->get_m3u8_video_segment($fileToHls240);
        $path_parts = pathinfo($fileToHls240);
        $u[] = $path_parts['filename'] . ".m3u8";
        $u['video_duration'] = $this->ffmpegConv->getVideoDuration($fileToHls240);
        echo "\n\rFileSteward u\n";
        print_r($u);
        // TODO: add check
        return $u;
    }

    public function fileToHlsAny($fileToHls240Any, $param)
    {
        $this->log->toFile(['service' => 'file', 'type' => '', 'text' => 'fileToHlsAny start ' . $fileToHls240Any]);

        echo "\n\rFileSteward fileToHlsAny\n\r";
        print_r($fileToHls240Any);
        echo "\n\rFileSteward param\n\r";
        print_r($param);
        //exit;
        //$res = $this->ffmpegConv->fileToHlsAny($fileToHls240Any, $param);
        $this->ffmpegConv->fileToHlsAny($fileToHls240Any, $param);
        $u = $this->welcome->get_m3u8_video_segment($fileToHls240Any);
        $path_parts = pathinfo($fileToHls240Any);
        //$u[] = $path_parts['filename'] . '-' . $param['RESOLUTION_Y'] . ".m3u8";
        $u[] = $path_parts['filename'] . ".m3u8";
        //if (!empty($res['error'])) $u['error'] = $res['error'];
        $u['video_duration'] = $this->ffmpegConv->getVideoDuration($fileToHls240Any);
        echo "\n\rFileSteward u\n";
        print_r($u);
        // TODO: add check
        $this->log->toFile(['service' => 'file', 'type' => '', 'text' => 'fileToHlsAny return ' . $fileToHls240Any]);

        return $u;
    }

    public function fileToMP4($fileToMP4, $param) // TODO: remove?
    {
        echo "\n\rfileToMP4 fileToMP4\n\r";
        print_r($fileToMP4);
        echo "\n\rfileToMP4 param\n\r";
        print_r($param);
        //exit;
        $this->ffmpegConv->fileToMP4($fileToMP4, $param);
        /*$u = $this->welcome->get_m3u8_video_segment($fileToMP4);
        $path_parts = pathinfo($fileToMP4);
        //$u[] = $path_parts['filename'] . '-' . $param['RESOLUTION_Y'] . ".m3u8";
        $u[] = $path_parts['filename'] . ".m3u8";
        $u['video_duration'] = $this->ffmpegConv->getVideoDuration($fileToMP4);
        echo "\n\rFileSteward u\n";
        print_r($u);
        // TODO: add check
        return $u;*/
    }

    public function fileToS3($fileToS3)
    {
        $this->log->toFile(['service' => 'file', 'type' => '', 'text' => 'fileToS3 start ' . $fileToS3['task_item_id']]);

        echo "\n\rFileSteward fileToS3\n";
        print_r($fileToS3);
        $dataArray = $this->welcome->ConvParseData(json_decode($fileToS3['data_json']));
        echo "\n\rFileSteward json_decode dataArray\n";
        var_dump($dataArray);
        //$newFileToS3 = $dataArray;
        if (!empty($dataArray["video_duration"])) unset ($dataArray["video_duration"]);

        foreach ($dataArray as $key => $val) {
            //echo "\n\r foreach $key: \n\r";
            //echo "\n\r foreach $val: \n\r";
            $fullNewFilename = $this->welcome->nadtemp . $val;
            $resMp4ToS3 = $this->s3->uploadFile([
                "file" => $fullNewFilename,
                "name" => $val
            ]);
            //$this->uploadToEmergencyServer(['file' => $fullNewFilename, 'origin' => 'video']);
        }

        $resJpgToS3 = $this->s3->uploadImage([ // TODO: Remove
            "file" => $fileToS3['task_item_id'] . '.jpg',
            /*"name" => $path_parts['filename']*/
        ]);
        $this->log->toFile(['service' => 'file', 'type' => '', 'text' => 'fileToS3 return ' . $fileToS3['task_item_id']]);
       // $this->uploadToEmergencyServer(['file' => $this->welcome->nadtemp . $fileToS3['task_item_id'] . '.jpg', 'origin' => 'img']);

        return $resMp4ToS3;
    }

    public function fileRemove($fileRemove)
    {
        $this->log->toFile(['service' => 'file_remove', 'type' => '', 'text' => 'fileRemove start ' . $fileRemove['task_item_id']]);
        echo "\n\rFileSteward fileRemove\n";
        print_r($fileRemove);
        $dataArray = $this->welcome->ConvParseData(json_decode($fileRemove['data_json']));
        echo "\n\rFileSteward json_decode dataArray\n";
        var_dump($dataArray);
        //$newFileToS3 = $dataArray;
        //if (!empty($dataArray["video_duration"])) unset ($dataArray["video_duration"]);
        foreach ($dataArray as $key => $val) {
            //echo "\n\r foreach $key: \n\r";
            //echo "\n\r foreach $val: \n\r";
            $fullNewFilename = $this->welcome->nadtemp . $val;
            /*$resMp4ToS3 = $this->s3->uploadFile([
                "file" => $fullNewFilename,
                "name" => $val
            ]);*/
            $this->welcome->RedisAddArray(['key' => 'del_' . $fileRemove['task_item_id'], 'value' => $fullNewFilename]);

            $this->log->toFile(['service' => 'file_remove', 'type' => '', 'text' => 'fileRemove file ' . $fullNewFilename]);
            //unlink($fullNewFilename);
        }
        /*$resJpgToS3 = $this->s3->uploadImage([ // TODO: Remove
            "file" => $fileRemove['task_item_id'] . '.jpg',
            /!*"name" => $path_parts['filename']*!/
        ]);*/
        $this->log->toFile(['service' => 'file_remove', 'type' => '', 'text' => 'fileRemove return ' . $fileRemove['task_item_id']]);
        //return $resMp4ToS3;
    }

    public function fileRemoveFromRedisAddArray($fileRemoveFromRedisAddArray)
    {
        $this->log->toFile(['service' => 'file_remove', 'type' => '', 'text' => 'fileRemoveFromRedisAddArray start ' . $fileRemoveFromRedisAddArray['task_item_id']]);
        echo "\n\rfileRemoveFromRedisAddArray fileRemoveFromRedisAddArray\n";
        print_r($fileRemoveFromRedisAddArray);
        $jsonFile = $this->welcome->memcachedGetKey(['key' => 'del_' . $fileRemoveFromRedisAddArray['task_item_id']]);
        //$dataArray = $this->welcome->ConvParseData(json_decode($fileRemoveFromRedisAddArray['data_json']));
        echo "\n\rfileRemoveFromRedisAddArray jsonFile\n";
        var_dump($jsonFile);
        $arrayFile = json_decode($jsonFile);
        echo "\n\rfileRemoveFromRedisAddArray arrayFile\n";
        var_dump($arrayFile);
        //$newFileToS3 = $dataArray;
        //if (!empty($dataArray["video_duration"])) unset ($dataArray["video_duration"]);
        foreach ($arrayFile as $key => $val) {
            $this->log->toFile(['service' => 'file_remove', 'type' => '', 'text' => 'fileRemoveFromRedisAddArray file ' . $val]);
            //!!! ===== work!!! unlink($val);
            unlink($val);
        }
        $this->log->toFile(['service' => 'file_remove', 'type' => '', 'text' => 'fileRemoveFromRedisAddArray return ' . $fileRemoveFromRedisAddArray['task_item_id']]);
        //return $resMp4ToS3;
    }

    public function fileToS3NoJPG($fileToS3NoJPG)
    {
        $this->log->toFile(['service' => 'file', 'type' => '', 'text' => 'fileToS3NoJPG start ' . $fileToS3NoJPG['task_item_id']]);

        echo "\n\rFileSteward fileToS3NoJPG\n";
        print_r($fileToS3NoJPG);
        $dataArray = $this->welcome->ConvParseData(json_decode($fileToS3NoJPG['data_json']));
        echo "\n\rFileSteward json_decode dataArray\n";
        var_dump($dataArray);
        //$newFileToS3 = $dataArray;
        //if (!empty($dataArray["video_duration"])) unset ($dataArray["video_duration"]); // "video_duration":0, <--- err
        if (array_key_exists("video_duration", $dataArray)) unset ($dataArray["video_duration"]);

        foreach ($dataArray as $key => $val) {
            //echo "\n\r foreach $key: \n\r";
            //echo "\n\r foreach $val: \n\r";
            $fullNewFilename = $this->welcome->nadtemp . $val;
            $resMp4ToS3 = $this->s3->uploadFile([
                "file" => $fullNewFilename,
                "name" => $val
            ]);
            $this->uploadToEmergencyServer(['file' => $fullNewFilename, 'origin' => 'video']);
        }
        $this->log->toFile(['service' => 'file', 'type' => '', 'text' => 'fileToS3NoJPG return ' . $fileToS3NoJPG['task_item_id']]);
        return $resMp4ToS3;
    }

    public function fileRemoveNoJPG($fileRemoveNoJPG)
    {
        $this->log->toFile(['service' => 'file_remove', 'type' => '', 'text' => 'fileRemoveNoJPG start ' . $fileRemoveNoJPG['task_item_id']]);
        echo "\n\rFileSteward fileRemoveNoJPG\n";
        print_r($fileRemoveNoJPG);
        $dataArray = $this->welcome->ConvParseData(json_decode($fileRemoveNoJPG['data_json']));
        echo "\n\rFileSteward json_decode dataArray\n";
        var_dump($dataArray);
        //$newFileToS3 = $dataArray;
        //if (!empty($dataArray["video_duration"])) unset ($dataArray["video_duration"]); // "video_duration":0, <--- err
        if (array_key_exists("video_duration", $dataArray)) unset ($dataArray["video_duration"]);
        foreach ($dataArray as $key => $val) {
            //echo "\n\r foreach $key: \n\r";
            //echo "\n\r foreach $val: \n\r";
            $fullNewFilename = $this->welcome->nadtemp . $val;
            /*$resMp4ToS3 = $this->s3->uploadFile([
                "file" => $fullNewFilename,
                "name" => $val
            ]);*/
            $this->welcome->RedisAddArray(['key' => 'del_' . $fileRemoveNoJPG['task_item_id'], 'value' => $fullNewFilename]);

            $this->log->toFile(['service' => 'file_remove', 'type' => '', 'text' => 'fileRemoveNoJPG file ' . $fullNewFilename]);
            //unlink($fullNewFilename);
        }
        $this->log->toFile(['service' => 'file_remove', 'type' => '', 'text' => 'fileRemoveNoJPG return ' . $fileRemoveNoJPG['task_item_id']]);
        //return $resMp4ToS3;
    }

    public function fileToS3_pre_video_image_sprite($fileToS3_pre_video_image_sprite)
    {
        $this->log->toFile(['service' => 'file', 'type' => '', 'text' => 'fileToS3_pre_video_image_sprite start ' . $fileToS3_pre_video_image_sprite['file']]);

        echo "\n\rFileSteward fileToS3_pre_video_image_sprite\n";
        print_r($fileToS3_pre_video_image_sprite);
        //$dataArray = $this->welcome->ConvParseData(json_decode($fileToS3_pre_video_image_sprite['data_json']));
        /*echo "\n\rFileSteward json_decode dataArray\n";
        var_dump($dataArray);
        //$newFileToS3 = $dataArray;
        //if (!empty($dataArray["video_duration"])) unset ($dataArray["video_duration"]); // "video_duration":0, <--- err
        if (array_key_exists("video_duration", $dataArray)) unset ($dataArray["video_duration"]);*/

        /*foreach ($dataArray as $key => $val) {
            //echo "\n\r foreach $key: \n\r";
            //echo "\n\r foreach $val: \n\r";
            $fullNewFilename = $this->welcome->nadtemp . $val;
            $resMp4ToS3 = $this->s3->uploadFile([
                "file" => $fullNewFilename,
                "name" => $val
            ]);
        }*/

        $fullNewFilename = $this->welcome->nadtemp . 'pre-video-w320/' . $fileToS3_pre_video_image_sprite['file'] . '-pre-v-w320.mp4';
        $resMp4ToS3 = $this->s3->uploadFile_pre_video([
            "file" => $fullNewFilename,
            "name" => $fileToS3_pre_video_image_sprite['file'] . '-pre-v-w320.mp4'
        ]);

        $this->uploadToEmergencyServer(['file' => $fullNewFilename, 'origin' => 'pre-video-w320']);

        $fullNewFilename = $this->welcome->nadtemp . 'pre-image-w320/' . $fileToS3_pre_video_image_sprite['file'] . '-pre-i-w320.jpg';
        $resMp4ToS3 = $this->s3->uploadFile_pre_image([
            "file" => $fullNewFilename,
            "name" => $fileToS3_pre_video_image_sprite['file'] . '-pre-i-w320.jpg'
        ]);

        $this->uploadToEmergencyServer(['file' => $fullNewFilename, 'origin' => 'pre-image-w320']);


        //if ($video_info['video_duration'] > 29) {
        $fullNewFilename = $this->welcome->nadtemp . 'sprite-w120/' . $fileToS3_pre_video_image_sprite['file'] . '-spr-w120.jpg';
        $resMp4ToS3 = $this->s3->uploadFile_thumb_sprite([
            "file" => $fullNewFilename,
            "name" => $fileToS3_pre_video_image_sprite['file'] . '-spr-w120.jpg'
        ]);
        $this->uploadToEmergencyServer(['file' => $fullNewFilename, 'origin' => 'sprite-w120']);
        //}

        $fullNewFilename = $this->welcome->nadtemp . 'sprite-w120/' . $fileToS3_pre_video_image_sprite['file'] . '-spr-w120.vtt';
        $resMp4ToS3 = $this->s3->uploadFile_vtt([
            "file" => $fullNewFilename,
            "name" => $fileToS3_pre_video_image_sprite['file'] . '-spr-w120.vtt'
        ]);
        $this->uploadToEmergencyServer(['file' => $fullNewFilename, 'origin' => 'sprite-w120']);

        $this->log->toFile(['service' => 'file', 'type' => '', 'text' => 'fileToS3_pre_video_image_sprite return ' . $fileToS3_pre_video_image_sprite['file']]);

        return $resMp4ToS3;
    }

    public function fileRemove_pre_video_image_sprite($fileRemove_pre_video_image_sprite)
    {
        $this->log->toFile(['service' => 'file', 'type' => '', 'text' => 'fileRemove_pre_video_image_sprite start ' . $fileRemove_pre_video_image_sprite['file']]);
        echo "\n\rFileSteward fileRemove_pre_video_image_sprite\n";
        print_r($fileRemove_pre_video_image_sprite);
        $preVw320Filename = $this->welcome->nadtemp . 'pre-video-w320/' . $fileRemove_pre_video_image_sprite['task_item_id'] . '-pre-v-w320.mp4';
        $sec5Filename = $this->welcome->nadtemp . 'pre-video-w320/' . $fileRemove_pre_video_image_sprite['task_item_id'] . '-5sec.mp4';
        $preIw320Filename = $this->welcome->nadtemp . 'pre-image-w320/' . $fileRemove_pre_video_image_sprite['task_item_id'] . '-pre-i-w320.jpg';
        $preIw320FilenameHTML = $_SERVER['DOCUMENT_ROOT'] . '/pre-image-w320/' . $fileRemove_pre_video_image_sprite['task_item_id'] . '.jpg';
        $sprW120JPGFilename = $this->welcome->nadtemp . 'sprite-w120/' . $fileRemove_pre_video_image_sprite['task_item_id'] . '-spr-w120.jpg';
        $sprW120VTTFilename = $this->welcome->nadtemp . 'sprite-w120/' . $fileRemove_pre_video_image_sprite['task_item_id'] . '-spr-w120.vtt';
        $mp4240Filename = $this->welcome->nadtemp . $fileRemove_pre_video_image_sprite['task_item_id'] . '-240.mp4';
        $jpgFilename = $this->welcome->nadtemp . $fileRemove_pre_video_image_sprite['task_item_id'] . '.jpg';
        $mp4Filename = $this->welcome->nadtemp . $fileRemove_pre_video_image_sprite['task_item_id'] . '.mp4';
        $this->log->toFile(['service' => 'file_remove', 'type' => '', 'text' => 'fileRemove_pre_video_image_sprite file ' . $preVw320Filename]);
        $this->log->toFile(['service' => 'file_remove', 'type' => '', 'text' => 'fileRemove_pre_video_image_sprite file ' . $sec5Filename]);
        $this->log->toFile(['service' => 'file_remove', 'type' => '', 'text' => 'fileRemove_pre_video_image_sprite file ' . $preIw320Filename]);
        $this->log->toFile(['service' => 'file_remove', 'type' => '', 'text' => 'fileRemove_pre_video_image_sprite file ' . $preIw320FilenameHTML]);
        $this->log->toFile(['service' => 'file_remove', 'type' => '', 'text' => 'fileRemove_pre_video_image_sprite file ' . $sprW120JPGFilename]);
        $this->log->toFile(['service' => 'file_remove', 'type' => '', 'text' => 'fileRemove_pre_video_image_sprite file ' . $sprW120VTTFilename]);
        $this->log->toFile(['service' => 'file_remove', 'type' => '', 'text' => 'fileRemove_pre_video_image_sprite file ' . $mp4240Filename]);
        $this->log->toFile(['service' => 'file_remove', 'type' => '', 'text' => 'fileRemove_pre_video_image_sprite file ' . $jpgFilename]);
        $this->log->toFile(['service' => 'file_remove', 'type' => '', 'text' => 'fileRemove_pre_video_image_sprite file ' . $mp4Filename]);
        /*unlink($preVw320Filename);
        unlink($preIw320Filename);
        unlink($sprW120JPGFilename);
        unlink($sprW120VTTFilename);*/
        $this->welcome->RedisAddArray(['key' => 'del_' . $fileRemove_pre_video_image_sprite['task_item_id'], 'value' => $preVw320Filename]);
        $this->welcome->RedisAddArray(['key' => 'del_' . $fileRemove_pre_video_image_sprite['task_item_id'], 'value' => $sec5Filename]);
        $this->welcome->RedisAddArray(['key' => 'del_' . $fileRemove_pre_video_image_sprite['task_item_id'], 'value' => $preIw320Filename]);
        $this->welcome->RedisAddArray(['key' => 'del_' . $fileRemove_pre_video_image_sprite['task_item_id'], 'value' => $preIw320FilenameHTML]);
        $this->welcome->RedisAddArray(['key' => 'del_' . $fileRemove_pre_video_image_sprite['task_item_id'], 'value' => $sprW120JPGFilename]);
        $this->welcome->RedisAddArray(['key' => 'del_' . $fileRemove_pre_video_image_sprite['task_item_id'], 'value' => $sprW120VTTFilename]);
        $this->welcome->RedisAddArray(['key' => 'del_' . $fileRemove_pre_video_image_sprite['task_item_id'], 'value' => $mp4240Filename]);
        $this->welcome->RedisAddArray(['key' => 'del_' . $fileRemove_pre_video_image_sprite['task_item_id'], 'value' => $jpgFilename]);
        $this->welcome->RedisAddArray(['key' => 'del_' . $fileRemove_pre_video_image_sprite['task_item_id'], 'value' => $mp4Filename]);
    }

    public function fileToS3_sprite_Only($fileToS3_pre_video_image_sprite, $video_info) // TODO: remove
    {
        echo "\n\rFileSteward fileToS3_pre_video_image_sprite\n";
        print_r($fileToS3_pre_video_image_sprite);
        //$dataArray = $this->welcome->ConvParseData(json_decode($fileToS3_pre_video_image_sprite['data_json']));
        /*echo "\n\rFileSteward json_decode dataArray\n";
        var_dump($dataArray);
        //$newFileToS3 = $dataArray;
        //if (!empty($dataArray["video_duration"])) unset ($dataArray["video_duration"]); // "video_duration":0, <--- err
        if (array_key_exists("video_duration", $dataArray)) unset ($dataArray["video_duration"]);*/

        /*foreach ($dataArray as $key => $val) {
            //echo "\n\r foreach $key: \n\r";
            //echo "\n\r foreach $val: \n\r";
            $fullNewFilename = $this->welcome->nadtemp . $val;
            $resMp4ToS3 = $this->s3->uploadFile([
                "file" => $fullNewFilename,
                "name" => $val
            ]);
        }*/

        /*$fullNewFilename = $this->welcome->nadtemp . 'pre-video-w320/' . $fileToS3_pre_video_image_sprite['file'] . '-pre-v-w320.mp4';
        $resMp4ToS3 = $this->s3->uploadFile_pre_video([
            "file" => $fullNewFilename,
            "name" => $fileToS3_pre_video_image_sprite['file'] . '-pre-v-w320.mp4'
        ]);

        $fullNewFilename = $this->welcome->nadtemp . 'pre-image-w320/' . $fileToS3_pre_video_image_sprite['file'] . '-pre-i-w320.jpg';
        $resMp4ToS3 = $this->s3->uploadFile_pre_image([
            "file" => $fullNewFilename,
            "name" => $fileToS3_pre_video_image_sprite['file'] . '-pre-i-w320.jpg'
        ]);

        //if ($video_info['video_duration'] > 29) {
            $fullNewFilename = $this->welcome->nadtemp . 'sprite-w120/' . $fileToS3_pre_video_image_sprite['file'] . '-spr-w120.jpg';
            $resMp4ToS3 = $this->s3->uploadFile_thumb_sprite([
                "file" => $fullNewFilename,
                "name" => $fileToS3_pre_video_image_sprite['file'] . '-spr-w120.jpg'
            ]);
        //}*/
        $fullNewFilename = $this->welcome->nadtemp . 'sprite-w120/' . $fileToS3_pre_video_image_sprite['file'] . '-spr-w120.vtt';
        $resMp4ToS3 = $this->s3->uploadFile_vtt([
            "file" => $fullNewFilename,
            "name" => $fileToS3_pre_video_image_sprite['file'] . '-spr-w120.vtt'
        ]);
        return $resMp4ToS3;
    }

    public function compMultiM3U8Start($compMultiM3U8Start)
    {
        $this->log->toFile(['service' => 'file', 'type' => '', 'text' => 'compMultiM3U8Start start ' . $compMultiM3U8Start['item_id']]);

        //$myfile = fopen($this->raw_path."/".$this->file_name.".m3u8", "w") or die("Unable to open file!");
        $fulFileName = $this->welcome->nadtemp . $compMultiM3U8Start['item_id'];
        $myfile = fopen($fulFileName . ".m3u8", "w") or die("Unable to open file!");

        $txt = "#EXTM3U\n";

        fwrite($myfile, $txt);

        $txt = "#EXT-X-VERSION:3\n";

        fwrite($myfile, $txt);
        fclose($myfile);
        $this->log->toFile(['service' => 'file', 'type' => '', 'text' => 'compMultiM3U8Start return ' . $compMultiM3U8Start['item_id']]);

    }

    public function imgToS3Items($imgToS3Items)
    {
        //echo "\nimgToS3Items \n";
        //print_r($imgToS3Items);
        if (!empty($imgToS3Items['item_id'] and !empty($imgToS3Items['owner_id']))) {
            /* ================================ */
            $s3 = new S3();
            //$welcome = new NAD();
            //$path_parts = pathinfo($fileToHls_S3);
            //$path_parts['filename'];
            $resJpgToS3 = $s3->uploadImage([
                /*"file" => $this->nadtemp . $imgToS3IMG['item_id'],*/
                "file" => $imgToS3Items['file']
            ]);
            $this->uploadToEmergencyServer(['file' => $this->welcome->nadtemp . $imgToS3Items['file'], 'origin' => 'img']);

            //echo "\nresJpgToS3 \n";
            //print_r($resJpgToS3);
            /* ================================ */
            if ($resJpgToS3) {
                $dataItems['item_id'] = $imgToS3Items['item_id'];
                $dataItems['owner_id'] = $imgToS3Items['owner_id'];
                if (!empty($imgToS3Items['access'])) {
                    $dataItems['access'] = $imgToS3Items['access'];
                } else {
                    $dataItems['access'] = 'public';
                }
                $dataItems['type'] = 'image';
                $dataItems['cover'] = $imgToS3Items['file'];
                if (!empty($imgToS3Items['title'])) $dataItems['title'] = $imgToS3Items['title'];
                if (!empty($imgToS3Items['content'])) $dataItems['content'] = $imgToS3Items['content'];
                /*$tags = [];
                foreach ($articleBody['tags'] as $key => $value) {
                    echo "\n\r foreach key: " . $key;
                    echo "\n\r foreach value: " . $value;
                    $tags[] = htmlspecialchars($value);
                }
                $dataItems['tags'] = json_encode($tags);
                $dataItemsForTags['item_id'] = $itemId;
                $resItemsTags = $this->welcome->addToTags($dataItemsForTags);*/
                //echo "\nurlImgToItem dataItems \n";
                //print_r($dataItems);
                $resItems = $this->welcome->addToItems($dataItems);
                if ($imgToS3Items['access'] !== 'private'
                    and $resItems
                    and $imgToS3Items['send_to'] !== 'items_only') {

                    if ($dataItems['access'] == 'friends')
                        $this->welcome->pgSetAccessFriends($imgToS3Items);
                    //} else {
                    $dataPosts['post_id'] = $this->welcome->trueRandom();
                    $dataPosts['item_id'] = $dataItems['item_id'];
                    $dataPosts['type'] = $imgToS3Items['post_type'];
                    $dataPosts['post_owner_id'] = $dataItems['owner_id'];
                    if (!empty($imgToS3Items['sign_id']))
                        $dataPosts['sign_id'] = $imgToS3Items['sign_id'];
                    //$dataPosts['type'] = 'article';
                    //$dataPosts['title'] = $articlePublished[$this->welcome->subject];
                    //$dataPosts['content'] = $articlePublished[$this->welcome->message];
                    //$dataPosts['created_at'] = $articleConfirm[$this->welcome->createdAt];
                    //$dataPosts['tags'] = 'video';
                    //echo "\nadd to posts \n";
                    //print_r($dataPosts);
                    //echo $this->welcome->pgAddData($this->pg->getTableItems(), $dataSigns);
                    $this->welcome->addToPosts($dataPosts);
                    //}
                }

                return $imgToS3Items['file'];
            } else {
                echo 'Upload error';
                return false;
            }
            //return $dataItems['item_id'];
            /* ======================================= */
        } else {
            echo 'Empty item_id or owner_id';
            return false;
        }
    }

    public function compMultiM3U8($compMultiM3U8)
    {
        $this->log->toFile(['service' => 'file', 'type' => '', 'text' => 'compMultiM3U8 start ' . $compMultiM3U8['item_id']]);

        // https://stackoverflow.com/questions/46305780/creating-master-playlist-for-hls
        //$myfile = fopen($this->raw_path."/".$this->file_name.".m3u8", "w") or die("Unable to open file!");
        $fulFileName = $this->welcome->nadtemp . $compMultiM3U8['item_id'];
        //$myfile = fopen($fulFileName . ".m3u8", "w") or die("Unable to open file!");

        /*$txt = "#EXTM3U\n";

        fwrite($myfile, $txt);

        $txt = "#EXT-X-VERSION:3\n";

        fwrite($myfile, $txt);*/
        // fclose($myfile);
        /*if($convertedRes['720']){

            $txt = "#EXT-X-STREAM-INF:PROGRAM-ID=1,BANDWIDTH=73056000,RESOLUTION=1280x720\n";
            fwrite($myfile, $txt);
            //$txt = $this->file_name."/".$this->file_name."-720.m3u8\n";
            fwrite($myfile, $txt);

        }
        if($convertedRes['480']){

            $txt = "#EXT-X-STREAM-INF:PROGRAM-ID=1,BANDWIDTH=5605600,RESOLUTION=854x480\n";
            fwrite($myfile, $txt);
            $txt = $this->file_name."/".$this->file_name."-480.m3u8\n";
            fwrite($myfile, $txt);

        }

        if($convertedRes['360']){

            $txt = "#EXT-X-STREAM-INF:PROGRAM-ID=1,BANDWIDTH=2855600,RESOLUTION=640x360\n";
            fwrite($myfile, $txt);
            $txt = $this->file_name."/".$this->file_name."-360.m3u8\n";
            fwrite($myfile, $txt);

        }

        if($convertedRes['240']){


            $txt = "#EXT-X-STREAM-INF:PROGRAM-ID=1,BANDWIDTH=1755600,RESOLUTION=428x240\n";
            fwrite($myfile, $txt);
            $txt = $this->file_name."/".$this->file_name."-240.m3u8\n";
            fwrite($myfile, $txt);


        }*/
        /*foreach ($compMultiM3U8['size'] as $key => $vallue)
        {

        }*/
        /*************************************************************************/
        /*$txt = "#EXT-X-STREAM-INF:PROGRAM-ID=1,BANDWIDTH=" . $compMultiM3U8['BANDWIDTH'] . ",RESOLUTION=" . $compMultiM3U8['RESOLUTION_X'] . "x" . $compMultiM3U8['RESOLUTION_Y'] . "\n";
        fwrite($myfile, $txt);
        $txt = $fulFileName . "-" . $compMultiM3U8['RESOLUTION_Y'] . ".m3u8\n";
        fwrite($myfile, $txt);*/
        /*************************************************************************/

        //fclose($myfile);

        $txt = "#EXT-X-STREAM-INF:PROGRAM-ID=1,BANDWIDTH=" . $compMultiM3U8['BANDWIDTH'] . ",RESOLUTION=" . $compMultiM3U8['RESOLUTION_X'] . "x" . $compMultiM3U8['RESOLUTION_Y'] . "\n";
        $txt .= $compMultiM3U8['item_id'] . "-" . $compMultiM3U8['RESOLUTION_Y'] . ".m3u8\n";
        file_put_contents($fulFileName . ".m3u8", $txt, FILE_APPEND | LOCK_EX);
        $this->log->toFile(['service' => 'file', 'type' => '', 'text' => 'compMultiM3U8 return ' . $compMultiM3U8['item_id']]);

    }

    public function uploadToEmergencyServer($uploadToEmergencyServer)
    {
        return true;
        //$welcome = new NAD();
        $web_page_to_send = 'https://video.vide.me/upload/upload_test.php';
        if (!empty($uploadToEmergencyServer['file']) and !empty($uploadToEmergencyServer['origin'])) {
            $file_name_with_full_path = $uploadToEmergencyServer['file'];
            $post_request = [
                "origin" => $uploadToEmergencyServer['origin'],
                "file" => curl_file_create($file_name_with_full_path) // for php 5.5+
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $web_page_to_send);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_request);
            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'Error: ' . curl_error($ch);
                //exit();
                $this->log->toFile(['service' => 'file_upload_emergency', 'type' => 'error', 'text' => 'curl file: ' . $uploadToEmergencyServer['file'] . ' origin: ' . $uploadToEmergencyServer['origin']]);
                curl_close($ch);
                return false;
            } else {
                $this->log->toFile(['service' => 'file_upload_emergency', 'type' => 'success', 'text' => 'curl file: ' . $uploadToEmergencyServer['file'] . ' origin: ' . $uploadToEmergencyServer['origin']]);
                echo "Upload Successful!<br><br>Return: " . $result;
                curl_close($ch);
                return true;
            }
        } else {
            return false;
        }
    }
}