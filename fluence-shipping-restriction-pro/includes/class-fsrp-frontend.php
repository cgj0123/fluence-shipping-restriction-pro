<?php
if (!defined('ABSPATH')) exit;

class FSRP_Frontend {

    public function __construct(){
        add_action('woocommerce_checkout_process',[$this,'run']);
        add_action('woocommerce_review_order_before_submit',[$this,'hide_button'],5);
        add_filter('woocommerce_package_rates',[$this,'change_label'],10,2);
    }

    private function get_country(){
        $c = WC()->customer->get_shipping_country();
        if(!$c) $c = WC()->customer->get_billing_country();
        if(!$c) $c = WC()->countries->get_base_country();
        return $c;
    }

    private function match(&$product=null,&$rule=null,&$index=null){
        $rules=FSRP_Rules::get();
        if(!$rules || !WC()->cart) return false;
        $country=$this->get_country();

        foreach(WC()->cart->get_cart() as $item){
            $obj=$item['data'];
            $pid=$obj->is_type('variation')?$obj->get_parent_id():$obj->get_id();
            $name=$obj->get_name();
            $cats=wp_get_post_terms($pid,'product_cat',['fields'=>'ids']);

            foreach($rules as $i=>$r){
                if(!in_array($country,$r['countries'])) continue;

                if(in_array($pid,$r['products']) || array_intersect($cats,$r['categories'])){
                    $product=$name;
                    $rule=$r;
                    $index=$i;
                    return true;
                }
            }
        }
        return false;
    }

    public function run(){
        $product=null;$rule=null;$i=null;
        if(!$this->match($product,$rule,$i)) return;

        FSRP_Rules::increment_stat($i);

        $msg=str_replace(['{{product_name}}','{{country}}'],[$product,$this->get_country()],$rule['message']);
        wc_add_notice($msg,'error');

        if($rule['mode']==='block'){
            wc_add_notice(__('Please remove restricted items before placing the order.'),'error');
        }
    }

    public function hide_button(){
        $p=null;$r=null;$i=null;
        if(!$this->match($p,$r,$i)) return;
        if($r['mode']==='hide'){
            echo '<style>#place_order{display:none!important}</style>';
        }
    }

    public function change_label($rates,$pkg){
        $p=null;$r=null;$i=null;
        if(!$this->match($p,$r,$i)) return $rates;

        foreach($rates as $id=>$rate){
            if($rate->method_id==='free_shipping'){
                $rates[$id]->label=str_replace(['{{product_name}}','{{country}}'],[$p,$this->get_country()],$r['message']);
            }
        }
        return $rates;
    }
}
