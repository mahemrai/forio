<?php
namespace Forio\Model;

use Illuminate\Database\Eloquent\Model as Model;

/**
 * @package Model
 * @uses Illuminate\Database\Eloquent\Model
 * @author Mahendra Rai
 */
class Keyword extends Model {
    protected $table = 'keywords';

    protected $fillable = array('name');
}