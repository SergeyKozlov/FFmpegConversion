<?php

namespace VideMe\Ffmpegconversion;

class PsqlFfmpeg extends \VideMe\Datacraft\model\PostgreSQL
{
    public function pgGetTasksWorked_fileUploadVideo()
    {
        try {
            $result = pg_query($this->pgConn, "
SELECT *
from tasks
where tasks.type = 'fileUploadVideo' and tasks.status = 'worked'
LIMIT 9;");

        } catch (Exception $e) {
            echo 'Pg. ' . $e;
            return false;
            //echo "No file. ";
        }
        //pg_close($this->pgConn);
        if ($result) {
            return pg_fetch_all($result);
            //return pg_fetch_assoc($result);
            //return pg_fetch_row($result);
            //return pg_fetch_result($result, 0);
        } else {
            return false;
        }
    }
}