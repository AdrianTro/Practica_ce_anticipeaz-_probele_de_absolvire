<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ContactThread extends Model
{
    use HasFactory;

    protected $fillable = [
        'thread_uuid',
        'public_token',
        'first_name',
        'last_name',
        'email',
        'status',
        'last_message_at',
        'admin_seen_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'admin_seen_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'thread_uuid';
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ContactMessage::class);
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(ContactMessage::class)->latestOfMany();
    }

    public function fullName(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function mailSubject(): string
    {
        return 'Pretentie '.$this->thread_uuid.' - '.$this->fullName();
    }

    public function rootEmailMessageId(): string
    {
        return 'contact-'.$this->thread_uuid.'@'.$this->emailHost();
    }

    public function customerEmailMessageId(ContactMessage $message): string
    {
        return 'contact-'.$this->thread_uuid.'-client-'.$message->id.'@'.$this->emailHost();
    }

    public function companyEmailMessageId(ContactMessage $message): string
    {
        return 'contact-'.$this->thread_uuid.'-company-'.$message->id.'@'.$this->emailHost();
    }

    private function emailHost(): string
    {
        $host = parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'reclamdesign.local';
        $host = preg_replace('/[^A-Za-z0-9.-]/', '', $host) ?: 'reclamdesign.local';

        return mb_strtolower($host);
    }
}
