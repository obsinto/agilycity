<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasRoles, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'cpf',         // Adicionado campo CPF
        'first_access', // Adicionado campo first_access
        'secretary_id',
        'department_id',
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

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function secretary()
    {
        return $this->belongsTo(Secretary::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'first_access' => 'boolean',
        ];
    }

    /**
     * Set the user's CPF - remove formatação antes de salvar.
     *
     * @param string $value
     * @return void
     */
    public function setCpfAttribute($value)
    {
        // Salva apenas os números do CPF no banco de dados
        $this->attributes['cpf'] = preg_replace('/[^0-9]/', '', $value);
    }

    /**
     * Get the user's CPF with formatting.
     *
     * @param string $value
     * @return string
     */
    public function getCpfAttribute($value)
    {
        // Se o CPF já tiver formatação ou for vazio, retorna como está
        if (empty($value) || strpos($value, '.') !== false) {
            return $value;
        }

        // Aplica a formatação 000.000.000-00
        $cpf = $value;
        if (strlen($cpf) === 11) {
            $cpf = substr($cpf, 0, 3) . '.' .
                substr($cpf, 3, 3) . '.' .
                substr($cpf, 6, 3) . '-' .
                substr($cpf, 9, 2);
        }

        return $cpf;
    }
}
