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
    public function pgGetMyTasks($pgGetMyTasks, $limit = 18)
    {
        try {
            $result = pg_query($this->pgConn, "
                select *
                from " . $this->table_tasks . " 
                WHERE owner_id = '" . $pgGetMyTasks['user_id'] . "' 
                and tasks.task_status <> 'ready'
                and tasks.task_status <> 'success'
                order by created_at desc
                LIMIT " . $limit . ";");
        } catch (Exception $e) {
            echo 'Pg. ' . $e;
            return false;
            //echo "No file. ";
        }
        //pg_close($this->pgConn);
        if ($result) {
            return pg_fetch_all($result);
        } else {
            return false;
        }

    }
}