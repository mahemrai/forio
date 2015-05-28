<?php
namespace Forio\Model;

use Illuminate\Database\Eloquent\Model as Model;

/**
 * @package Model
 * @uses Illuminate\Database\Eloquent\Model
 * @author Mahendra Rai
 */
class Experience extends Model {
    protected $table = 'experiences';

    protected $fillable = array(
        'title',
        'organisation',
        'location',
        'start_date',
        'end_date'
    );
}