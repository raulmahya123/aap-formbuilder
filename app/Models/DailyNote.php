<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyNote extends Model
{
    protected $fillable = ['user_id','title','content','note_time'];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
