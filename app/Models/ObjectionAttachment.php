<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObjectionAttachment extends Model
{
    //
    protected $fillable = [
        'objection_id',
        'attachment',
        'filename','size'];
}
