<?php

// app/Models/BecomeAdviser.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class BecomeAdviser extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'age',
        'gender',
        'role',
        'username',
        'specialities',
        'description',
        'downloading_a_file',
    ];

    
}
