<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactMessage extends Model
{
    use HasFactory;

    public const SENDER_CUSTOMER = 'customer';

    public const SENDER_COMPANY = 'company';

    protected $fillable = [
        'contact_thread_id',
        'sender',
        'body',
    ];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(ContactThread::class, 'contact_thread_id');
    }
}
