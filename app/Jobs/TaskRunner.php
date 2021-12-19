<?php

namespace App\Jobs;

use App\Models\Task as TaskModel;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\Project;

class TaskRunner implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $task;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(TaskModel $task)
    {
        $this->task = $task;
    }

    /**
     * Execute the job.
     *
     * @return void
     */

    public function handle(){
        $task = $this->task;
        $project_id = $task->project_id;
        // Update project status to running
        $project = Project::find($project_id);
        $project->status = 1;
        $project->save();
        // Update task status to running
        $task->status = 3;
        $task->started_at = Carbon::now()->toDateTimeString();
        $task->save();

        $pathArr = json_decode($task->file);
        $path    = storage_path('app\public/'.$pathArr[0]->download_link);
        $file    = @fopen($path, "r");
        // Check file path validation
        if ($file === FALSE) {
            echo("Failed to open file $path .");
            return -1;
        }

        if($task->type == "Count Lines"){
            $this->countLines($project, $task, $file, $path);
        }
        if($task->type == "Count Words"){
            $this->countWords($project, $task, $file, $path);
        }
        if($task->type == "Count Characters"){
            $this->countCharacters($project, $task, $file, $path);
        }
    }

    public function countLines($project, $task, $file, $path)
    {
        // Calculate progress of task and the occuerence
        $max = count(file($path));
        $count = 0;
        while (($line = fgets($file)) !== false) {
            $count++;
            $percentage = round(($count / $max),1) * 100;

            $task->occurrences = $count;
            $task->percentage = $percentage;
            $task->save();
        }

        fclose($file);
        if($count){
            // Update task status to pass
            $task->status = 1;
            $task->ended_at = Carbon::now()->toDateTimeString();
            $task->save();
            // Update project status to not running
            $project->status = 0;
            $project->save();
            return $count;
        }else{
            // Update task status to fail
            $task->status = 0;
            $task->ended_at = Carbon::now()->toDateTimeString();
            $task->save();
            // Update project status to not running
            $project->status = 0;
            $project->save();
            return 0;
        }
    }

    public function countWords($project, $task, $file, $path)
    {
        // Calculate progress of task and the occuerence
        $document    = file_get_contents($path);
        $max = count(preg_split("/[\s,]+/",$document));
        $count = 0;
        while (($line = fgets($file)) !== false) {
            $line = trim(preg_replace('/\s+/', ' ', $line));
            
            $count += count(preg_split("/[\s,]+/",$line));
            $percentage = round(($count / $max),1) * 100;

            $task->occurrences = $count;
            $task->percentage = $percentage;
            $task->save();
        }

        fclose($file);
        if($count){
            // Update task status to pass
            $task->status = 1;
            $task->ended_at = Carbon::now()->toDateTimeString();
            $task->save();
            // Update project status to not running
            $project->status = 0;
            $project->save();
            return $count;
        }else{
            // Update task status to fail
            $task->status = 0;
            $task->ended_at = Carbon::now()->toDateTimeString();
            $task->save();
            // Update project status to not running
            $project->status = 0;
            $project->save();
            return 0;
        }
    }

    public function countCharacters($project, $task, $file, $path)
    {
        // Calculate progress of task and the occuerence
        $document = file_get_contents($path);
        // Count file words after exclude special characters
        $document = trim(preg_replace('/\s+/', ' ', $document));
        $document = str_replace( array( '\n','\r','\t','\v','\0','\x0B', '"',',' , ';', '<', '>' ), '', $document);
        $max      = strlen($document);
        $count    = 0;
        $newLine  = 0;
        while (($line = fgets($file)) !== false) {
            $line = trim(preg_replace('/\s+/', ' ', $line));
            $line = str_replace( array( '\n','\r','\t','\v','\0','\x0B', '"',',' , ';', '<', '>' ), '', $line);
            
            $count += strlen($line);
            $percentage = round((($count+$newLine) / $max),1) * 100;
            
            $task->occurrences = $count + $newLine;
            $task->percentage = $percentage;
            $task->save();
            $newLine++;
        }

        fclose($file);
        
        if($count){
            // Update task status to pass
            $task->status = 1;
            $task->ended_at = Carbon::now()->toDateTimeString();
            $task->save();
            // Update project status to not running
            $project->status = 0;
            $project->save();
            return $count + ($newLine-1);
        }else{
            // Update task status to fail
            $task->status = 0;
            $task->ended_at = Carbon::now()->toDateTimeString();
            $task->save();
            // Update project status to not running
            $project->status = 0;
            $project->save();
            return 0;
        }
    }
}
