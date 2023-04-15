<?php

namespace VideMe\Ffmpegconversion;

class S3Ffmpeg
{
    public function uploadFile($uploadFile)
    {
        //$welcome = new NADFFMpeg();

        $fileInfo = pathinfo($uploadFile['name']);
        echo "\n\rS3 uploadFile \n\r";
        print_r($uploadFile);
        echo "\n\rS3 fileInfo \n\r";
        print_r($fileInfo);
        $ContentType = '';
        if ($fileInfo['extension'] == 'ts') $ContentType = 'video/mp4';
        //if ($fileInfo['extension'] == 'jpg') $ContentType = 'image/jpeg';
        if ($fileInfo['extension'] == 'm3u8') $ContentType = 'application/x-mpegURL';
        if ($fileInfo['extension'] == 'webm') $ContentType = 'video/webm';
        if ($fileInfo['extension'] == 'mp4') $ContentType = 'video/mp4';
        //$bucket = 'video.vide.me';
        try {
            /*$result = $this->s3->putObject([
                'Bucket'       => $this->bucket_video_vide_me,
                'Key'          => $uploadFile['name'],
                'SourceFile'   => $uploadFile['file'],
                'ContentType'  => $ContentType,
                'ACL'          => 'public-read',
                //'StorageClass' => 'REDUCED_REDUNDANCY',
                'Metadata'     => [
                    'param1' => 'Source',
                    'param2' => 'www.vide.me'
                ]]);*/
            /*copy($welcome->nadtemp . 'pre-image-w320/'  . $frae_name . '.jpg',
                //copy('/var/www/videme_nfs/pre-image-w320/'  . $frae_name . '.jpg',
                $_SERVER['DOCUMENT_ROOT'] . '/pre-image-w320/'  . $frae_name . '.jpg');*/
            copy($uploadFile['file'], $_SERVER['DOCUMENT_ROOT'] . '/../media/'  . $uploadFile['name']);
        } catch (Exception $e) {
            echo "\n\ruploadFile this->s3->putObject: " . $e . "\n\r";
            //$sendmail = new sendmail();
            //$sendmail->SendStaffAlert(['message' => "\n\ruploadFile this->s3->putObject: " .$e]);
            exit;
            //return false;
        }
        return $fileInfo;
    }
    public function uploadImage($uploadImage)
    {   // https://www.w3.org/Graphics/JPEG/
        //echo "\n\rs3 uploadImage uploadImage:\n\r";
        //print_r($uploadImage);
        //$bucket = 'img.vide.me';
        /*$result = $this->s3->putObject([
            'Bucket'       => $this->bucket_img_vide_me,
            'Key'          => $uploadImage['file'],
            'SourceFile'   => $this->welcome->nadtemp . $uploadImage['file'],
            'ContentType'  => 'image/jpeg',
            'ACL'          => 'public-read',
            //'StorageClass' => 'REDUCED_REDUNDANCY',
            'Metadata'     => [
                'param1' => 'Source',
                'param2' => 'www.vide.me'
            ]]);*/
        $welcome = new NADFFMpeg();

        copy($welcome->getNadtemp() . $uploadImage['file'], $_SERVER['DOCUMENT_ROOT'] . '/../media/'  . $uploadImage['file']);
        return $uploadImage['file'];
    }
}