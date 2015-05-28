<?php
namespace Forio\Model;

use Illuminate\Database\Eloquent\Model as Model;

/**
 * @package Model
 * @uses Illuminate\Database\Eloquent\Model
 * @author Mahendra Rai
 */
class Image extends Model {
    private $url;
    
    protected $table = 'images';
    
    public static $accepted_image_types = array(
        'image/jpeg',
        'image/jpg',
        'image/gif',
        'image/png'
    );
    
    /**
     * @param string $url
     */
    public function __construct($url=null) {
        parent::__construct();
        $this->url = $url;
    }

    /**
     * @param string $file
     * @param array $image_paths
     * @return array | null
     */
    public function copy($file, $image_paths) {
        $image = $this->getImageResource(__DIR__ . '../../../../tmp/' . $file);
        list($width, $height) = getimagesize(__DIR__ . '../../../../tmp/' . $file);
        
        $new = imagecreatetruecolor($width, $height);
        $copied_image = imagecopyresampled($new, $image, 0, 0, 0, 0, $width, $height, $width, $height);

        if ($this->createImage($new, __DIR__ . '../../../../public/image/large/' . $file, __DIR__ . '../../../../tmp/' . $file)) {
            if ($this->createThumb($file, __DIR__ . '../../../../public/image/large/' . $file)) {
                if ($this->createCover($file, __DIR__ . '../../../../public/image/large/' . $file)) {
                    return array(
                        'large' => $image_paths['large'] . $file,
                        'cover' => $image_paths['cover'] . $file,
                        'thumb' => $image_paths['thumb'] . $file
                    );
                }
            }
            
            return null;
        }
    }
    
    /**
     * @param string $file - name of the image file
     * @param string $type - image file type
     * @param array $rect - co-ordinate values of a cropped image
     * @param array $image_paths - paths of where the image will be uploaded
     * @return array
     */
    public function createCover($file, $image) {
        list($width, $height) = getimagesize($image);

        $center_x = $width/2;
        $center_y = $height/2;

        $min_x = $center_x - 225;
        $min_y = $center_y - 225;
        $max_x = $center_x + 225;
        $max_y = $center_y + 225;

        $cover = imagecreatetruecolor(450, 450);
        $source = $this->getImageResource($image);

        imagecopyresampled($cover, $source, 0, 0, $min_x, $min_y, $max_x, $max_y, $max_x, $max_y);
        return $this->createImage($cover, __DIR__ . '../../../../public/image/cover/' . $file, $image);
    }
    
    /**
     * @param string $file - name of the image file
     * @param string $image - image resource to be copied from
     * @return resource
     */
    protected function createThumb($file, $image) {
        $percent = 0.3;
        
        list($width, $height) = getimagesize($image);
        $new_width = $width * $percent;
        $new_height = $height * $percent;
        
        $thumb = imagecreatetruecolor($new_width, $new_height);
        $source = $this->getImageResource($image);
        
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        return $this->createImage($thumb, __DIR__ . '../../../../public/image/thumb/' . $file, $image);
    }
    
    /**
     * @param string $file
     * @return resource
     */
    protected function getImageResource($file) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $file_type = finfo_file($finfo, $file);
        
        switch($file_type) {
            case 'image/jpeg':
            case 'image/jpg':
                return imagecreatefromjpeg($file);
            case 'image/gif':
                return imagecreatefromgif($file);
            case 'image/png':
                return imagecreatefrompng($file);
            default:
                break;
        }
    }
    
    /**
     * @param resource $resource
     * @param string $filename
     * @param string $tmpfile
     * @return boolean
     */
    protected function createImage($resource, $filename, $tmpfile) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $file_type = finfo_file($finfo, $tmpfile);
        
        switch($file_type) {
            case 'image/jpeg':
            case 'image/jpg':
                return imagejpeg($resource, $filename, 100);
            case 'image/gif':
                return imagegif($resource, $filename, 100);
            case 'image/png':
                return imagepng($resource, $filename, 9);
            default:
                break;
        }
    }
}