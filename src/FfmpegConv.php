<?php
/**
 * Created by PhpStorm.
 * User: sergey
 * Date: 13.03.18
 * Time: 22:26
 */

namespace VideMe\Ffmpegconversion;


putenv("PATH=/usr/bin");

use FFMpeg;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Format\Video\X264;
//use Emgag\Video\ThumbnailSprite\ThumbnailSprite;
use VideMe\Datacraft\nad;
//use VideMe\Datacraft;
//namespace VideMe\Datacraft\model\PostgreSQL;
use VideMe\Datacraft\log\log;

/*include_once($_SERVER['DOCUMENT_ROOT'] . '/nad/index.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/sendmail/sendmail.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/system/log/log.php');*/

class FfmpegConv
{
    public $ffmpegPath = '/usr/bin/ffmpeg';
    public $ffprobePath = '/usr/bin/ffprobe';
    public $percentage;
    public $lastTask;

    public function __construct()
    {
        //echo "\n\rFfmpegConv __construct getFfprobePath\n";
        //print_r($this->getFfprobePath());
        $this->welcome = new NADFFMpeg();
        $this->log = new log();
        $this->ffmpeg = FFMpeg\FFMpeg::create(array( // TODO: WHY?
            //'ffmpeg.binaries'  => '/usr/bin/ffmpeg', // common
            //'ffmpeg.binaries'  => '/home/ubuntu/bin/ffmpeg', // aws
            'ffmpeg.binaries' => '/usr/bin/ffmpeg', // aws vide18
            //'ffmpeg.binaries'  => $this->getFfmpegPath(),
            //'ffmpeg.binaries'  => exec('which ffmpeg'), // Command 'which' from package 'debianutils' (main)

            //'ffprobe.binaries' => '/usr/bin/ffprobe', // common
            //'ffprobe.binaries' => '/home/ubuntu/bin/ffprobe', // aws
            'ffprobe.binaries' => '/usr/bin/ffprobe', // aws vide18
            //'ffprobe.binaries' => $this->getFfprobePath(),
            //'ffprobe.binaries' => exec('which ffprobe'),
            'timeout' => 3600 // The timeout for the underlying process
            //'ffmpeg.threads'   => 12,   // The number of threads that FFMpeg should use
        ));
    }

    /**
     * @param string $ffmpegPath
     */
    public function setFfmpegPath(string $ffmpegPath): void
    {
        $this->ffmpegPath = $ffmpegPath;
    }

    /**
     * @param string $ffprobePath
     */
    public function setFfprobePath(string $ffprobePath): void
    {
        $this->ffprobePath = $ffprobePath;
    }

    /**
     * @return string
     */
    public function getFfmpegPath(): string
    {
        return $this->ffmpegPath;
    }

    /**
     * @return string
     */
    public function getFfprobePath(): string
    {
        return $this->ffprobePath;
    }

    /**
     * @param mixed $percentage
     */
    public function setPercentage($percentage): void
    {
        $this->percentage = $percentage;
    }

    /**
     * @return mixed
     */
    public function getPercentage()
    {
        return $this->percentage;
    }

    /**
     * @param mixed $lastTask
     */
    public function setLastTask($lastTask): void
    {
        $this->lastTask = $lastTask;
    }

    /**
     * @return mixed
     */
    public function getLastTask()
    {
        return $this->lastTask;
    }


