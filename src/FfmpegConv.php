<?php
/**
 * Created by PhpStorm.
 * User: sergey
 * Date: 13.03.18
 * Time: 22:26
 */

namespace VideMe\Ffmpegconversion;


putenv("PATH=/usr/bin");

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
        $this->welcome = new NAD();
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

                $log = new log();
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
            /*$sendmail = new sendmail();
            $sendmail->SendStaffAlert(['message' => "fileToHls save error: " . $e]);

            $welcome = new NAD();
            $userInfo = $welcome->pgUserInfo($lastTask['owner_id']);
            $sendmail_user = new sendmail();
            $sendmail_user->SendItemUploadError([
                'item_id' => $lastTask['task_item_id'],
                'user_display_name' => $userInfo['user_display_name'],
                'title' => $lastTask['title'],
                'username' => $userInfo['user_email']]);*/
            exit;
        }

    }

    public function fileToHls240($fileToHls) // TODO: remove
    {
        echo "\n\rFfmpegConv fileToHls\n";
        print_r($fileToHls);
        //exit;
        $path_parts = pathinfo($fileToHls);
        //exit;
        $video = $this->ffmpeg->open($fileToHls);
        //$format = new FFMpeg\Format\Video\X264('libmp3lame', 'libx264');
        //-$format = new FFMpeg\Format\Video\X264('libfdk_aac', 'libx264');
        $format = new FFMpeg\Format\Video\X264('aac', 'libx264');
        $format->setAdditionalParameters(['-hls_list_size', '0']);
        $video
            ->filters()
            ->resize(new FFMpeg\Coordinate\Dimension(320, 240))
            ->synchronize();
        $video
            ->save($format, $this->welcome->nadtemp . $path_parts['filename'] . '.m3u8');
        $video
            ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(3))
            ->save($this->welcome->nadtemp . $path_parts['filename'] . '.jpg');

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
            /*$sendmail = new sendmail();
            $sendmail->SendStaffAlert(['message' => "\n\rfileToHlsAny ffmpeg->open error: " . $e]);*/
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

            /*$sendmail = new sendmail();
            $sendmail->SendStaffAlert(['message' => "fileToHlsAny ffmpeg->save error: " . $e]);*/
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
            /*$sendmail = new sendmail();
            $sendmail->SendStaffAlert(['message' => $e]);*/
            exit;
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
            /*$sendmail = new sendmail();
            $sendmail->SendStaffAlert(['message' => "fileToHlsAny ffmpeg->save error: " . $e]);*/
            exit;
            //return false;
        }
        $this->log->toFile(['service' => 'file', 'type' => '', 'text' => 'fileToMP4 return ' . $fileToMP4]);

    }

    public function fileToMP4_Only($fileToMP4_Only)
    {
        $this->log->toFile(['service' => 'file', 'type' => '', 'text' => 'fileToMP4_Only start ' . $fileToMP4_Only]);

        echo "\n\rfileToMP4 fileToMP4\n";
        print_r($fileToMP4_Only);
        //exit;
        $path_parts = pathinfo($fileToMP4_Only);
        //exit;
        try {
            $video = $this->ffmpeg->open($fileToMP4_Only);
        } catch (Exception $e) {
            echo "\n\r======================================================\n\r";
            echo "\n\rfileToMP4 ffmpeg->open error: " . $e . "\n\r";
            echo "\n\r======================================================\n\r";
            //$sendmail = new sendmail();
            //$sendmail->SendStaffAlert(['message' => $e]);
            exit;
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
                //->setKiloBitrate($param['BANDWIDTH'])/*->setAudioChannels(2)
                //->setAudioKiloBitrate(256)
            ;

            $video
                ->filters()
                //->resize(new FFMpeg\Coordinate\Dimension($param['RESOLUTION_X'], $param['RESOLUTION_Y']))
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
                //->save($format, $this->welcome->nadtemp . $path_parts['filename'] . '-' . $param['RESOLUTION_Y'] . '.mp4');
                //->save($format, $this->welcome->nadtemp . $path_parts['filename'] . '_force.mp4');
                ->save($format, $this->welcome->nadtemp . $path_parts['filename'] . '.mp4');
            //->save($format, $this->welcome->nadtemp . $path_parts['filename'] . '-' . $param['RESOLUTION_Y'] . '.' . $path_parts['extension']);
        } catch (Exception $e) {
            echo "\n\r======================================================\n\r";
            echo "\n\rfileToHlsAny ffmpeg->save error: " . $e . "\n\r";
            echo "\n\r======================================================\n\r";
            /*$sendmail = new sendmail();
            $sendmail->SendStaffAlert(['message' => "fileToHlsAny ffmpeg->save error: " . $e]);*/
            exit;
            //return false;
        }
        $this->log->toFile(['service' => 'file', 'type' => '', 'text' => 'fileToMP4_Only return ' . $fileToMP4_Only]);

    }


    public function fileToMP4prev_video5sec($fileToMP4_Only, $param) // TODO: Test
    {
        echo "\n\rfileToMP4prev_video5sec\n";
        print_r($fileToMP4_Only);
        //exit;
        //$path_parts = pathinfo($fileToMP4_Only);
        //exit;
        try {
            //$video = $this->ffmpeg->open($fileToMP4_Only);
            $ffmpeg = $this->ffmpeg;
        } catch (Exception $e) {
            echo "\n\r======================================================\n\r";
            echo "\n\rfileToMP4prev_video5sec ffmpeg->open error: " . $e . "\n\r";
            echo "\n\r======================================================\n\r";
            //$sendmail = new sendmail();
            //$sendmail->SendStaffAlert(['message' => $e]);
            exit;
            //return false;
        }
        //$format = new FFMpeg\Format\Video\X264('libmp3lame', 'libx264');
        //-$format = new FFMpeg\Format\Video\X264('libfdk_aac', 'libx264');
        try {
            $format = new FFMpeg\Format\Video\X264('aac', 'libx264');
            //$format = new FFMpeg\Format\Video\X264();
            //$format->setAdditionalParameters(['-hls_list_size', '0']);
            //===$format->setAdditionalParameters(['-movflags', '+faststart']); // 19072019 https://superuser.com/questions/802132/how-to-place-metadata-at-beginning-of-mp4-video-using-ffmpeg
            //--$format->setAdditionalParameters(['-filter_complex "[0:v]trim=start=3:end=5,setpts=PTS-STARTPTS[a]; [0:v]trim=start=23:end=25,setpts=PTS-STARTPTS[b]; [a][b]concat[c];  [0:v]trim=start=63:end=65,setpts=PTS-STARTPTS[d]; [0:v]trim=start=83:end=85,setpts=PTS-STARTPTS[f]; [d][f]concat[g]; [c][g]concat[out1]"', '-map [out1]']);
            $format
                //->setKiloBitrate($param['BANDWIDTH'])/*->setAudioChannels(2)
                //->setAudioKiloBitrate(256)
            ;
            //--$customFilter = '-an';
            //--$format->addFilter(new FFMpeg\Filters\Audio\SimpleFilter([$customFilter]));
            /*===$video
                ->filters()
                ->resize(new FFMpeg\Coordinate\Dimension($param['RESOLUTION_X'], $param['RESOLUTION_Y']))
                ->synchronize();*/
            /*->custom('[0:v]trim=start=3:end=5', 'setpts=PTS-STARTPTS[a]')
            ->custom('[0:v]trim=start=23:end=25', 'setpts=PTS-STARTPTS[b]')
            ->custom('[a][b]concat[c]')
            ->custom('[0:v]trim=start=63:end=65', 'setpts=PTS-STARTPTS[d]')
            ->custom('[0:v]trim=start=83:end=85', 'setpts=PTS-STARTPTS[f]')
            ->custom('[d][f]concat[g]')
            ->custom('[c][g]concat[out1]')
            ->custom('-map [out1]');*/
            //$video->filters()->clip(FFMpeg\Coordinate\TimeCode::fromSeconds(0), FFMpeg\Coordinate\TimeCode::fromSeconds(5));
            //$video->filters()->clip(FFMpeg\Coordinate\TimeCode::fromSeconds(11), FFMpeg\Coordinate\TimeCode::fromSeconds(22));
            /*$video->filters()->clip(FFMpeg\Coordinate\TimeCode::fromSeconds(7), FFMpeg\Coordinate\TimeCode::fromSeconds(8));
            $video->filters()->clip(FFMpeg\Coordinate\TimeCode::fromSeconds(17), FFMpeg\Coordinate\TimeCode::fromSeconds(18));
            $video->filters()->clip(FFMpeg\Coordinate\TimeCode::fromSeconds(27), FFMpeg\Coordinate\TimeCode::fromSeconds(28));
            $video->filters()->clip(FFMpeg\Coordinate\TimeCode::fromSeconds(37), FFMpeg\Coordinate\TimeCode::fromSeconds(38));*/
            /*$video->filters()->clip(FFMpeg\Coordinate\TimeCode::fromSeconds(7), FFMpeg\Coordinate\TimeCode::fromSeconds(1));
            $video->filters()->clip(FFMpeg\Coordinate\TimeCode::fromSeconds(17), FFMpeg\Coordinate\TimeCode::fromSeconds(1));
            $video->filters()->clip(FFMpeg\Coordinate\TimeCode::fromSeconds(27), FFMpeg\Coordinate\TimeCode::fromSeconds(1));
            $video->filters()->clip(FFMpeg\Coordinate\TimeCode::fromSeconds(37), FFMpeg\Coordinate\TimeCode::fromSeconds(1));*/
            /*$video->filters()->clip(FFMpeg\Coordinate\TimeCode::fromSeconds($param['clip1']), FFMpeg\Coordinate\TimeCode::fromSeconds($param['clip1']));
            $video->filters()->clip(FFMpeg\Coordinate\TimeCode::fromSeconds($param['clip2']), FFMpeg\Coordinate\TimeCode::fromSeconds($param['clip2']));
            $video->filters()->clip(FFMpeg\Coordinate\TimeCode::fromSeconds($param['clip3']), FFMpeg\Coordinate\TimeCode::fromSeconds($param['clip3']));
            $video->filters()->clip(FFMpeg\Coordinate\TimeCode::fromSeconds($param['clip4']), FFMpeg\Coordinate\TimeCode::fromSeconds($param['clip4']));
            $video->filters()->clip(FFMpeg\Coordinate\TimeCode::fromSeconds($param['clip5']), FFMpeg\Coordinate\TimeCode::fromSeconds($param['clip5']));*/
            //$video->filters()->resize(new FFMpeg\Coordinate\Dimension(320, 240), FFMpeg\Filters\Video\ResizeFilter::RESIZEMODE_INSET, true);
            //$video->filters()->removeAudio();
            /*$video
                ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(3))
                ->save($this->welcome->nadtemp . $path_parts['filename'] . '.jpg');*/

            /*$video
                ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(10))
                ->save('frame.jpg');*/
            //$video
            /*$ffmpeg = FFMpeg\FFMpeg::create();
            $ffmpeg->open($fileToMP4_Only)
            //$format
                ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(3))
                ->addFilter(new CustomFrameFilter('scale=320x160')) //resize output frame image
                ->save($this->welcome->nadtemp . $path_parts['filename'] . '-test.jpg');*/
            /*$video
                ->save(new FFMpeg\Format\Video\X264(), 'export-x264.mp4')
                ->save(new FFMpeg\Format\Video\WMV(), 'export-wmv.wmv')
                ->save(new FFMpeg\Format\Video\WebM(), 'export-webm.webm');*/
            /*===$video
                //->save($format, $this->welcome->nadtemp . $path_parts['filename'] . '-' . $param['RESOLUTION_Y'] . '.mp4');
                //->save($format, $this->welcome->nadtemp . $path_parts['filename'] . '_force.mp4');
                ->save($format, $this->welcome->nadtemp . $path_parts['filename'] . '-test.mp4');*/
            //->save($format, $this->welcome->nadtemp . $path_parts['filename'] . '-' . $param['RESOLUTION_Y'] . '.' . $path_parts['extension']);
            //$advancedMedia = $ffmpeg->openAdvanced(array('video_1.mp4', 'video_2.mp4'));
            //$ffmpeg = FFMpeg\FFMpeg::create();
            /*$ffmpeg = FFMpeg\FFMpeg::create(array( // TODO: WHY?
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
            ));*/
            //$advancedMedia = $ffmpeg->openAdvanced(array($fileToMP4_Only));
            $advancedMedia = $ffmpeg->openAdvanced([$fileToMP4_Only]);
            /*$video
                ->filters()
                ->resize(new FFMpeg\Coordinate\Dimension($param['RESOLUTION_X'], $param['RESOLUTION_Y']))
                ->synchronize();*/
            //$advancedMedia->addFilter(new FFMpeg\Filters\Audio\SimpleFilter(['-an']));
            //$advancedMedia->addFilter(new FFMpeg\Media\Audio('-an'));
            $advancedMedia->filters()
                //->custom('[0:v][1:v]', 'hstack', '[v]');
                ->custom('[0:v]', 'trim=start=' . $param['clip1'] . ':end=' . ($param['clip1'] + 1) . ',setpts=PTS-STARTPTS', '[a]')
                ->custom('[0:v]', 'trim=start=' . $param['clip2'] . ':end=' . ($param['clip2'] + 1) . ',setpts=PTS-STARTPTS', '[b]')
                ->custom('[a][b]', 'concat', '[c]')
                ->custom('[0:v]', 'trim=start=' . $param['clip3'] . ':end=' . ($param['clip3'] + 1) . ',setpts=PTS-STARTPTS', '[d]')
                ->custom('[0:v]', 'trim=start=' . $param['clip4'] . ':end=' . ($param['clip4'] + 1) . ',setpts=PTS-STARTPTS', '[e]')
                ->custom('[d][e]', 'concat', '[f]')
                ->custom('[0:v]', 'trim=start=' . $param['clip5'] . ':end=' . ($param['clip5'] + 1) . ',setpts=PTS-STARTPTS', '[g]')
                ->custom('[g][f]', 'concat', '[h]')
                ->custom('[c][h]', 'concat', '[out1]');
            $advancedMedia
                //->map(array('0:a', '[v]'), new X264('aac', 'libx264'), 'output.mp4')
                //->map(array('0:a', '[v]'), new X264('aac', 'libx264'), $this->welcome->nadtemp . $path_parts['filename'] . '-test.mp4')
                //->map(array('[out1]'), $format, $this->welcome->nadtemp . $path_parts['filename'] . '-test-5sec.mp4')
                //==->map(array('[out1]'), $format, $this->welcome->nadtemp . 'pre-video-w320/' . $path_parts['filename'] . '-5sec.mp4')
                ->map(array('[out1]'), $format, $this->welcome->nadtemp . 'pre-video-w320/' . $param['filename'] . '-5sec.mp4')
                ->save();
        } catch (Exception $e) {
            echo "\n\r======================================================\n\r";
            echo "\n\rfileToMP4prev_video5sec ffmpeg->save error: " . $e . "\n\r";
            echo "\n\r======================================================\n\r";
            //$sendmail = new sendmail();
            //===$sendmail->SendStaffAlert(['message' => "fileToMP4prev_video5sec ffmpeg->save error: " . $e]);
            exit;
            //return false;
        }
    }

    public function fileToMP4prev_video320an($fileToMP4_Only, $param) // TODO: Test
    {
        echo "\n\rfileToMP4prev_video320an fileToMP4\n\r";
        print_r($fileToMP4_Only);
        //exit;
        //$path_parts = pathinfo($fileToMP4_Only);
        //exit;
        try {
            $video = $this->ffmpeg->open($fileToMP4_Only);
            //$ffmpeg = $this->ffmpeg;
        } catch (Exception $e) {
            echo "\n\r======================================================\n\r";
            echo "\n\rfileToMP4prev_video320an ffmpeg->open error: " . $e . "\n\r";
            echo "\n\r======================================================\n\r";
            //$sendmail = new sendmail();
            //$sendmail->SendStaffAlert(['message' => $e]);
            exit;
            //return false;
        }
        //$format = new FFMpeg\Format\Video\X264('libmp3lame', 'libx264');
        //-$format = new FFMpeg\Format\Video\X264('libfdk_aac', 'libx264');
        try {
            $format = new FFMpeg\Format\Video\X264('aac', 'libx264');
            //$format = new FFMpeg\Format\Video\X264();
            //$format->setAdditionalParameters(['-hls_list_size', '0']);
            $format->setAdditionalParameters(['-movflags', '+faststart']); // 19072019 https://superuser.com/questions/802132/how-to-place-metadata-at-beginning-of-mp4-video-using-ffmpeg
            //--$format->setAdditionalParameters(['-filter_complex "[0:v]trim=start=3:end=5,setpts=PTS-STARTPTS[a]; [0:v]trim=start=23:end=25,setpts=PTS-STARTPTS[b]; [a][b]concat[c];  [0:v]trim=start=63:end=65,setpts=PTS-STARTPTS[d]; [0:v]trim=start=83:end=85,setpts=PTS-STARTPTS[f]; [d][f]concat[g]; [c][g]concat[out1]"', '-map [out1]']);
            $format
                //->setKiloBitrate($param['BANDWIDTH'])//*->setAudioChannels(2)
                ->setKiloBitrate(500)//*->setAudioChannels(2)
                //->setAudioKiloBitrate(256)
            ;
            //--$customFilter = '-an';
            //--$format->addFilter(new FFMpeg\Filters\Audio\SimpleFilter([$customFilter]));
            $video
                ->filters()
                ->resize(new FFMpeg\Coordinate\Dimension($param['RESOLUTION_X'], $param['RESOLUTION_Y']))
                ->synchronize();
            /*->custom('[0:v]trim=start=3:end=5', 'setpts=PTS-STARTPTS[a]')
            ->custom('[0:v]trim=start=23:end=25', 'setpts=PTS-STARTPTS[b]')
            ->custom('[a][b]concat[c]')
            ->custom('[0:v]trim=start=63:end=65', 'setpts=PTS-STARTPTS[d]')
            ->custom('[0:v]trim=start=83:end=85', 'setpts=PTS-STARTPTS[f]')
            ->custom('[d][f]concat[g]')
            ->custom('[c][g]concat[out1]')
            ->custom('-map [out1]');*/
            //$video->filters()->clip(FFMpeg\Coordinate\TimeCode::fromSeconds(0), FFMpeg\Coordinate\TimeCode::fromSeconds(5));
            //$video->filters()->clip(FFMpeg\Coordinate\TimeCode::fromSeconds(11), FFMpeg\Coordinate\TimeCode::fromSeconds(22));
            /*$video->filters()->clip(FFMpeg\Coordinate\TimeCode::fromSeconds(7), FFMpeg\Coordinate\TimeCode::fromSeconds(8));
            $video->filters()->clip(FFMpeg\Coordinate\TimeCode::fromSeconds(17), FFMpeg\Coordinate\TimeCode::fromSeconds(18));
            $video->filters()->clip(FFMpeg\Coordinate\TimeCode::fromSeconds(27), FFMpeg\Coordinate\TimeCode::fromSeconds(28));
            $video->filters()->clip(FFMpeg\Coordinate\TimeCode::fromSeconds(37), FFMpeg\Coordinate\TimeCode::fromSeconds(38));*/
            /*$video->filters()->clip(FFMpeg\Coordinate\TimeCode::fromSeconds(7), FFMpeg\Coordinate\TimeCode::fromSeconds(1));
            $video->filters()->clip(FFMpeg\Coordinate\TimeCode::fromSeconds(17), FFMpeg\Coordinate\TimeCode::fromSeconds(1));
            $video->filters()->clip(FFMpeg\Coordinate\TimeCode::fromSeconds(27), FFMpeg\Coordinate\TimeCode::fromSeconds(1));
            $video->filters()->clip(FFMpeg\Coordinate\TimeCode::fromSeconds(37), FFMpeg\Coordinate\TimeCode::fromSeconds(1));*/
            /*$video->filters()->clip(FFMpeg\Coordinate\TimeCode::fromSeconds($param['clip1']), FFMpeg\Coordinate\TimeCode::fromSeconds($param['clip1']));
            $video->filters()->clip(FFMpeg\Coordinate\TimeCode::fromSeconds($param['clip2']), FFMpeg\Coordinate\TimeCode::fromSeconds($param['clip2']));
            $video->filters()->clip(FFMpeg\Coordinate\TimeCode::fromSeconds($param['clip3']), FFMpeg\Coordinate\TimeCode::fromSeconds($param['clip3']));
            $video->filters()->clip(FFMpeg\Coordinate\TimeCode::fromSeconds($param['clip4']), FFMpeg\Coordinate\TimeCode::fromSeconds($param['clip4']));
            $video->filters()->clip(FFMpeg\Coordinate\TimeCode::fromSeconds($param['clip5']), FFMpeg\Coordinate\TimeCode::fromSeconds($param['clip5']));*/
            //$video->filters()->resize(new FFMpeg\Coordinate\Dimension(320, 240), FFMpeg\Filters\Video\ResizeFilter::RESIZEMODE_INSET, true);
            //$video->filters()->removeAudio();
            /*$video
                ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(3))
                ->save($this->welcome->nadtemp . $path_parts['filename'] . '.jpg');*/

            /*$video
                ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(10))
                ->save('frame.jpg');*/
            //$video
            /*$ffmpeg = FFMpeg\FFMpeg::create();
            $ffmpeg->open($fileToMP4_Only)
            //$format
                ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(3))
                ->addFilter(new CustomFrameFilter('scale=320x160')) //resize output frame image
                ->save($this->welcome->nadtemp . $path_parts['filename'] . '-test.jpg');*/
            /*$video
                ->save(new FFMpeg\Format\Video\X264(), 'export-x264.mp4')
                ->save(new FFMpeg\Format\Video\WMV(), 'export-wmv.wmv')
                ->save(new FFMpeg\Format\Video\WebM(), 'export-webm.webm');*/
            /*$video
                ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(0))
                ->save($this->welcome->nadtemp . $path_parts['filename'] . '-test.jpg');*/
            $video
                ->addFilter(new FFMpeg\Filters\Audio\SimpleFilter(['-an'])) // TODO: remove?
                //->save($format, $this->welcome->nadtemp . $path_parts['filename'] . '-' . $param['RESOLUTION_Y'] . '.mp4');
                //->save($format, $this->welcome->nadtemp . $path_parts['filename'] . '_force.mp4');
                ->save($format, $this->welcome->nadtemp . 'pre-video-w320/' . $param['filename'] . '-pre-v-w320.mp4');
            //->save($format, $this->welcome->nadtemp . $path_parts['filename'] . '-' . $param['RESOLUTION_Y'] . '.' . $path_parts['extension']);
            //$advancedMedia = $ffmpeg->openAdvanced(array('video_1.mp4', 'video_2.mp4'));
            //$ffmpeg = FFMpeg\FFMpeg::create();
            /*$ffmpeg = FFMpeg\FFMpeg::create(array( // TODO: WHY?
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
            ));*/
            /*$advancedMedia = $ffmpeg->openAdvanced(array($fileToMP4_Only));
            $video
                ->filters()
                ->resize(new FFMpeg\Coordinate\Dimension($param['RESOLUTION_X'], $param['RESOLUTION_Y']))
                ->synchronize();*/
            //$advancedMedia->addFilter(new FFMpeg\Filters\Audio\SimpleFilter(['-an']));
            //$advancedMedia->addFilter(new FFMpeg\Media\Audio('-an'));
            /*$advancedMedia->filters()
                //->custom('[0:v][1:v]', 'hstack', '[v]');
                ->custom('[0:v]', 'trim=start=' . $param['clip1'] . ':end=' . ( $param['clip1'] + 1 ) . ',setpts=PTS-STARTPTS', '[a]')
                ->custom('[0:v]', 'trim=start=' . $param['clip2'] . ':end=' . ( $param['clip2'] + 1 ) . ',setpts=PTS-STARTPTS', '[b]')
                ->custom('[a][b]', 'concat', '[c]')
                ->custom('[0:v]', 'trim=start=' . $param['clip3'] . ':end=' . ( $param['clip3'] + 1 ) . ',setpts=PTS-STARTPTS', '[d]')
                ->custom('[0:v]', 'trim=start=' . $param['clip4'] . ':end=' . ( $param['clip4'] + 1 ) . ',setpts=PTS-STARTPTS', '[e]')
                ->custom('[d][e]', 'concat', '[f]')
                ->custom('[0:v]', 'trim=start=' . $param['clip5'] . ':end=' . ( $param['clip5'] + 1 ) . ',setpts=PTS-STARTPTS', '[g]')
                ->custom('[g][f]', 'concat', '[h]')
                ->custom('[c][h]', 'concat', '[out1]');
            $advancedMedia
                //->map(array('0:a', '[v]'), new X264('aac', 'libx264'), 'output.mp4')
                //->map(array('0:a', '[v]'), new X264('aac', 'libx264'), $this->welcome->nadtemp . $path_parts['filename'] . '-test.mp4')
                ->map(array('[out1]'), $format, $this->welcome->nadtemp . $path_parts['filename'] . '-test-5sec.mp4')
                ->save();*/
        } catch (Exception $e) {
            echo "\n\r======================================================\n\r";
            echo "\n\rfileToMP4prev_video320an ffmpeg->save error: " . $e . "\n\r";
            echo "\n\r======================================================\n\r";
            $sendmail = new sendmail();
            //===$sendmail->SendStaffAlert(['message' => "fileToMP4prev_video320an ffmpeg->save error: " . $e]);
            exit;
            //return false;
        }
    }

    public function fileToMP4prev_image($fileToMP4_Only, $param)
    {
        echo "\n\rfileToMP4prev_image\n\r";
        print_r($fileToMP4_Only);
        $path_parts = pathinfo($fileToMP4_Only);
        try {
            $video = $this->ffmpeg->open($fileToMP4_Only);
            //$video = $this->ffmpeg->open($this->welcome->nadtemp . $path_parts['filename'] . '-test.mp4');
        } catch (Exception $e) {
            echo "\n\r======================================================\n\r";
            echo "\n\rfileToMP4prev_image ffmpeg->open error: " . $e . "\n\r";
            echo "\n\r======================================================\n\r";
            //$sendmail = new sendmail();
            //$sendmail->SendStaffAlert(['message' => $e]);
            exit;
        }
        try {
            $video
                ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(0))
                //->save($this->welcome->nadtemp . $path_parts['filename'] . '-test.jpg');
                //->save($this->welcome->nadtemp . 'pre-image-w320/' . $path_parts['filename'] . '-pre-i-w320.jpg');
                ->save($this->welcome->nadtemp . 'pre-image-w320/' . $param['filename'] . '-pre-i-w320.jpg');
            /*$video
                ->filters()
                ->resize(new FFMpeg\Coordinate\Dimension($param['RESOLUTION_X'], $param['RESOLUTION_Y']))
                ->synchronize();
            $frame = $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(3));
            $frame->save($this->welcome->nadtemp . $path_parts['filename'] . '-test.jpg');*/
        } catch (Exception $e) {
            echo "\n\r======================================================\n\r";
            echo "\n\rfileToMP4prev_image ffmpeg->save error: " . $e . "\n\r";
            echo "\n\r======================================================\n\r";
            $sendmail = new sendmail();
            $sendmail->SendStaffAlert(['message' => "fileToMP4prev_image ffmpeg->save error: " . $e]);
            exit;
        }
    }
    public function fileToMP4_get_image($fileToMP4_Only) // TODO: Test 08082021
    {
        //echo "\n\rfileToMP4_get_image\n\r";
        //print_r($fileToMP4_Only);
        //$path_parts = pathinfo($fileToMP4_Only);
        $path_parts = pathinfo($this->welcome->nadtemp . $fileToMP4_Only['file']);

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
                //->save($_SERVER['DOCUMENT_ROOT'] . '/pre-image-w320/'  . $frae_name . '.jpg');
                copy($this->welcome->nadtemp . 'pre-image-w320/'  . $frae_name . '.jpg',
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
                $sendmail = new sendmail();
                $sendmail->SendStaffAlert(['message' => "fileToMP4_get_image ffmpeg->save error: " . $e]);
                exit;
            }
        }
        return $retArray;
    }

    public function fileToMP4thumb_sprite($fileToMP4_Only, $param) // TODO: Test ; delete
    {
        echo "\n\r======================================================\n\r";
        echo "fileToMP4thumb_sprite\n\r";
        print_r($fileToMP4_Only);
        echo "\n\rfileToMP4thumb_sprite param\n\r";
        print_r($param);
        echo "\n\r======================================================\n\r";
        $path_parts = pathinfo($fileToMP4_Only);
        //$welcome = new NAD();
        //try {
        //$mtnExp = "sudo mtn -O '/usr/share/nginx/nadtemp/thumb-image-56-w120' -o '.jpg' -a 1.76 -j 70 -s 6 -c 6 -r 5 -h 58 -w 720 -b 2 -t -i -f '/usr/share/fonts/truetype/liberation/LiberationMono-Regular.ttf' '" . $fileToMP4_Only . "' 2>&1 > /dev/null 2>&1 &'";
        $mtnExp = "mtn -O '/usr/share/nginx/nadtemp/thumb-sprite-56-w120' -o '.jpg' -a 1.76 -j 70 -s " . $param['time_step'] . " -c 6 -r 5 -h 58 -w 720 -b 2 -t -i -f '/usr/share/fonts/truetype/liberation/LiberationMono-Regular.ttf' '" . $fileToMP4_Only . "' 2>&1";
        //==$mtnExp = "ls";
        //$mtnExp = "mtn 2>&1 > /dev/null 2>&1 &";
        //==$mtnExp = "mtn 2>&1";
        //print_r($mtnExp);
        $mtnRes = shell_exec($mtnExp);
        //$mtnRes = exec($mtnExp);
        print_r($mtnRes);
        //$resRename = rename($welcome->nadtemp . $path_parts['filename'] . '.jpg', $welcome->nadtemp . $path_parts['filename'] . '.jpg');
        $resRename = rename($this->welcome->nadtemp . $path_parts['filename'] . '.jpg', $this->welcome->nadtemp . $param['filename'] . '.jpg');
        echo "\n\r======================================================\n\r";
        echo "fileToMP4thumb_image resRename\n\r";
        print_r($resRename);

        /* work
         * exec($mtnExp, $out, $status);
        if (0 === $status) {
            var_dump($out);
        } else {
            echo "\n\r======================================================\n\r";
            var_dump($out);
            echo "Command failed with status: $status";
        }*/
        /*} catch (Exception $e) {
            echo "\n\r======================================================\n\r";
            echo "\n\rfileToMP4thumb_image ffmpeg->open error: " . $e . "\n\r";
            echo "\n\r======================================================\n\r";
            //$sendmail = new sendmail();
            //$sendmail->SendStaffAlert(['message' => $e]);
            exit;
        }*/
    }

    public function fileToMP4_PHP_thumb_sprite($fileToMP4_Only, $param) // TODO: Test
    {
        echo "\n\r======================================================\n\r";
        echo "fileToMP4thumb_sprite\n\r";
        print_r($fileToMP4_Only);
        echo "\n\rfileToMP4thumb_sprite param\n\r";
        print_r($param);
        echo "\n\r======================================================\n\r";
        //$path_parts = pathinfo($fileToMP4_Only);
        //$welcome = new NAD();
        //use Emgag\Video\ThumbnailSprite\ThumbnailSprite;

        /*$sprite = new ThumbnailSprite();
        //$sprite = Emgag\Video\ThumbnailSprite\ThumbnailSprite::generate([]);
        //$ret = $sprite->setSource('path-to-source-video.mp4')
        $ret = $sprite->setSource($fileToMP4_Only)
            //->setOutputDirectory('dir-to-store-sprite-and-vtt')
            // filename prefix for image sprite and WebVTT file (defaults to "sprite", resulting in "sprite.jpg" and "sprite.vtt")
            ->setPrefix('sprite')
            // absolute URL of sprite image or relative to where the WebVTT file is stored
            ->setUrlPrefix('http://example.org/sprites')
            // sampling rate in seconds
            ->setRate(10)
            // minimum number of images (will modify sampling rate accordingly if it would result in fewer images than this)
            ->setMinThumbs(20)
            // width of a single thumbnail in px
            ->setWidth(120)
            ->generate();*/

        $sprite = new ThumbnailSprite();
//$sprite = Emgag\Video\ThumbnailSprite\ThumbnailSprite::generate([]);
//$ret = $sprite->setSource('path-to-source-video.mp4')
        $ret = $sprite->setSource($fileToMP4_Only)
            ->setOutputDirectory('/usr/share/nginx/nadtemp/sprite-w120')
            // filename prefix for image sprite and WebVTT file (defaults to "sprite", resulting in "sprite.jpg" and "sprite.vtt")
            //->setPrefix('sprite')
            ->setPrefix($param['filename'] . '-spr-w120')
            // absolute URL of sprite image or relative to where the WebVTT file is stored
            //->setUrlPrefix('http://example.org/sprites')
            //===->setUrlPrefix('https://s3.amazonaws.com/sprite-w120.vide.me')
            // sampling rate in seconds
            //==->setRate(10)
            //->setRate(6)
            //===->setRate($param['time_step'])
            // minimum number of images (will modify sampling rate accordingly if it would result in fewer images than this)
            ->setMinThumbs(30)
            // width of a single thumbnail in px
            ->setWidth(120)
            ->generate()/**/
        ;

        print_r($ret);
        return $ret;

// $ret = ['vttFile' => 'path-to-vtt-file', 'sprite' => 'path-to-sprite-file']

    }

    public function fileTo_pre_video_image_sprite($fileTo_pre_video_image_sprite)
    {
        $this->log->toFile(['service' => 'file', 'type' => '', 'text' => 'fileTo_pre_video_image_sprite start ' . $fileTo_pre_video_image_sprite['file']]);

        //$welcome = new NAD();
        $path_parts = pathinfo($fileTo_pre_video_image_sprite["file"]);

        //$video_info = $ffmpegConv->getVideoInfo($welcome->nadtemp . $_REQUEST["file"]);
        $video_info = $this->getVideoInfo($this->welcome->nadtemp . $fileTo_pre_video_image_sprite["file"] . '-240.mp4');
        //$video_info['video_duration'] = $ffmpegConv->getVideoDuration($welcome->nadtemp . $_REQUEST["file"]);
        if (empty($video_info)) {
            echo "\n\r======================================================\n\r";
            echo "\n\rfile not found: \n\r";
            echo $this->welcome->nadtemp . $fileTo_pre_video_image_sprite["file"] . '-240.mp4';
            $this->log->toFile(['service' => 'fileTo_pre_video_image_sprite', 'type' => 'error', 'text' => 'file not found: ' . $this->welcome->nadtemp . $fileTo_pre_video_image_sprite["file"] . '-240.mp4']);
            $sendmail = new sendmail();
            $sendmail->SendStaffAlert(['message' => "file not found: " . $this->welcome->nadtemp . $fileTo_pre_video_image_sprite["file"] . '-240.mp4']);
            echo "\n\r======================================================\n\r";
            //exit;
        }
        $video_info['video_duration'] = $this->getVideoDuration($this->welcome->nadtemp . $fileTo_pre_video_image_sprite["file"] . '-240.mp4');
        echo "\n\r======================================================\n\r";
        echo "\n\rfileToMP4prev video_info: \n\r";
        print_r($video_info);

        // 640 x 480 = 480 / ( 640 / 320 ) = 320 x 240
        // $param['RESOLUTION_X'], $param['RESOLUTION_Y']
        $param['filename'] = $path_parts['filename'];
        $param['RESOLUTION_X'] = 320;
        $param['RESOLUTION_Y'] = round($video_info['height'] / ($video_info['width'] / 320));
        //$param['RESOLUTION_Y'] = round($video_info['height'] / ( $video_info['width'] / 320), 0, PHP_ROUND_HALF_EVEN);
        if ($param['RESOLUTION_Y'] % 2 == 1) {
            // odd or even
            $param['RESOLUTION_Y'] = $param['RESOLUTION_Y'] - 1;
        }
        echo "\n\r======================================================\n\r";
        echo "\n\rfileToMP4prev param: \n\r";
        print_r($param);

        // clip 3, middle-1middle, middle, middle-2middle, -3 sec video
        //     [video_duration] => 187
        // 0 |<--- 3 ----------- 47 ------------ 94 ------------- 140 ------ -12 ----->| 187
        $param['clip1'] = 3;
        $middle = $video_info['video_duration'] / 2;
        $param['clip2'] = round($middle / 2);
        $param['clip3'] = round($middle);
        $middle2 = $video_info['video_duration'] - $middle;
        $param['clip4'] = round($middle + $middle2 / 2);
        if ($video_info['video_duration'] - $param['clip4'] > 12) {
            $param['clip5'] = $video_info['video_duration'] - 12;
        } else {
            $param['clip5'] = $param['clip4'] + 1;
        }

        $param['time_step'] = round($video_info['video_duration'] / 30);
        //$middle3 = $middle - $middle2;
        //$param['clip5'] = round($middle2 + $middle3 / 2);
        //$param['clip5'] = round($middle2 + $middle3);
        echo "\n\r======================================================\n\r";
        echo "\n\rfileTo_pre_video_image_sprite middle: $middle \n\r";
        echo "\n\r======================================================\n\r";
        echo "\n\rfileTo_pre_video_image_sprite middle2: $middle2\n\r";
        echo "\n\r======================================================\n\r";
        echo "\n\rfileTo_pre_video_image_sprite param: \n\r";
        print_r($param);
        //exit;


        //$ffmpegConv->fileToMP4prev_video320an($welcome->nadtemp . $_REQUEST["file"], $param);
        //==$ffmpegConv->fileToMP4prev_video5sec($welcome->nadtemp . $_REQUEST["file"], $param);
        $this->fileToMP4prev_video5sec($this->welcome->nadtemp . $fileTo_pre_video_image_sprite["file"] . '-240.mp4', $param);
        //$ffmpegConv->fileToMP4prev_video5sec($welcome->nadtemp . $path_parts['filename'] . '-test-320an.mp4', $param);
        //==$ffmpegConv->fileToMP4prev_video320an($welcome->nadtemp . 'pre-video-w320/' . $path_parts['filename'] . '-5sec.mp4', $param);
        $this->fileToMP4prev_video320an($this->welcome->nadtemp . 'pre-video-w320/' . $fileTo_pre_video_image_sprite["file"] . '-5sec.mp4', $param);
        //$ffmpegConv->fileToMP4prev_image($welcome->nadtemp . $_REQUEST['file'], $param);
        //$ffmpegConv->fileToMP4prev_image($welcome->nadtemp . $path_parts['filename'] . '-test-320an.mp4', $param);
        //==$ffmpegConv->fileToMP4prev_image($welcome->nadtemp . 'pre-video-w320/' . $path_parts['filename'] . '.mp4');
        $lastTask = $this->getLastTask();
        echo "\n\rfileTo_pre_video_image_sprite lastTask: \n\r";
        print_r($lastTask);
        if (empty($lastTask['cover_upload'])) {
            echo "\n\r======================================================\n\r";
            echo "\n\rfileTo_pre_video_image_sprite cover_upload EMPTY\n\r";
            echo "\n\r======================================================\n\r";
            $this->fileToMP4prev_image($this->welcome->nadtemp . 'pre-video-w320/' . $fileTo_pre_video_image_sprite["file"] . '-pre-v-w320.mp4', $param);
        } else {
            echo "\n\r======================================================\n\r";
            echo "\n\rfileTo_pre_video_image_sprite cover_upload NOT EMPTY\n\r";
            echo "\n\r======================================================\n\r";
            //->save($this->welcome->nadtemp . 'pre-image-w320/'  . $path_parts['filename'] . '.jpg');
            //->save($this->welcome->nadtemp . 'pre-image-w320/' . $param['filename'] . '-pre-i-w320.jpg');

            copy($this->welcome->nadtemp . $lastTask['task_item_id'] . '.jpg',
                $this->welcome->nadtemp . 'pre-image-w320/' . $path_parts['filename'] . '-pre-i-w320.jpg');
        }
        /*if ($video_info['video_duration'] > 30) {
            $this->fileToMP4thumb_sprite($this->welcome->nadtemp . $fileTo_pre_video_image_sprite["file"] . '-240.mp4', $param);
        }*/
        $this->fileToMP4_PHP_thumb_sprite($this->welcome->nadtemp . $fileTo_pre_video_image_sprite["file"] . '-240.mp4', $param);
        $this->log->toFile(['service' => 'file', 'type' => '', 'text' => 'fileTo_pre_video_image_sprite return ' . $fileTo_pre_video_image_sprite['file']]);

        return true;
    }

    public function fileTo_sprite_Only($fileTo_sprite_Only)
    {
        //$welcome = new NAD();
        $path_parts = pathinfo($fileTo_sprite_Only["file"]);

        //$video_info = $ffmpegConv->getVideoInfo($welcome->nadtemp . $_REQUEST["file"]);
        $video_info = $this->getVideoInfo($this->welcome->nadtemp . $fileTo_sprite_Only["file"] . '-240.mp4');
        //$video_info['video_duration'] = $ffmpegConv->getVideoDuration($welcome->nadtemp . $_REQUEST["file"]);
        $video_info['video_duration'] = $this->getVideoDuration($this->welcome->nadtemp . $fileTo_sprite_Only["file"] . '-240.mp4');
        echo "\n\r======================================================\n\r";
        echo "\n\rfileToMP4prev video_info: \n\r";
        print_r($video_info);

        // 640 x 480 = 480 / ( 640 / 320 ) = 320 x 240
        // $param['RESOLUTION_X'], $param['RESOLUTION_Y']
        $param['filename'] = $path_parts['filename'];
        $param['RESOLUTION_X'] = 320;
        $param['RESOLUTION_Y'] = round($video_info['height'] / ($video_info['width'] / 320));
        //$param['RESOLUTION_Y'] = round($video_info['height'] / ( $video_info['width'] / 320), 0, PHP_ROUND_HALF_EVEN);
        if ($param['RESOLUTION_Y'] % 2 == 1) {
            // odd or even
            $param['RESOLUTION_Y'] = $param['RESOLUTION_Y'] - 1;
        }
        echo "\n\r======================================================\n\r";
        echo "\n\rfileToMP4prev param: \n\r";
        print_r($param);

        // clip 3, middle-1middle, middle, middle-2middle, -3 sec video
        //     [video_duration] => 187
        // 0 |<--- 3 ----------- 47 ------------ 94 ------------- 140 ------ -12 ----->| 187
        $param['clip1'] = 3;
        $middle = $video_info['video_duration'] / 2;
        $param['clip2'] = round($middle / 2);
        $param['clip3'] = round($middle);
        $middle2 = $video_info['video_duration'] - $middle;
        $param['clip4'] = round($middle + $middle2 / 2);
        if ($video_info['video_duration'] - $param['clip4'] > 12) {
            $param['clip5'] = $video_info['video_duration'] - 12;
        } else {
            $param['clip5'] = $param['clip4'] + 1;
        }

        $param['time_step'] = round($video_info['video_duration'] / 30);
        //$middle3 = $middle - $middle2;
        //$param['clip5'] = round($middle2 + $middle3 / 2);
        //$param['clip5'] = round($middle2 + $middle3);
        echo "\n\r======================================================\n\r";
        echo "\n\rfileToMP4prev middle: $middle \n\r";
        echo "\n\r======================================================\n\r";
        echo "\n\rfileToMP4prev middle2: $middle2\n\r";
        echo "\n\r======================================================\n\r";
        echo "\n\rfileToMP4prev param: \n\r";
        print_r($param);
        //exit;


        //$ffmpegConv->fileToMP4prev_video320an($welcome->nadtemp . $_REQUEST["file"], $param);
        //==$ffmpegConv->fileToMP4prev_video5sec($welcome->nadtemp . $_REQUEST["file"], $param);
        //???==$this->fileToMP4prev_video5sec($this->welcome->nadtemp . $fileTo_image_sprite_Only["file"] . '-240.mp4', $param);
        //$ffmpegConv->fileToMP4prev_video5sec($welcome->nadtemp . $path_parts['filename'] . '-test-320an.mp4', $param);
        //==$ffmpegConv->fileToMP4prev_video320an($welcome->nadtemp . 'pre-video-w320/' . $path_parts['filename'] . '-5sec.mp4', $param);
        //???==$this->fileToMP4prev_video320an($this->welcome->nadtemp . 'pre-video-w320/' . $fileTo_image_sprite_Only["file"] . '-5sec.mp4', $param);
        //$ffmpegConv->fileToMP4prev_image($welcome->nadtemp . $_REQUEST['file'], $param);
        //$ffmpegConv->fileToMP4prev_image($welcome->nadtemp . $path_parts['filename'] . '-test-320an.mp4', $param);
        //==$ffmpegConv->fileToMP4prev_image($welcome->nadtemp . 'pre-video-w320/' . $path_parts['filename'] . '.mp4');
        //???==$this->fileToMP4prev_image($this->welcome->nadtemp . 'pre-video-w320/' . $fileTo_image_sprite_Only["file"] . '-pre-v-w320.mp4', $param);
        /*if ($video_info['video_duration'] > 30) {
            $this->fileToMP4thumb_sprite($this->welcome->nadtemp . $fileTo_pre_video_image_sprite["file"] . '-240.mp4', $param);
        }*/
        $this->fileToMP4_PHP_thumb_sprite($this->welcome->nadtemp . $fileTo_sprite_Only["file"] . '-240.mp4', $param);

        return true;
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
            $sendmail = new sendmail();
            $sendmail->SendStaffAlert(['message' => "getVideoDuration get error: " . $e]);
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

    public function getVideoSize($getVideoSize) // TODO: remove?
    {
        echo "\n\rgetVideoSize\n";
        print_r($getVideoSize);
        //$path_parts = pathinfo($getVideoSize);
        try {
            $ffprobe = FFMpeg\FFProbe::create([
                'ffmpeg.binaries' => '/usr/bin/ffmpeg', // aws vide18
                'ffprobe.binaries' => '/usr/bin/ffprobe', // aws vide18
                'timeout' => 3600, // The timeout for the underlying process
                'ffmpeg.threads' => 1
            ]);
            $video_dimensions = $ffprobe
                ->streams($getVideoSize)// extracts streams informations
                ->videos()// filters video streams
                ->first()// returns the first video stream
                ->getDimensions();              // returns a FFMpeg\Coordinate\Dimension object
            $video_size['width'] = $video_dimensions->getWidth();
            $video_size['height'] = $video_dimensions->getHeight();
            echo "\n\rgetVideoSize width\n";
            print_r($video_size['width']);
            echo "\n\rgetVideoSize height\n";
            print_r($video_size['height']);
        } catch (Exception $e) {
            echo "\n\rgetVideoSize error: " . $e . "\n\r";
            $sendmail = new sendmail();
            $sendmail->SendStaffAlert(['message' => "getVideoSize error: " . $e]);
            exit;
            //return false;
        }
        return $video_size;
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
            $sendmail = new sendmail();
            $sendmail->SendStaffAlert(['message' => $timeE . "getVideoInfo error: " . $e]);
            //exit;
            return false;
        }
        $this->log->toFile(['service' => 'file', 'type' => '', 'text' => 'getVideoInfo return ' . $getVideoInfo]);
        return $video_info;
    }

    public function getAudioInfo($getAudioInfo)
    {
        // https://github.com/PHP-FFMpeg/PHP-FFMpeg/issues/264
        echo "\n\rgetVideoInfo\n";
        print_r($getAudioInfo);
        //$path_parts = pathinfo($getVideoInfo);
        try {
            $ffprobe = FFMpeg\FFProbe::create([
                'ffmpeg.binaries' => '/usr/bin/ffmpeg', // aws vide18
                'ffprobe.binaries' => '/usr/bin/ffprobe', // aws vide18
                'timeout' => 3600, // The timeout for the underlying process
                'ffmpeg.threads' => 1
            ]);
            $audio_info = [];
            $format = $ffprobe->format($getAudioInfo)->get('format_name');
            $channels = $ffprobe->streams($getAudioInfo)->audios()->first()->get('channels');
            $bits = $ffprobe->streams($getAudioInfo)->audios()->first()->get('bits_per_sample');
            $sample_rate = $ffprobe->streams($getAudioInfo)->audios()->first()->get('sample_rate');
            array_push($audio_info, $format, $channels, $bits, $sample_rate);
        } catch (Exception $e) {
            echo "\n\rgetAudioInfo error: " . $e . "\n\r";
            $sendmail = new sendmail();
            $sendmail->SendStaffAlert(['message' => "getAudioInfo error: " . $e]);
            exit;
            //return false;
        }
        return $audio_info;
    }

    public function getBitRateTS($getBitRateTS)
    {
        echo "\n\rgetTSinfo\n";
        print_r($getBitRateTS);
        if (file_exists($getBitRateTS)) {
            //exec('ffprobe -v quiet -print_format json -show_format ' . $getTSinfo, $res);
            exec('ffprobe -v quiet -show_format ' . $getBitRateTS, $res);
            /*$info = exec("/usr/bin/ffprobe -v quiet -print_format json -show_format " . $getTSinfo, $res, $result);
            print_r($result);
            print_r($info);
            print_r($res);*/
            //preg_match('bit_rate', $res[9], $res2);
            //preg_match('/bit_rate=/', $res[9], $output_array);
            $output = str_replace("bit_rate=", "", $res[9]);
            if (is_numeric($output)) {
                return $output;
            } else {
                echo "\n\rgetTSinfo bit_rate error: " . $output . "\n\r";
                $sendmail = new sendmail();
                $sendmail->SendStaffAlert(['message' => "\n\rgetTSinfo bit_rate error: " . $output . "\n\r"]);
                return 120000;
            }
        } else {
            return false;
        }
    }

    public function getAverageBitRateTS($getAverageBitRateTS)
    {
        $bitRateTS = [];
        for ($i = 0; $i <= 2; $i++) {
            $fileName = $getAverageBitRateTS . $i . '.ts';
            if (file_exists($fileName)) {
                $bitRateTS[] = $this->getBitRateTS($fileName);
            }
        }
        echo "\n\rgetAverageBitRateTS bitRateTS\n";
        print_r($bitRateTS);
        $average = round(array_sum($bitRateTS) / count($bitRateTS));
        echo "\n\rgetAverageBitRateTS average\n";
        print_r($average);
        return $average;
    }

    public function getVideoCodec($getVideoCodec)
    {
        echo "\n\rgetVideoCodec getVideoCodec\n";
        print_r($getVideoCodec);
        //exit;
        //$path_parts = pathinfo($getVideoCodec);
        //exit;
        try {
            $video = $this->ffmpeg->open($getVideoCodec);
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
            //$res = $format->getVideoCodec($video);
            $res = $format->getVideoCodec();
            //$format->setAdditionalParameters(['-hls_list_size', '0', '-movflags', '+faststart']); // 19072019 https://superuser.com/questions/802132/how-to-place-metadata-at-beginning-of-mp4-video-using-ffmpeg

            /*$format
                ->setKiloBitrate($param['BANDWIDTH'])/!*->setAudioChannels(2)
                ->setAudioKiloBitrate(256)*/;

            /*$video
                ->filters()
                ->resize(new FFMpeg\Coordinate\Dimension($param['RESOLUTION_X'], $param['RESOLUTION_Y']))
                ->synchronize();
            $video
                //->save($format, $this->welcome->nadtemp . $path_parts['filename'] . '-' . $param['RESOLUTION_Y'] . '.m3u8');
                ->save($format, $this->welcome->nadtemp . $path_parts['filename'] . '.m3u8');*/
            /*$video
                ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(3))
                ->save($this->welcome->nadtemp . $path_parts['filename'] . '.jpg');*!/*/
            //return true;
            echo "\n\rgetVideoCodec res\n";
            print_r($res);
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

    public function m3u8_analytic($file)
    {
        if (!empty($file)) {
            /*$string = <<<CUT
    #EXTM3U
    #EXTINF:-1 tvg-id="" tvg-name="A&E" tvg-logo="" group-title="ENTRETENIMIENTO",A&E`http://nxtv.tk:8080/live/jarenas/iDKZrC56xZ/76.ts
    http://nxtv.tk:8080/live/jarenas/iDKZrC56xZ/76.ts
    #EXTINF:-1 tvg-id="" tvg-name="ABC Puerto Rico" tvg-logo="" group-title="NACIONALES",ABC Puerto Rico
    http://nxtv.tk:8080/live/jarenas/iDKZrC56xZ/96.ts
    CUT;*/
            $string = file_get_contents($this->welcome->nadtemp . $file);
            //print_r($string);

            //preg_match_all('/(?P<tag>#EXTINF:-1)|(?:(?P<prop_key>[-a-z]+)=\"(?P<prop_val>[^"]+)")|(?<something>,[^\r\n]+)|(?<url>http[^\s]+)/', $string, $match);
            preg_match_all('/(?P<tag>#EXTINF)|(?:(?P<prop_key>[-a-z]+)=\"(?P<prop_val>[^"]+)")|(?<something>,[^\r\n]+)|(?<url>http[^\s]+)/', $string, $match);

            $count = count($match[0]);

            $result = [];
            $index = -1;

            for ($i = 0; $i < $count; $i++) {
                $item = $match[0][$i];

                if (!empty($match['tag'][$i])) {
                    //is a tag increment the result index
                    ++$index;
                } elseif (!empty($match['prop_key'][$i])) {
                    //is a prop - split item
                    $result[$index][$match['prop_key'][$i]] = $match['prop_val'][$i];
                } elseif (!empty($match['something'][$i])) {
                    //is a prop - split item
                    $result[$index]['something'] = $item;
                } elseif (!empty($match['url'][$i])) {
                    $result[$index]['url'] = $item;
                }
            }

            //print_r( $result );
            return $result;

        } else {
            echo 'no file';
            return false;
        }
    }
}