<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LanguageLocation extends Model
{
    use HasFactory;

    protected $table = 'language_location'; // Navnet på tabellen

    // Angi hvilke felter som kan massefylles (mass assignable)
    protected $fillable = [
        'location_code',
        'location_name',
        'location_code_parent',
        'country_iso_code',
        'location_type',
        'available_sources',
        'language_name',
        'language_code',
    ];

    // Hvis du bruker JSON-felt, kan du angi dem som "casts" for enkel tilgang
    protected $casts = [
        'available_sources' => 'array',
    ];
}
