<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_filename',
        'file_path',
        'filesize',
        'status',
        'file_hash',
        'total_rows',
        'error_message',
    ];
}
