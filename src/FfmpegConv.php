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

}