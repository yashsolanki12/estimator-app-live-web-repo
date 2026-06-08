<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopifySession extends Model
{
    protected $connection = 'mysql';
    protected $table = 'shopify_session';
    
    protected $fillable = [
        'session_id',
        'shop',
        'is_online',
        'state',
        'scope',
        'access_token',
        'expires_at',
        'user_id',
        'user_first_name',
        'user_last_name',
        'user_email',
        'user_email_verified',
        'account_owner',
        'locale',
        'collaborator'
    ];
}