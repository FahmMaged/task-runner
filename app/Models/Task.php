<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Task extends Model
{

    public function projectId()
    {
        return ($this->belongsTo(Project::class));
    }
    
}
