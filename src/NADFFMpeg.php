<?php

namespace VideMe\Ffmpegconversion;

class NADFFMpeg extends \VideMe\Datacraft\NAD
{
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
}