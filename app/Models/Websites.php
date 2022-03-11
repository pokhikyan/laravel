<?php

namespace App\Models;

use App\Http\Controllers\VacanciesController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\WebsitesController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Websites extends Model
{
    use HasFactory;

    protected $table = 'websites';

    public $fillable = [
        'company',
        'url',
        'active',
    ];

    /**
     * Get the phone associated with the user.
     */
    public function vacancies()
    {
        return $this->hasMany(Vacancies::class);
    }

    /**
     * Get the phone associated with the user.
     */
    public function logs()
    {
        return $this->hasOne(LogsController::class);
    }

}