    public function getVideoInfo($getVideoInfo)
    {
        $this->log->toFile(['service' => 'file', 'type' => '', 'text' => 'function getVideoInfo start ' . $getVideoInfo]);
        // https://github.com/PHP-FFMpeg/PHP-FFMpeg/issues/264
        //echo "\n\rgetVideoInfo\n";
        //print_r($getVideoInfo);
        //$path_parts = pathinfo($getVideoInfo);
        try {
            $ffprobe = FFMpeg\FFProbe::create([
                'ffmpeg.binaries' => '/usr/bin/ffmpeg', // aws vide18
                'ffprobe.binaries' => '/usr/bin/ffprobe', // aws vide18
                'timeout' => 3600, // The timeout for the underlying process
                'ffmpeg.threads' => 1
            ]);
            /*$video_info = $ffprobe
                ->streams($getVideoInfo) // extracts streams informations
                ->videos()                      // filters video streams
                ->first()                       // returns the first video stream
                ->get('codec_name');

            $output = array();
            $format = $ffprobe->format($getVideoInfo)->get('format_name');
            $channels = $ffprobe->streams($getVideoInfo)->videos()->first()->get('channels');
            $bits = $ffprobe->streams($getVideoInfo)->videos()->first()->get('bits_per_sample');
            $sample_rate = $ffprobe->streams($getVideoInfo)->videos()->first()->get('sample_rate');
            array_push($output, $format, $channels, $bits, $sample_rate);
            return $output;*/
            $streams = $ffprobe->streams($getVideoInfo);
            // extracts streams informations
            // get video data
            $video = $streams->videos()->first();
            // filters video first streams
            $dimensions = $video->getDimensions();
            // get audio data
            $audio = $streams->audios()->first();
            // filters audio first streams
            //$video_info = ['screen_width' => $dimensions->getWidth(), 'screen_height' => $dimensions->getHeight(), 'video_bitrate' => $video->get('bit_rate'), 'audio_bitrate' => $audio->get('bit_rate')];
            //$video_info = ['width' => $dimensions->getWidth(), 'height' => $dimensions->getHeight(), 'video_bitrate' => $video->get('bit_rate'), 'audio_bitrate' => $audio->get('bit_rate')]; // Fatal error</b>:  Uncaught Error: Call to a member function get() on null in /usr/share/nginx/html/nad/model/FfmpegConv.php:486
            $video_info = ['width' => $dimensions->getWidth(), 'height' => $dimensions->getHeight(), 'video_bitrate' => $video->get('bit_rate')];
            //echo "\n\rgetVideoInfo height\n";
            //print_r($video_info);
        } catch (Exception $e) {
            echo "\n\r======================================================\n\r";
            echo "\n\rgetVideoInfo error: " . $e . "\n\r";
            echo "\n\r======================================================\n\r";
            $timeE = $this->welcome->getTimeForPG_tz();
            //$sendmail = new sendmail();
            //$sendmail->SendStaffAlert(['message' => $timeE . "getVideoInfo error: " . $e]);
            //exit;
            return false;
        }
        $this->log->toFile(['service' => 'file', 'type' => '', 'text' => 'getVideoInfo return ' . $getVideoInfo]);
        return $video_info;
    }

