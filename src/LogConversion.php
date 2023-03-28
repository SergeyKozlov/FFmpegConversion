<?php

namespace VideMe\Ffmpegconversion;

use VideMe\Datacraft\model\PostgreSQL;
use VideMe\Datacraft\NAD;

class LogConversion extends \VideMe\Datacraft\log\log
{
    public function pgSetTask($pgSetTask)
    {
        //echo "\r\n<hr>setEvent setEvent<br>";
        //print_r($setEvent);
        $welcome = new NAD();
        if (empty($pgSetTask['task_id'])) $pgSetTask['task_id'] = $welcome->trueRandom();
        //echo "\r\n<hr>pgSetTask task_id:<br>";
        //print_r($pgSetTask['task_id']);
        $pg = new PostgreSQL();
        $trueTask = $pg->pgPaddingItems($pgSetTask); // TODO: dubble NOOO
        return $pg->pgAddData($pg->table_tasks, $trueTask);
    }
    public function pgGetTaskById($pgGetTaskById)
    {
        $pg = new PostgreSQL();
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
        $pg = new PostgreSQL();
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
        $pg = new PostgreSQL();
        $currentTask["task_status"] = $newStatus;
        $resUpdateDocument = $pg->pgUpdateData($pg->table_tasks,
            'task_status',
            $currentTask['task_status'],
            'task_id',
            $currentTask['task_id']);
        //echo "\n\rtaskChangeStatus resUpdateDocument\n\r";
        //print_r($resUpdateDocument);
    }
}