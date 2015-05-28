<?php
namespace Forio\Model;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model as Model;

/**
 * @package Model
 * @uses Illuminate\Database\Eloquent\Model
 * @author Mahendra Rai
 */
class Project extends Model {
    protected $table = 'projects';

    protected $fillable = array(
        'title',
        'description',
        'type',
        'for',
        'project_date'
    );

    /**
     * Relationship with keywords.
     * @return BelongsToMany
     */
    public function keywords() {
        return $this->belongsToMany('Forio\Model\Keyword', 'project_keyword');
    }

    /**
     * Relationship with images.
     * @return hasMany
     */
    public function images() {
        return $this->hasMany('Forio\Model\Image');
    }

    /**
     * Create slug by stripping special characters and replacing whitespaces.
     * @param string $title
     * @return string
     */
    public static function createSlug($title) {
        $slug = str_replace(' ', '-', strtolower($title));
        $slug = preg_replace('/[^A-Za-z0-9\-]/', '', $slug);
        return preg_replace('/-+/', '-', $slug);
    }
}