    public function getVideoDuration($getVideoDuration)
    {
        $this->log->toFile(['service' => 'file', 'type' => '', 'text' =>
            'parent function: ' . debug_backtrace()[1]['function'] . ' class: ' . get_class($this) . '->' . __FUNCTION__ .
            ' start ' . $getVideoDuration]);

        //echo "\n\rgetVideoDuration\n";
        //print_r($getVideoDuration);
        $path_parts = pathinfo($getVideoDuration);
        try {
            $ffprobe = FFMpeg\FFProbe::create(array(
                //'ffmpeg.binaries'  => '/usr/bin/ffmpeg', // common
                //'ffmpeg.binaries'  => '/home/ubuntu/bin/ffmpeg', // aws
                'ffmpeg.binaries' => '/usr/bin/ffmpeg', // aws vide18
                //'ffprobe.binaries' => '/usr/bin/ffprobe', // common
                //'ffprobe.binaries' => '/home/ubuntu/bin/ffprobe', // aws
                'ffprobe.binaries' => '/usr/bin/ffprobe', // aws vide18
                'timeout' => 3600, // The timeout for the underlying process
                //'ffmpeg.threads'   => 12,   // The number of threads that FFMpeg should use
                'ffmpeg.threads' => 1
            ));
        } catch (Exception $e) {
            echo "\n\r======================================================\n\r";
            echo "\n\rgetVideoDuration error: " . $e . "\n\r";
            echo "\n\r======================================================\n\r";
            $sendmail = new sendmail();
            $sendmail->SendStaffAlert(['message' => "getVideoDuration error: " . $e]);
            //exit;
            return false;
        }
        try {
            $video_duration = $ffprobe
                //->format($this->welcome->nadtemp . $path_parts['filename'] . '.m3u8') // extracts file informations
                ->format($getVideoDuration)// extracts file informations
                ->get('duration');             // returns the duration property
        } catch (Exception $e) {
            echo "\n\r======================================================\n\r";
            echo "\n\rgetVideoDuration get error: " . $e . "\n\r";
            echo "\n\r======================================================\n\r";
            //$sendmail = new sendmail();
            //$sendmail->SendStaffAlert(['message' => "getVideoDuration get error: " . $e]);
            //exit;
            return false;
        }
        $ffpegConvRes['video_duration'] = round($video_duration, 0);
        //return $ffpegConvRes;
        //echo "\n\rgetVideoDuration ffpegConvRes\n";
        //print_r($ffpegConvRes);
        $this->log->toFile(['service' => 'file', 'type' => '', 'text' => 'getVideoDuration return ' . $getVideoDuration]);

        return $ffpegConvRes['video_duration'];
    }
    public function fileToMP4_get_image($fileToMP4_Only) // TODO: Test 08082021
    {
        //echo "\n\rfileToMP4_get_image\n\r";
        //print_r($fileToMP4_Only);
        //$path_parts = pathinfo($fileToMP4_Only);
        $path_parts = pathinfo($this->welcome->getNadtemp() . $fileToMP4_Only['file']);

        try {
            //$video = $this->ffmpeg->open($fileToMP4_Only);
            $video = $this->ffmpeg->open($this->welcome->nadtemp . $fileToMP4_Only['file']);
            //$video = $this->ffmpeg->open($this->welcome->nadtemp . $path_parts['filename'] . '-test.mp4');
        } catch (Exception $e) {
            echo "\n\r======================================================\n\r";
            echo "\n\rfileToMP4_get_image ffmpeg->open error: " . $e . "\n\r";
            echo "\n\r======================================================\n\r";
            //$sendmail = new sendmail();
            //$sendmail->SendStaffAlert(['message' => $e]);
            exit;
        }
        $video_info = $this->getVideoInfo($this->welcome->nadtemp . $fileToMP4_Only['file']);
        //echo "\n\rfileToMP4_get_image getVideoInfo\n\r";
        //print_r($video_info);
        $video_info['video_duration'] = $this->getVideoDuration($this->welcome->nadtemp . $fileToMP4_Only['file']);
        //echo "\n\rfileToMP4_get_image video_duration\n\r";
        //print_r($video_info);
        //$period = round($video_info['video_duration'] / 5);
        $period = round($video_info['video_duration'] / ($fileToMP4_Only['limit'] + 1));
        //echo "\n\rfileToMP4_get_image period $period\n\r";
        $time = 0;
        $i = 1;
        //while($i <= 4) {
        $retArray = [];
        while($i <= $fileToMP4_Only['limit']) {
            //echo "\n\rThe number is: $i \n\r";
            $i++;
            $time = $time + $period;
            //echo "\n\rfileToMP4_get_image time $time\n\r";
            $frae_name = $this->welcome->trueRandom();
            $retArray[] = $frae_name;
            //echo "\n\rretArray\n\r";
            //print_r($retArray);
            try {
                $video
                    //frame(FFMpeg\Coordinate\TimeCode::fromSeconds($param['from_seconds']))
                    ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds($time))
                    //->save($this->welcome->nadtemp . $path_parts['filename'] . '-test.jpg');
                    //->save($this->welcome->nadtemp . 'pre-image-w320/' . $path_parts['filename'] . '-pre-i-w320.jpg');
                    ->addFilter(new FFMpeg\Filters\Frame\CustomFrameFilter('scale=320x180')) //resize output frame image
                    //->extractMultipleFrames(new FFMpeg\Filters\Video\ExtractMultipleFramesFilter('FRAMERATE_EVERY_20SEC', $this->welcome->nadtemp))
                    //->filters()
                    //->ExtractMultipleFramesFilter('FRAMERATE_EVERY_20SEC', $this->welcome->nadtemp)
                    //->extractMultipleFrames('FRAMERATE_EVERY_20SEC', $this->welcome->nadtemp)
                    //->extractMultipleFrames(new FFMpeg\Filters\Video\ExtractMultipleFramesFilter::FRAMERATE_EVERY_10SEC, $this->welcome->nadtemp)
                    //->extractMultipleFrames(new FFMpeg\Filters\Video\ExtractMultipleFramesFilter('FRAMERATE_EVERY_10SEC', $this->welcome->nadtemp))
                    //->synchronize();
                    //->save($this->welcome->nadtemp . $param['filename'] . '_' . $frae_name . '.jpg');
                    ->save($this->welcome->nadtemp . 'pre-image-w320/'  . $frae_name . '.jpg');
                    //->save('/var/www/videme_nfs/pre-image-w320/'  . $frae_name . '.jpg');
                //->save($_SERVER['DOCUMENT_ROOT'] . '/pre-image-w320/'  . $frae_name . '.jpg');
                copy($this->welcome->nadtemp . 'pre-image-w320/'  . $frae_name . '.jpg',
                //copy('/var/www/videme_nfs/pre-image-w320/'  . $frae_name . '.jpg',
                    $_SERVER['DOCUMENT_ROOT'] . '/pre-image-w320/'  . $frae_name . '.jpg');
                /*$video
                    ->filters()
                    ->resize(new FFMpeg\Coordinate\Dimension($param['RESOLUTION_X'], $param['RESOLUTION_Y']))
                    ->synchronize();
                $frame = $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(3));
                $frame->save($this->welcome->nadtemp . $path_parts['filename'] . '-test.jpg');*/
            } catch (Exception $e) {
                echo "\n\r======================================================\n\r";
                echo "\n\rfileToMP4_get_image ffmpeg->save error: " . $e . "\n\r";
                echo "\n\r======================================================\n\r";
                //$sendmail = new sendmail();
                //$sendmail->SendStaffAlert(['message' => "fileToMP4_get_image ffmpeg->save error: " . $e]);
                exit;
            }
        }
        return $retArray;
    }

    public function fileToHls($fileToHls, $lastTask)
    {
        echo "\n\rFfmpegConv fileToHls\n";
        print_r($fileToHls);
        //$welcome = new NAD();
        //$log = new log();
        //exit;
        $path_parts = pathinfo($fileToHls);
        $this->setPercentage($path_parts['filename']);
        $this->setLastTask($lastTask);

        echo "\n\rFfmpegConv fileToHls lastTask\n\r";
        print_r($lastTask);
        //exit;
        try {
            $video = $this->ffmpeg->open($fileToHls);
        } catch (Exception $e) {
            echo "\n\r======================================================\n\r";
            echo "\n\rfileToHls ffmpeg->open error: " . $e . "\n\r";
            echo "\n\r======================================================\n\r";
            $sendmail = new sendmail();
            $sendmail->SendStaffAlert(['message' => "fileToHls ffmpeg->open error: " . $e]);

            $welcome = new NAD();
            $userInfo = $welcome->pgUserInfo($lastTask['owner_id']);
            $sendmail_user = new sendmail();
            $sendmail_user->SendItemUploadError([
                'item_id' => $lastTask['task_item_id'],
                'user_display_name' => $userInfo['user_display_name'],
                'title' => $lastTask['title'],
                'username' => $userInfo['user_email']]);
            exit;
        }
        //$format = new FFMpeg\Format\Video\X264('libmp3lame', 'libx264');
        //-$format = new FFMpeg\Format\Video\X264('libfdk_aac', 'libx264');
        try {
            $format = new FFMpeg\Format\Video\X264('aac', 'libx264');
            $format->setAdditionalParameters(['-hls_list_size', '0']);
            //$format->setAdditionalParameters( [ '-crf', '29' ] );
            //$format->setAdditionalParameters(['-hls_list_size', '0', '-movflags', '+faststart']); // 19072019 https://superuser.com/questions/802132/how-to-place-metadata-at-beginning-of-mp4-video-using-ffmpeg
            /* Progress write to file
            *************************************************************/
            //echo "\n\r======================================================\n\r";
            //echo "\n\rfileToHls video: \n\r";
            //print_r($video);
            //$convVideo = $welcome->ConvParseData($video);
            //echo "\n\rfileToHls convVideo pathfile:protected: \n\r";
            //print_r($convVideo['pathfile:protected']);
            //print_r($convVideo);
            //$convVideo = $welcome->objectToArray($video);
            //echo "\n\rfileToHls objectToArray: \n\r";
            //print_r($convVideo['pathfile:protected']);
            //print_r($convVideo);
            //echo "\n\rfileToHls videoCopy->pathfile: \n\r";
            //print_r($convVideo['pathfile:protected']);
            //$videoCopy = &$video;
            //print_r($videoCopy->pathfile);
            //echo "\n\rfileToHls format: \n\r";
            //print_r($format);
            //$videoArray = (array) $video;
            //echo "\n\rfileToHls videoArray: \n\r";
            //print_r($videoArray);

            $format->on('progress', function ($video, $format, $percentage) {
                //$format->on('progress', function ($video, $format, $percentage) {
                $pc = "$percentage";
                //global $path_parts;
                //echo "\n\rfileToHls path_parts: \n\r";
                //$path_parts2 = "$path_parts";
                //print_r($path_parts2);
                //print_r($path_parts);
                //echo "\n\rfileToHls video->pathfile: \n\r";
                //print_r($convVideo['pathfile:protected']);
                //print_r($video->pathfile);
                //$videoArray = (array) $video;
                //echo "\n\rfileToHls videoArray: \n\r";
                //print_r($videoArray);
                //print_r($videoArray['pathfile']);
                //print_r($videoArray->pathfile);
                /*echo "\n\rfileToHls getFfmpegPath: \n\r";
                $ff = $this->getFfmpegPath();
                $result = substr($ff, 0, 12);
                print_r($ff);
                echo "\n\rfileToHls getFfmpegPath result: \n\r";
                print_r($result);*/

                //var_dump( (array) $video );
                //$path_parts = pathinfo($fileToHls);
                //echo "$percentage % transcoded";
                //file_put_contents($this->welcome->nadtemp . /!*$path_parts['filename'] .*!/ '.txt', $pc);
                //===file_put_contents($this->welcome->nadtemp . substr($this->getFfmpegPath(), 0, 12) . '.txt', $pc);

                $log = new LogConversion();
                $lastTask = $this->getLastTask();
                /*$log->taskChangeData($lastTask, [
                    "task_id" => $lastTask['task_id'],
                    "task_type" => "fileSendToS3",
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
                ]);*/
                $log->taskChangeData($lastTask, [
                    "percentage" => $pc
                ]);
            });

            $video
                ->save($format, $this->welcome->nadtemp . $path_parts['filename'] . '.m3u8');
            if (empty($lastTask['cover_upload'])) {
                echo "\n\r======================================================\n\r";
                echo "\n\rFfmpegConv fileToHls cover EMPTY\n\r";
                echo "\n\r======================================================\n\r";
                $video
                    ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(3))
                    ->save($this->welcome->nadtemp . $path_parts['filename'] . '.jpg');
            } else {
                echo "\n\r======================================================\n\r";
                echo "\n\rFfmpegConv fileToHls cover NOT EMPTY\n\r";
                echo "\n\r======================================================\n\r";
                //->save($this->welcome->nadtemp . 'pre-image-w320/'  . $path_parts['filename'] . '.jpg');
                rename($this->welcome->nadtemp . 'pre-image-w320/'  . $lastTask['cover_upload'] . '.jpg',
                    $this->welcome->nadtemp . $path_parts['filename'] . '.jpg');
            }
        } catch (Exception $e) {
            echo "\n\r======================================================\n\r";
            echo "\n\rfileToHls save error: " . $e . "\n\r";
            echo "\n\r======================================================\n\r";
            /*rename($welcome->nadtemp . $path_parts['filename'] . '-' . $video_info['height'] . '.' . $path_parts['extension'], $welcome->nadtemp . $lastTask["file"]);*/
            $sendmail = new sendmail();
            $sendmail->SendStaffAlert(['message' => "fileToHls save error: " . $e]);

            $welcome = new NAD();
            $userInfo = $welcome->pgUserInfo($lastTask['owner_id']);
            $sendmail_user = new sendmail();
            $sendmail_user->SendItemUploadError([
                'item_id' => $lastTask['task_item_id'],
                'user_display_name' => $userInfo['user_display_name'],
                'title' => $lastTask['title'],
                'username' => $userInfo['user_email']]);
            exit;
        }
    }
    public function sizeToBandwidth($sizeToBandwidth)
    {
        $this->log->toFile(['service' => 'file', 'type' => '', 'text' => 'sizeToBandwidth ' . $sizeToBandwidth['height']]);

        if ($sizeToBandwidth['height'] > 1020) return 7500000; // 1080
        if ($sizeToBandwidth['height'] > 715 and $sizeToBandwidth['height'] < 1019) return 3000; // 720
        if ($sizeToBandwidth['height'] > 470 and $sizeToBandwidth['height'] < 714) return 1400; // 480
        if ($sizeToBandwidth['height'] > 350 and $sizeToBandwidth['height'] < 469) return 800; // 360
        if ($sizeToBandwidth['height'] > 230 and $sizeToBandwidth['height'] < 349) return 500; // 240

        return 500;
    }
    public function fileToHlsAny($fileToHlsAny, $param)
    {
        echo "\n\rFfmpegConv fileToHls\n";
        print_r($fileToHlsAny);
        //exit;
        $path_parts = pathinfo($fileToHlsAny);
        //exit;
        try {
            $video = $this->ffmpeg->open($fileToHlsAny);
        } catch (Exception $e) {
            echo "\n\rfileToHlsAny ffmpeg->open error: " . $e . "\n\r";
            //$sendmail = new sendmail();
            //$sendmail->SendStaffAlert(['message' => "\n\rfileToHlsAny ffmpeg->open error: " . $e]);
            //exit;
            return $e;
            //return false;
        }
        //$format = new FFMpeg\Format\Video\X264('libmp3lame', 'libx264');
        //-$format = new FFMpeg\Format\Video\X264('libfdk_aac', 'libx264');
        try {
            $format = new FFMpeg\Format\Video\X264('aac', 'libx264');
            $format->setAdditionalParameters(['-hls_list_size', '0']);
            //$format->setAdditionalParameters(['-hls_list_size', '0', '-movflags', '+faststart']); // 19072019 https://superuser.com/questions/802132/how-to-place-metadata-at-beginning-of-mp4-video-using-ffmpeg

            $format
                ->setKiloBitrate($param['BANDWIDTH'])/*->setAudioChannels(2)
                ->setAudioKiloBitrate(256)*/
            ;

            $video
                ->filters()
                ->resize(new FFMpeg\Coordinate\Dimension($param['RESOLUTION_X'], $param['RESOLUTION_Y']))
                ->synchronize();
            $video
                //->save($format, $this->welcome->nadtemp . $path_parts['filename'] . '-' . $param['RESOLUTION_Y'] . '.m3u8');
                ->save($format, $this->welcome->nadtemp . $path_parts['filename'] . '.m3u8');
            /*$video
                ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(3))
                ->save($this->welcome->nadtemp . $path_parts['filename'] . '.jpg');*/
            return true;
        } catch (Exception $e) {
            echo "\n\r======================================================\n\r";
            echo "\n\rfileToHlsAny ffmpeg->save error: " . $e . "\n\r";
            echo "\n\r======================================================\n\r";
            //$path_parts = pathinfo($fileToHlsAny);

            //rename($welcome->nadtemp . $path_parts['filename'] . '-240.' . $path_parts['extension'], $welcome->nadtemp . $lastTask["file"]);
            //$new_filename = preg_replace('-240', '', $path_parts['filename']);
            //rename($fileToHlsAny, $new_filename . '.' . $path_parts['extension']);

            //$sendmail = new sendmail();
            //$sendmail->SendStaffAlert(['message' => "fileToHlsAny ffmpeg->save error: " . $e]);
            //exit;
            return ['error' => $e];
            //return false;
        }

    }
    public function fileToMP4($fileToMP4, $param)
    {
        $this->log->toFile(['service' => 'file', 'type' => '', 'text' => 'fileToMP4 start ' . $fileToMP4]);

        echo "\n\rfileToMP4 fileToMP4\n";
        print_r($fileToMP4);
        //exit;
        $path_parts = pathinfo($fileToMP4);
        //exit;
        try {
            $video = $this->ffmpeg->open($fileToMP4);
        } catch (Exception $e) {
            echo "\n\r======================================================\n\r";
            echo "\n\rfileToMP4 ffmpeg->open error: " . $e . "\n\r";
            echo "\n\r======================================================\n\r";
            //$sendmail = new sendmail();
            //$sendmail->SendStaffAlert(['message' => $e]);
            //exit;
            //return false;
        }
        //$format = new FFMpeg\Format\Video\X264('libmp3lame', 'libx264');
        //-$format = new FFMpeg\Format\Video\X264('libfdk_aac', 'libx264');
        try {
            $format = new FFMpeg\Format\Video\X264('aac', 'libx264');
            //$format = new FFMpeg\Format\Video\X264();
            //$format->setAdditionalParameters(['-hls_list_size', '0']);
            $format->setAdditionalParameters(['-movflags', '+faststart']); // 19072019 https://superuser.com/questions/802132/how-to-place-metadata-at-beginning-of-mp4-video-using-ffmpeg

            $format
                ->setKiloBitrate($param['BANDWIDTH'])/*->setAudioChannels(2)
                ->setAudioKiloBitrate(256)*/
            ;

            $video
                ->filters()
                ->resize(new FFMpeg\Coordinate\Dimension($param['RESOLUTION_X'], $param['RESOLUTION_Y']))
                ->synchronize();

            /*$video
                ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(3))
                ->save($this->welcome->nadtemp . $path_parts['filename'] . '.jpg');*/

            /*$video
                ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(10))
                ->save('frame.jpg');*/
            /*$video
                ->save(new FFMpeg\Format\Video\X264(), 'export-x264.mp4')
                ->save(new FFMpeg\Format\Video\WMV(), 'export-wmv.wmv')
                ->save(new FFMpeg\Format\Video\WebM(), 'export-webm.webm');*/
            $video
                ->save($format, $this->welcome->nadtemp . $path_parts['filename'] . '-' . $param['RESOLUTION_Y'] . '.mp4');
            //->save($format, $this->welcome->nadtemp . $path_parts['filename'] . '-' . $param['RESOLUTION_Y'] . '.' . $path_parts['extension']);
        } catch (Exception $e) {
            echo "\n\r======================================================\n\r";
            echo "\n\rfileToHlsAny ffmpeg->save error: " . $e . "\n\r";
            echo "\n\r======================================================\n\r";
            //$sendmail = new sendmail();
            //$sendmail->SendStaffAlert(['message' => "fileToHlsAny ffmpeg->save error: " . $e]);
            //exit;
            //return false;
        }
        $this->log->toFile(['service' => 'file', 'type' => '', 'text' => 'fileToMP4 return ' . $fileToMP4]);

    }
}