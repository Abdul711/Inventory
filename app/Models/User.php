<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\ResetPasswordNotification;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function sendPasswordResetNotification($token): void
    {
        $url = route('customer.password_reset', [
            'token' => $token,
            'email' => $this->email,
        ]);

        $this->notify(new ResetPasswordNotification($url));
    }
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function supplier(){
      return $this->hasOne(Supplier::class);

    }
      public function employee(){
      return $this->hasOne(Employee::class);

    }
    public function customer(){
        return $this->hasOne(Customer::class);
    }
     public function deliveryboy(){
        return $this->hasOne(DeliveryBoy::class);
    }
    public function role(){
         return $this->belongsTo(Role::class);
    }
}