<?php

namespace VideMe\Ffmpegconversion;

class NADFFMpeg extends \VideMe\Datacraft\NAD
{
    public $nadtemp;
    public function __construct(/*log $log*/)
    {
        $this->setNadtemp($_SERVER['DOCUMENT_ROOT'] . '/../nadtemp/');
    }

    /**
     * @param string $nadtemp
     */
    public function setNadtemp(string $nadtemp): void
    {
        $this->nadtemp = $nadtemp;
    }

    /**
     * @return string
     */
    public function getNadtemp(): string
    {
        return $this->nadtemp;
    }

    function paddingTagsForItem($paddingTagsForItem)
    {
        if (!empty($paddingTagsForItem)) {
            $tags = [];
            //$articleBody['tags'] = $this->welcome->safetyTags($articleBody['tags']);
            $paddingTagsForItemSafety = $this->welcome->safetyTags($paddingTagsForItem);
            foreach ($paddingTagsForItemSafety as $key => $value) { // Brasília -> [tags] => ["Bras\u00edlia"]
                //foreach ($paddingTagsForItem as $key => $value) {
                //echo "\n\r paddingTagsForItem foreach key: " . $key;
                //echo "\n\r paddingTagsForItem foreach value: " . $value;
                $tags[] = htmlspecialchars($value);

                //$tags[] = $value;
                //$tags[] = $value;
            }
            //$dataItems['tags'] = json_encode($tags);
            //return json_encode($tags);
            return $tags;
        } else {
            return false;
        }
    }
    public function uploadSetParam($uploadSetParam)
    {
        //echo "\n\ruploadSetParam\n\r";
        //print_r($uploadSetParam);
        $uploadDo['owner_id'] = $this->CookieToUserId();
        if (!empty($uploadSetParam['title'])) {
            $uploadDo['title'] = $uploadSetParam['title'];
        } else {
            $uploadDo['title'] = '';
        }
        if (!empty($uploadSetParam['content'])) {
            $uploadDo['content'] = $uploadSetParam['content'];
        } else {
            $uploadDo['content'] = '';
        }
        if (!empty($uploadSetParam['album_id'])) {
            $uploadDo['album_id'] = $uploadSetParam["album_id"];
        } else {
            $uploadDo['album_id'] = '';
        }
        if (!empty($uploadSetParam['ticket_id'])) $uploadDo['ticket_id'] = $uploadSetParam["ticket_id"]; //??????????????????
//if (!empty($_POST['ticket'])) $retVal['ticket'] = $_POST["ticket"];
        $uploadDo['task_id'] = $this->memcachedGetKey(['key' => $uploadSetParam['ticket_id']]);
        $uploadDo['ticket'] = $this->memcachedGetKey(['key' => $uploadSetParam['ticket_id']]); // TODO: why?
//$retVal['access'] = $_POST['access'] ?? 'private';
        /* desabled because no web button if ($uploadDo['album_id'] == 'public') {
            $uploadDo['access'] = 'public';
        } elseif ($uploadDo['album_id'] == 'friends') {
            $uploadDo['access'] = 'friends';
        } elseif ($uploadDo['album_id'] == 'private') {
            $uploadDo['access'] = 'private';
        } elseif (!empty($uploadDo['album_id'])) {
            $albumInfo = $this->pgAlbumInfoById($uploadDo);
            echo 'albumInfo';
            print_r($albumInfo);
            $uploadDo['access'] = $albumInfo['access'];
        }*/
        $uploadDo['access'] = 'public'; // because no web button

        if (!empty($_POST['upload_type'])) {
            $uploadDo['upload_type'] = $uploadSetParam['upload_type'];
        }
        return $uploadDo;
    }
}