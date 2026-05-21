<?php
if (!defined('ABSPATH')) exit;

class FSRP_Rules {

    private static $key = 'fsrp_rules_v2';

    public static function get(){
        $rules = get_option(self::$key, []);
        return is_array($rules) ? $rules : [];
    }

    public static function save($rules){
        update_option(self::$key, $rules);
    }

    public static function add($rule){
        $rules = self::get();
        $rules[] = $rule;
        self::save($rules);
    }

    public static function increment_stat($index){
        $rules = self::get();
        if(isset($rules[$index])){
            $rules[$index]['hits'] = ($rules[$index]['hits'] ?? 0) + 1;
            self::save($rules);
        }
    }
}
