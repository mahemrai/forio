<?php
namespace Forio\Model;

use Illuminate\Database\Eloquent\Model as Model;
use Zend\Session\Container as Container;

/**
 * @package Model
 * @uses Illuminate\Database\Eloquent\Model
 * @author Mahendra Rai
 */
class User extends Model {
    protected $table = 'users';

    public static function getId() {
        $session = new Container('user');
        return $session->user_id;
    }
    
    /**
     * @param string $email
     * @param string $password
     * @return boolean
     */
    public static function authenticate($email, $password) {
        $user = self::where('email', '=', $email)
                    ->first();
        
        if (password_verify($password, $user->password)) {
            $session = new Container('user');
            $session->user_id = $user->id;
            $session->user = $email;
            $session->logged_in = true;
            return true;
        }
        
        return false;
    }
    
    /**
     * @return boolean
     */
    public static function isLoggedIn() {
        $session = new Container('user');
        return $session->logged_in;
    }
    
    /**
     * Clear session to log user out.
     */
    public static function logout() {
        $session = new Container('user');
        $session->getManager()->getStorage()->clear('user');
    }
}