<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
#[Fillable(['prefix'])]
class PhonePrefix extends Model
{
    public function users()
{
    return $this->hasMany(User::class);
}
    public static function isValidPhonePrefix(string $phone): bool
    {
        $prefix = substr($phone, 0, 2);

        return self::where('prefix', $prefix)->exists();
    }
}
