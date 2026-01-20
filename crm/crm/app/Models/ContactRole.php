<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactRole extends Model
{
    use HasFactory;

    protected $table = 'contact_roles';

    protected $fillable = [
        'contact_id',
        'role',
        'label_personalizado',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}
