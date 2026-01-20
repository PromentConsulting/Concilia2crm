<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactEmail extends Model
{
    use HasFactory;

    protected $table = 'contact_emails';

    protected $fillable = [
        'contact_id',
        'email',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}
