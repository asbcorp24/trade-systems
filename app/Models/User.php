<?php


namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    // Доступные для массового заполнения поля
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',       // admin, superadmin, user, manager
    ];

    // Скрытые атрибуты
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Автоматическое шифрование пароля
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    public function isSuperAdmin()
    {
        return $this->role === 'superadmin';
    }

    public function isAdmin()
    {
        return in_array($this->role, ['admin', 'superadmin']);
    }

    public function isSeller()
    {
        return $this->role === 'seller';
    }

    public function isStorekeeper()
    {
        return $this->role === 'storekeeper';
    }

}
