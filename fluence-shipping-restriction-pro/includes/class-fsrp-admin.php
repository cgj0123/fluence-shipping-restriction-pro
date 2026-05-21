<?php
if (!defined('ABSPATH')) exit;

class FSRP_Admin {

    public function __construct(){
        add_action('admin_menu', [$this,'menu']);
        add_action('admin_enqueue_scripts', [$this,'assets']);
        add_action('wp_ajax_fsrp_save_rule', [$this,'save_rule']);
        add_action('wp_ajax_fsrp_product_search', [$this,'product_search']);
    }

    public function menu(){
        add_menu_page('Fluence Restrictions','Shipping Restriction','manage_woocommerce','fsrp',[$this,'page'],'dashicons-shield-alt',56);
    }

    public function assets($hook){
        if($hook !== 'toplevel_page_fsrp') return;

        // ---------- Select2 依赖保障（优先使用已注册的，否则从 CDN 加载）----------
        if( !wp_style_is('select2', 'registered') ){
            wp_register_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css', [], '4.0.13');
        }
        if( !wp_script_is('select2', 'registered') ){
            wp_register_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', ['jquery'], '4.0.13', true);
        }
        wp_enqueue_style('select2');
        wp_enqueue_script('select2');

        // ---------- Chart.js (CDN) ----------
        wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', [], '4.4.0', true);

        // 插件自有样式
        wp_enqueue_style('fsrp-css', FSRP_URL.'assets/css/admin.css', [], '1.2.0');

        // 插件脚本（依赖 jQuery 和 select2）
        wp_enqueue_script('fsrp', FSRP_URL.'assets/js/admin.js', ['jquery', 'select2'], '1.2.0', true);
        wp_localize_script('fsrp','FSRP_AJAX',[
            'url'   => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('fsrp_nonce')
        ]);
    }

    public function page(){
        $rules = FSRP_Rules::get();
        $chart_data = array_map(function($r){ return $r['hits'] ?? 0; }, $rules);
        $chart_labels = array_map(function($r){ return $r['name'] ?? 'Rule'; }, $rules);
        ?>
        <div class="wrap fsrp-wrap">
            <h1>Fluence Shipping Restriction — Rules</h1>

            <div class="fsrp-panel">
                <h2>Statistics</h2>
                <canvas id="fsrpChart" height="100"></canvas>
                <script>
                window.FSRP_CHART_DATA = <?php echo json_encode($chart_data); ?>;
                window.FSRP_LABELS = <?php echo json_encode($chart_labels); ?>;
                </script>
            </div>

            <div class="fsrp-panel">
                <h2>Create Rule</h2>
                <div class="fsrp-grid">
                    <div>
                        <label>Rule Name</label>
                        <input type="text" id="fsrp_name" class="regular-text">
                    </div>

                    <div>
                        <label>Products</label>
                        <select id="fsrp_products" multiple></select>
                        <small>Ajax search enabled</small>
                    </div>

                    <div>
                        <label>Categories</label>
                        <select id="fsrp_categories" multiple>
                            <?php foreach(get_terms(['taxonomy'=>'product_cat','hide_empty'=>false]) as $t): ?>
                            <option value="<?php echo esc_attr($t->term_id); ?>"><?php echo esc_html($t->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label>Countries</label>
                        <select id="fsrp_countries" multiple>
                        <?php foreach(WC()->countries->get_countries() as $code=>$name): ?>
                            <option value="<?php echo esc_attr($code); ?>"><?php echo esc_html($name); ?></option>
                        <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label>Mode</label>
                        <select id="fsrp_mode">
                            <option value="block">Block Checkout</option>
                            <option value="hide">Hide Order Button</option>
                        </select>
                    </div>

                    <div>
                        <label>Message</label>
                        <textarea id="fsrp_message" rows="3">This product is not available for delivery to {{country}}: {{product_name}}</textarea>
                    </div>

                    <div>
                        <button id="fsrp_save" class="button button-primary button-large">Save Rule</button>
                        <div id="fsrp_status"></div>
                    </div>
                </div>
            </div>

            <div class="fsrp-panel">
                <h2>Existing Rules</h2>
                <?php if(!$rules): ?>
                    <p>No rules yet.</p>
                <?php else: ?>
                    <table class="widefat striped">
                        <thead>
                            <tr><th>Name</th><th>Countries</th><th>Hits</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach($rules as $r): ?>
                            <tr>
                                <td><?php echo esc_html($r['name']); ?></td>
                                <td class="countries-list">
                                    <?php 
                                    $countries = $r['countries'] ?? [];
                                    $display = array_slice($countries, 0, 3);
                                    echo esc_html(implode(', ', $display));
                                    if(count($countries) > 3) echo ' …';
                                    ?>
                                </td>
                                <td><?php echo esc_html($r['hits']??0); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    public function save_rule(){
        check_ajax_referer('fsrp_nonce','nonce');

        $rule=[
            'name'       => sanitize_text_field($_POST['name']??''),
            'products'   => array_map('intval', $_POST['products']??[]),
            'categories' => array_map('intval', $_POST['categories']??[]),
            'countries'  => array_map('sanitize_text_field', $_POST['countries']??[]),
            'mode'       => sanitize_text_field($_POST['mode']??'block'),
            'message'    => sanitize_textarea_field($_POST['message']??''),
            'hits'       => 0
        ];
        FSRP_Rules::add($rule);
        wp_send_json_success();
    }

    public function product_search(){
        check_ajax_referer('fsrp_nonce','nonce');
        $term = sanitize_text_field($_GET['q']??'');
        $q = new WP_Query([
            'post_type'      => 'product',
            'posts_per_page' => 20,
            's'              => $term
        ]);
        $res = [];
        while($q->have_posts()){ $q->the_post();
            $res[] = ['id'=>get_the_ID(), 'text'=>get_the_title()];
        }
        wp_reset_postdata();
        wp_send_json($res);
    }
}