<?php
namespace Forio\Model;

use Illuminate\Database\Eloquent\Model as Model;

/**
 * @package Model
 * @uses Illuminate\Database\Eloquent\Model
 * @author Mahendra Rai
 */
class Education extends Model {
    protected $table = 'educations';

    protected $fillable = array(
        'course',
        'school',
        'location',
        'start_year',
        'end_year'
    );
}