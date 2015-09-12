<?php
/*
Plugin Name: Aegis
Plugin URI: http://colourstheme.com/plugins/aegis-page-builder
Description: With "Aegis" you can build your site quickly and smarty.
Version: 1.0.0
Author: Colours Theme
Author URI: http://colourstheme.com
License: GNU General Public License v3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Aegis plugin, Copyright 2014 Colourstheme
Aegis is distributed under the terms of the GNU GPL.

Requires at least: 3.9
Tested up to: 4.3
Text Domain: aegis
Domain Path: /languages/
 */

define('AEGIS_DIR_URL', plugin_dir_url(__FILE__));
define('AEGIS_DIR_PATH', plugin_dir_path(__FILE__));

add_action('plugins_loaded', array('Aegis', 'plugins_loaded'));
add_action('after_setup_theme', array('Aegis', 'get_instance'));

class Aegis {

    protected static $instance = null;

    public function __construct() {

        if (is_admin()) {
            add_action('admin_init', array($this, 'admin_init'));
            add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
            add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
            add_action('admin_footer', array($this, 'admin_footer'));
            add_action('wp_ajax_aegis_get_widget_form', array($this, 'get_widget_form'));
            add_action('wp_ajax_aegis_save_widget', array($this, 'save_widget'));
            add_action('wp_ajax_aegis_remove_widget', array($this, 'remove_widget'));
            add_action('wp_ajax_aegis_save_all', array($this, 'save_all'));
            add_action('wp_ajax_aegis_get_row_customize_form', array($this, 'get_row_customize_form'));
            add_action('wp_ajax_aegis_save_row_customize_form', array($this, 'save_row_customize_form'));
        }

        add_shortcode('aegis_site_url', array($this, 'get_site_url'));
    }

    public static function get_instance() {
        if (null == self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public static function plugins_loaded() {
        load_plugin_textdomain('aegis', false, AEGIS_DIR_PATH . '/languages/');
    }

    public static function get_meta_key_widget_customize() {
        return apply_filters('aegis_get_meta_key_widget_customize', 'aegis_widget_customize');
    }

    public static function get_meta_key_page_customize() {
        return apply_filters('aegis_get_meta_key_page_customize', 'aegis_page_customize');
    }

    public static function get_meta_key_page() {
        return apply_filters('aegis_get_meta_key_page', 'aegis_page');
    }

    public static function get_meta_key_col() {
        return apply_filters('aegis_get_meta_key_row_customize', 'aegis_col');
    }

    public static function get_meta_key_row() {
        return apply_filters('aegis_get_meta_key_row_customize', 'aegis_row');
    }

    public static function get_meta_key_widget() {
        return apply_filters('aegis_get_meta_key_widget', 'aegis_widget');
    }

    public function admin_init() {
        
    }

    public function admin_enqueue_scripts($hook) {

        if (in_array($hook, array('post-new.php', 'post.php'))) {

            #Script
            wp_enqueue_media();
            wp_enqueue_script('jquery-form');
            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script('jquery-ui-position');
            wp_enqueue_script('jquery-ui-resizable');                
            wp_enqueue_script('jquery-ui-dialog');
            wp_enqueue_script('jquery-ui-tooltip');
            wp_enqueue_script('jquery-ui-draggable');
            wp_enqueue_script('jquery-ui-droppable');
            wp_enqueue_script('thickbox');
            wp_enqueue_script('wp-color-picker');
            wp_enqueue_script('jquery-noty', plugins_url('js/jquery.noty.js', __FILE__), array('jquery'), NULL, true);
            wp_enqueue_script('jquery-tooltipster', plugins_url('js/jquery.tooltipster.js', __FILE__), array('jquery'), NULL, true);
            wp_enqueue_script('aegis', plugins_url('js/aegis.js', __FILE__), array('jquery', 'jquery-noty'), NULL, true);
            wp_localize_script('aegis', 'aegis_json', array(
                'directory_uri' => AEGIS_DIR_URL,
                'ajax' => admin_url('admin-ajax.php'),
                'i18n' => array(
                    'layouts'                          => __('Layouts', 'aegis'),
                    'elements'                         => __('Elements', 'aegis'),
                    'row_customize'                    => __('Row customize', 'aegis'),
                    'media_center'                     => __('Media center', 'aegis'),
                    'use'                              => __('Use', 'aegis'),
                    'drag_row_to_reorder'              => __('Drag row to reorder', 'aegis'),
                    'split_row_to_multi_columns'       => __('Split row to multi columns', 'aegis'),
                    'edit_this_row'                    => __('Edit this row', 'aegis'),
                    'delete_this_row'                  => __('Delete this row', 'aegis'),
                    'drag_column_to_reorder'           => __('Drag column to reorder', 'aegis'),
                    'insert_new_widget_to_this_column' => __('Insert new widget to this column', 'aegis'),
                    'edit_this_column'                 => __('Edit this column', 'aegis'),
                    'drag_widget_to_reorder'           => __('Drag widget to reorder', 'aegis'),
                    'delete_this_widget'               => __('Delete this widget', 'aegis'),
                    'edit_this_widget'                 => __('Edit this widget', 'aegis')
                ),
                'layouts' => $this->get_grid(),
                'key' => array(
                    'widget' => self::get_meta_key_widget(),
                    'col'    => self::get_meta_key_col(),
                    'row'    => self::get_meta_key_row(),
                )
            ));

            #Style
            wp_enqueue_style('thickbox');
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_style('themify-icons', plugins_url('css/themify-icons.css', __FILE__), array(), NULL);
            wp_enqueue_style('jquery-ui-structure', plugins_url('css/jquery-ui/jquery-ui.structure.css', __FILE__), array(), NULL);
            wp_enqueue_style('jquery-ui-theme', plugins_url('css/jquery-ui/jquery-ui.theme.css', __FILE__), array(), NULL);
            wp_enqueue_style('jquery-tooltipster', plugins_url('css/tooltipster.css', __FILE__), array(), NULL);
            wp_enqueue_style('aegis', plugins_url('css/aegis.css', __FILE__), array(), NULL);
        }
    }

    public function admin_footer() {
        wp_nonce_field('aegis_get_row_customize_form', 'aegis_get_row_customize_form_security', false);
        wp_nonce_field('aegis_get_widget_form', 'aegis_get_widget_form_security', false);
        wp_nonce_field('aegis_remove_widget', 'aegis_remove_widget_security', false);
        wp_nonce_field('aegis_save_all', 'aegis_save_all_security', false);

        $this->pre_load_modals();
    }

    public function add_meta_boxes() {

        add_meta_box('aegis_metabox', __('Aegis', 'aegis'), array($this, 'get_metabox_form'), 'page');
    }

    public function get_metabox_form($post) {
        wp_nonce_field('aegis_nonce', 'aegis_nonce');
        ?>
        <div class="a_wrap a_clearfix">



            <div class="a_row_wrap a_body a_clearfix">
                <?php
                $key = self::get_meta_key_page();
                $data = get_post_meta($post->ID, $key, true);
                $grid = $this->get_grid();

                if ($data):

                    $rows = isset($data['rows']) && !empty($data['rows']) ? $data['rows'] : array();
                    if ($rows):
                        foreach ($rows as $row_index => $row):
                            $grid_index = (int) $row['index'];
                            ?>

                            <div id="<?php echo esc_attr($row['id']); ?>" class="a_grid_item" data-index="<?php echo esc_attr($grid_index); ?>">
                                <div class="a_header a_clearfix">
                                    
                                    <span class="a_action a_hanle a_row_hanle a_pull_left tooltip" title="<?php _e('Drag row to reorder', 'aegis'); ?>"><i class="ti-split-v"></i></span>
                                    <span class="a_action a_row_style a_pull_left tooltip" title="<?php _e('Split row to multi columns', 'aegis'); ?>"><i class="ti-layout-column3"></i></span>                                           
                                    <?php
                                    $row_customize_fields = apply_filters('aegis_get_row_customize_fields', array());
                                    if ($row_customize_fields):
                                        ?>
                                        <span class="a_action a_row_customize a_pull_left tooltip" title="<?php _e('Edit this row', 'aegis'); ?>"><i class="ti-paint-roller"></i></span>
                                    <?php endif; ?>
                                    <span class="a_action a_close a_row_close a_pull_right tooltip"  title="<?php _e('Delete this row', 'aegis'); ?>"><i class="ti-trash"></i></span>

                                </div>
                                <div class="a_body a_clearfix">
                                    <div class="a_column_wrap a_row a_clearfix">

                                        <?php
                                        $cols = isset($row['cols']) && !empty($row['cols']) ? $row['cols'] : array();
                                        if ($cols):
                                            foreach ($cols as $col_index => $col):
                                                $col_index = (int) $col['index'];
                                                ?>

                                                <div id="<?php echo esc_attr($col['id']); ?>" class="a_column_item_outer <?php echo esc_attr("a_col_{$col_index}"); ?>" data-index="<?php echo esc_attr($col_index); ?>">
                                                    <div class="a_column_item">
                                                        <div class="a_header a_clearfix">
                                                            <span class="a_action a_hanle a_column_hanle a_pull_left tooltip" title="<?php _e('Drag column to reorder', 'aegis'); ?>"><i class="ti-split-v"></i></span>
                                                            <span class="a_action a_column_add_widget a_pull_left tooltip" title="<?php _e('Insert new widget to this column', 'aegis'); ?>"><i class="ti-package"></i></span>

                                                            <?php
                                                            $col_customize_fields = apply_filters('aegis_get_col_customize_fields', array());
                                                            if ($col_customize_fields):
                                                                ?>
                                                                <span class="a_action a_column_customize a_pull_left tooltip" title="<?php _e('Edit this column', 'aegis'); ?>"><i class="ti-paint-roller"></i></span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="a_block_wrap a_body a_clearfix">

                                                            <?php
                                                            $widgets = isset($col['widgets']) && !empty($col['widgets']) ? $col['widgets'] : array();
                                                            if ($widgets):
                                                                foreach ($widgets as $widget_index => $widget):
                                                                    $widget_id = isset($widget['id']) && !empty($widget['id']) ? $widget['id'] : false;
                                                                    $widget_name = isset($widget['name']) && !empty($widget['name']) ? $widget['name'] : false;
                                                                    if ($widget_id):
                                                                        ?>
                                                                        <div id="<?php echo esc_attr($widget_id); ?>" class="a_block a_clearfix">
                                                                            <div class="a_header a_clearfix">
                                                                                <span class="a_action a_hanle a_block_hanle a_pull_left tooltip" title="<?php _e('Drag widget to reorder', 'aegis'); ?>"><i class="ti-split-v"></i></span>
                                                                                <span class="a_action a_block_edit a_pull_left tooltip" title="<?php _e('Edit this widget', 'aegis'); ?>"><i class="ti-pencil"></i></span>
                                                                                <span class="a_action a_close a_block_close a_pull_right tooltip" title="<?php _e('Delete this widget', 'aegis'); ?>"><i class="ti-trash"></i></span>                                                                                
                                                                            </div>

                                                                            <div class="a_body a_clearfix"><?php echo esc_attr($widget_name); ?></div>
                                                                        </div>

                                                                        <?php
                                                                    endif;
                                                                endforeach;
                                                            endif;
                                                            ?>

                                                        </div>
                                                    </div>
                                                </div>

                                                <?php
                                            endforeach;
                                        endif;
                                        ?>

                                    </div>
                                </div>
                            </div>

                            <?php
                        endforeach;
                    endif;
                endif;
                ?>
            </div>

            <div class="a_footer a_clearfix">

                <span class="a_button a_left a_add_row">
                    <i class="a_icon ti-plus"></i>
                    <span class="a_text"><?php _e('Add new row', 'aegis'); ?></span>
                </span>

                <span class="a_button a_right a_pull_right a_save_all">
                    <i class="a_icon ti-save"></i>
                    <span class="a_text"><?php _e('Save all', 'aegis'); ?></span>
                </span>

            </div>

        </div>

        <?php
    }

    public function pre_load_modals() {
        $screen = get_current_screen();
        if ($screen->base == 'post') {
            global $post;
            if ('page' == $post->post_type) {
                global $wp_widget_factory;
                ?>
                <div class="hide_all">
                    <?php
                    $grid = $this->get_grid();
                    if ($grid):
                        ?>
                        <div id="a_modal_grid" class="a_wrap">
                            <div class="a_row a_clearfix">
                                <?php
                                $index = 0;
                                foreach ($grid as $grid_index => $cols):
                                    ?>
                                    <div class="a_col_4">
                                        <div class="a_row_mockup" data-index="<?php echo esc_attr($grid_index); ?>">
                                            <div class="a_row a_clearfix">
                                                <?php foreach ($cols as $col): ?>
                                                    <div class="a_col <?php echo esc_attr("a_col_{$col}"); ?>">
                                                        <div class="a_col_mockup">
                                                            <?php echo esc_attr($col); ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                    $index++;
                                    if (0 == $index % 3 && $index < count($grid)) {
                                        echo '</div>';
                                        echo '<div class="a_row a_clearfix">';
                                    }
                                endforeach;
                                ?>	
                            </div>
                        </div>
                    <?php endif; ?>

                    <div id="a_modal_widgets" class="a_wrap a_clearfix">
                        <div class="a_row a_clearfix">
                            <?php
                            $widgets = $wp_widget_factory->widgets;
                            $index = 0;
                            foreach ($widgets as $class_name => $widget_info):
                                ?>
                                <div class="a_col_4">
                                    <div class="a_item">

                                        <input type="hidden" name="a_widget_class_name" value="<?php echo esc_attr($class_name); ?>" autocomplete="off">
                                        <input type="hidden" name="a_widget_title" value="<?php echo esc_attr($widget_info->name); ?>" autocomplete="off">

                                        <div class="a_header a_clearfix">                    
                                            <?php
                                            $icon = 'ti-wordpress';
                                            if (isset($widget_info->icon) && !empty($widget_info->icon)) {
                                                $icon = $widget_info->icon;
                                            }
                                            ?>

                                            <span class="a_title a_pull_left"><?php echo $widget_info->name; ?></span>

                                            <span class="a_icon a_pull_right">
                                                <i class="<?php echo esc_attr($icon); ?>"></i>
                                            </span>                    
                                        </div>

                                        <div class="a_body a_clearfix">	                    
                                            <?php echo esc_attr($widget_info->widget_options['description']); ?>	                    
                                        </div>                              
                                    </div>
                                </div>
                                <?php
                                $index++;
                                if (0 == $index % 3) {
                                    echo '</div>';
                                    echo '<div class="a_row a_clearfix">';
                                }
                            endforeach;
                            ?>
                        </div>
                    </div>

                    <?php $form_action = wp_nonce_url(admin_url('admin-ajax.php'), 'aegis_save_widget', 'security'); ?>

                    <form id="a_modal_single_widget"
                          class="a_wrap a_clearfix"
                          name="a_form_single_widget"
                          method="POST"
                          autocomplete="off"
                          onsubmit="AegisAjax.saveWidget(event, jQuery(this));" 
                          action="<?php echo esc_url($form_action); ?>">									
                        <input type="hidden" name="action" value="aegis_save_widget" autocomplete="off">
                        <input type="hidden" name="a_widget_class_name" value="" autocomplete="off">
                        <input type="hidden" name="a_widget_title" value="" autocomplete="off">
                        <input type="hidden" name="a_widget_id" value="" autocomplete="off">					
                        <input type="hidden" name="a_post_id" value="<?php echo $post->ID; ?>" autocomplete="off">					
                        <div class="a_widget_form a_tabs"></div>					
                    </form>

                    <?php $form_action = wp_nonce_url(admin_url('admin-ajax.php'), 'aegis_save_row_customize_form', 'security'); ?>

                    <form id="a_modal_row_customize"
                          class="a_wrap a_clearfix"
                          name="a_form_single_widget"
                          method="POST"
                          autocomplete="off"
                          onsubmit="AegisAjax.saveRowCustomize(event, jQuery(this));"
                          action="<?php echo esc_url($form_action); ?>">
                        <input type="hidden" name="action" value="aegis_save_row_customize_form" autocomplete="off">
                        <input type="hidden" name="a_row_id" value="" autocomplete="off">
                        <input type="hidden" name="a_post_id" value="<?php echo $post->ID; ?>" autocomplete="off">
                        <div class="a_row_customize_form a_tabs"></div>					
                    </form>

                </div>
                <?php
            }
        }
    }

    public function get_grid() {
        $grid = array(
            array(12),
            array(10, 2),
            array(8, 4),
            array(7, 5),
            array(7, 2, 3),
            array(6, 6),
            array(5, 4, 3),
            array(4, 4, 4),
            array(4, 6, 2),
            array(3, 9),
            array(3, 6, 3),
            array(3, 3, 3, 3),
            array(2, 8, 2),
            array(2, 2, 2, 6),
            array(2, 2, 2, 2, 2, 2)
        );

        return apply_filters('aegis_get_grid', $grid);
    }

    public function save_all() {
        check_ajax_referer('aegis_save_all', 'security');

        $data = isset($_POST['data']) ? $_POST['data'] : false;
        $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : false;
        $return = '';

        if ($post_id) {
            $meta_key = self::get_meta_key_page();

            if ($data) {
                update_post_meta($post_id, $meta_key, $data);
                $return = __('All data has been saved !', 'aegis');
            } else {
                delete_post_meta($post_id, $meta_key);
                $widget_key = self::get_meta_key_widget();
                $row_key = self::get_meta_key_row();

                global $wpdb;
                $wpdb->query($wpdb->prepare("delete from $wpdb->postmeta where post_id = %d and meta_key like %s", $post_id, "{$widget_key}_%"));
                $wpdb->query($wpdb->prepare("delete from $wpdb->postmeta where post_id = %d and meta_key like %s", $post_id, "{$row_key}_%"));
                $return = __('All data has been cleaned.', 'aegis');

                wp_reset_query();
            }
        }

        echo $return;
        exit();
    }

    public function get_row_customize_form() {
        check_ajax_referer('aegis_get_row_customize_form', 'security');

        $row_id = isset($_POST['row_id']) ? $_POST['row_id'] : false;
        $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : false;

        $customize_key = self::get_meta_key_row();
        $customize_fields = apply_filters('aegis_get_row_customize_fields', array());

        if ($customize_fields):
            $customize_data = array();
            if ($row_id) {
                $customize_data = get_post_meta($post_id, $row_id, true);
            }
            ?>
            <nav class="a_nav">
                <ul class="a_clearfix">
                    <?php
                    $is_first = true;
                    foreach ($customize_fields as $tab_slug => $tab):
                        $tab_id = "#aegis_tab_row_{$tab_slug}";
                        $tab_class = $is_first ? 'a_tab_item a_first a_active' : 'a_tab_item';
                        ?>
                        <li class="<?php echo esc_attr($tab_class); ?>">
                            <span data-tab-id="<?php echo esc_attr($tab_id); ?>"><?php echo esc_attr($tab['title']); ?></span>
                        </li>
                        <?php
                        $is_first = false;
                    endforeach;
                    ?>
                </ul>
            </nav>

            <?php
            $is_first = true;
            foreach ($customize_fields as $tab_slug => $tab):
                $tab_id = "aegis_tab_row_{$tab_slug}";
                $tab_class = $is_first ? 'a_tab_content a_first a_active' : 'a_tab_content a_hide';
                ?>
                <div id="<?php echo esc_attr($tab_id); ?>" class="<?php echo esc_attr($tab_class); ?>">
                    <?php
                    foreach ($tab['params'] as $param_key => $param_args):
                        $param_args['name'] = sprintf('%s[%s][%s]', $customize_key, $tab_slug, $param_key);
                        $param_args['value'] = isset($customize_data[$tab_slug][$param_key]) ? $customize_data[$tab_slug][$param_key] : (isset($param_args['default']) ? $param_args['default'] : null);
                        $this->get_control($param_args);
                    endforeach;
                    ?>
                </div>
                <?php
                $is_first = false;
            endforeach;
        endif;

        exit();
    }

    public function save_row_customize_form() {
        check_ajax_referer('aegis_save_row_customize_form', 'security');
        $return = '';

        if (!empty($_POST)) {
            $customize_key = self::get_meta_key_row();
            $customize_fields = apply_filters('aegis_get_row_customize_fields', array());

            if ($customize_key) {
                $row_id = isset($_POST['a_row_id']) ? $_POST['a_row_id'] : false;
                $post_id = isset($_POST['a_post_id']) ? (int) $_POST['a_post_id'] : false;
                $data = isset($_POST[$customize_key]) ? $_POST[$customize_key] : array();

                if ($data) {
                    foreach ($customize_fields as $tab_slug => $tab) {
                        foreach ($tab['params'] as $param_key => $param_args) {
                            $new_value = isset($data[$tab_slug][$param_key]) ? $data[$tab_slug][$param_key] : null;
                            $data[$tab_slug][$param_key] = $this->esc_data($param_args, $new_value);
                        }
                    }

                    update_post_meta($post_id, $row_id, $data);
                }
                $return = __('All data has been saved !', 'aegis');
            }
        }

        echo $return;
        exit();
    }

    public function get_widget_form() {
        check_ajax_referer('aegis_get_widget_form', 'security');

        $widget_class_name = isset($_POST['widget_class_name']) ? $_POST['widget_class_name'] : false;
        $widget_id = isset($_POST['widget_id']) ? $_POST['widget_id'] : false;
        $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : false;

        if ($post_id):
            $instance = array();
            $customize_data = array();

            if ($widget_id) {
                $widget_saved_data = get_post_meta($post_id, $widget_id, true);
                $instance = isset($widget_saved_data['instance']) ? (array) $widget_saved_data['instance'] : array();
                $customize_data = isset($widget_saved_data['customize']) ? $widget_saved_data['customize'] : array();
            }

            if (!empty($widget_saved_data) && empty($widget_class_name)) {
                $widget_class_name = isset($widget_saved_data['class_name']) ? $widget_saved_data['class_name'] : false;
            }

            if ($widget_class_name):

                $widget = new $widget_class_name;
                $widget->id_base = rand(0, 9999);
                $widget->number = rand(0, 9999);
                $customize_key = self::get_meta_key_widget_customize();
                $customize_fields = apply_filters('aegis_get_widget_customize_fields', array());
                ?>
                <nav class="a_nav">
                    <ul class="a_clearfix">
                        <li class="a_tab_item a_first a_active">
                            <span data-tab-id="#aegis_tab_widget"><?php _e('Widget', 'aegis'); ?></span>
                        </li>
                        <?php
                        if ($customize_fields):
                            foreach ($customize_fields as $tab_slug => $tab):
                                $tab_id = "#aegis_tab_widget_{$tab_slug}";
                                ?>
                                <li class="a_tab_item">
                                    <span data-tab-id="<?php echo esc_attr($tab_id); ?>"><?php echo esc_attr($tab['title']); ?></span>
                                </li>
                                <?php
                            endforeach;
                        endif;
                        ?>
                    </ul>
                </nav>

                <div id="aegis_tab_widget" class="a_tab_content a_active">
                    <?php $widget->form($instance); ?>
                </div>

                <?php
                if ($customize_fields):

                    foreach ($customize_fields as $tab_slug => $tab):
                        $tab_id = "aegis_tab_widget_{$tab_slug}";
                        ?>
                        <div id="<?php echo esc_attr($tab_id); ?>" class="a_tab_content a_hide">
                            <?php
                            foreach ($tab['params'] as $param_key => $param_args):

                                $param_args['name'] = sprintf('%s[%s][%s]', $customize_key, $tab_slug, $param_key);
                                $param_args['value'] = isset($customize_data[$tab_slug][$param_key]) ? $customize_data[$tab_slug][$param_key] : (isset($param_args['default']) ? $param_args['default'] : null);
                                $this->get_control($param_args);

                            endforeach;
                            ?>
                        </div>
                        <?php
                    endforeach;
                endif;

            endif;

        endif;

        exit();
    }

    public function save_widget() {
        check_ajax_referer('aegis_save_widget', 'security');

        if (!empty($_POST)):
            $form_data = $_POST;

            $post_id = false;
            $widget_id = false;
            $is_first = true;
            $data = array();
            $data['widget'] = array();
            $data['class_name'] = array();

            $customize_key = self::get_meta_key_widget_customize();

            foreach ($form_data as $key => $value) {
                if ('a_post_id' == $key) {
                    $post_id = (int) $value;
                } else if ('widget' == substr($key, 0, 6)) {
                    $data['instance'] = reset($value);
                } else if ('a_widget_class_name' == $key) {
                    $data['class_name'] = $value;
                } else if ('a_widget_id' == $key) {
                    $widget_id = $value;
                } else if ('a_widget_title' == $key) {
                    $data['name'] = $value;
                } else if ($customize_key == $key) {
                    $data['customize'] = $value;
                    if ($data) {
                        $customize_fields = apply_filters('aegis_get_widget_customize_fields', array());
                        foreach ($customize_fields as $tab_slug => $tab) {
                            foreach ($tab['params'] as $param_key => $param_args) {
                                $new_value = isset($data['customize'][$tab_slug][$param_key]) ? $data['customize'][$tab_slug][$param_key] : null;
                                $data['customize'][$tab_slug][$param_key] = $this->esc_data($param_args, $new_value);
                            }
                        }
                    }
                }
            }

            $old_data = get_post_meta($post_id, $widget_id, true);
            $old_instance = array();
            if ($old_data) {
                $is_first = false;
                $old_instance = isset($old_data['instance']) ? $old_data['instance'] : array();
                $data['class_name'] = isset($old_data['class_name']) ? $old_data['class_name'] : false;
            }


            if ($data['class_name']):

                $obj = new $data['class_name'];
                $data['instance'] = $obj->update($data['instance'], $old_instance);
                update_post_meta($post_id, $widget_id, $data);

                if ($is_first):
                    ob_start();
                    ?>
                    <div id="<?php echo esc_attr($widget_id); ?>" class="a_block a_clearfix">
                        <div class="a_header a_clearfix">
                            <span class="a_action a_hanle a_block_hanle a_pull_left tooltip" title="<?php _e('Drag widget to reorder', 'aegis'); ?>"><i class="ti-split-v"></i></span>												
                            <span class="a_action a_block_edit a_pull_left tooltip" title="<?php _e('Edit this widget', 'aegis'); ?>"><i class="ti-pencil"></i></span>
                            <span class="a_action a_close a_block_close a_pull_right tooltip" title="<?php _e('Delete this widget', 'aegis'); ?>"><i class="ti-trash"></i></span>                            
                        </div>

                        <div class="a_body a_clearfix">
                            <?php echo esc_attr($data['name']); ?>
                        </div>
                    </div>	    	
                    <?php
                    $html = ob_get_clean();
                    echo $html;
                endif;

            endif;

        endif;

        exit();
    }

    public function remove_widget() {
        check_ajax_referer('aegis_remove_widget', 'security');

        $return = '';

        if (!empty($_POST)) {
            $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : false;
            if ($post_id) {
                $widget_id = isset($_POST['widget_id']) ? esc_attr($_POST['widget_id']) : false;
                if ($widget_id) {
                    delete_post_meta($post_id, $widget_id);
                    $return = 'deleted';
                }
            }
        }

        echo $return;

        exit();
    }

    public function str_beautify($string) {

        return ucwords(str_replace('_', ' ', $string));
    }

    public function str_uglify($string) {
        $string = preg_replace("/[^a-zA-Z0-9\s]/", '', $string);
        return strtolower(str_replace(' ', '_', $string));
    }

    public function get_control($param_args) {
        ?>
        <div class="a_control a_wrap a_clearfix">
            <div class="a_row a_clearfix">
                <?php
                $control_class = 'a_col_9';
                if (!isset($param_args['title']) || empty($param_args['title'])):
                    $control_class = 'a_col_12';
                else:
                    ?>
                    <div class="a_col_3">
                        <?php echo esc_attr($param_args['title']); ?>
                    </div>					
                <?php
                endif;
                ?>			
                <div class="<?php echo esc_attr($control_class); ?>">
                    <?php
                    switch ($param_args['type']) {
                        case 'text':
                            $this->get_field_text($param_args);
                            break;
                        case 'select':
                            $this->get_field_select($param_args);
                            break;
                        case 'number':
                            $this->get_field_number($param_args);
                            break;
                        case 'checkbox':
                            $this->get_field_checkbox($param_args);
                            break;
                        case 'radio':
                            $this->get_field_radio($param_args);
                            break;
                        case 'textarea':
                            $this->get_field_textarea($param_args);
                            break;
                        case 'color':
                            $this->get_field_color($param_args);
                            break;
                        case 'image':
                            $this->get_field_image($param_args);
                            break;
                        case 'spacing':
                            $this->get_field_spacing($param_args);
                            break;
                    }
                    if (isset($param_args['help']) && !empty($param_args['help'])) {
                        ?>
                        <div class="a_ui_help_text">
                            <?php echo htmlspecialchars_decode(stripcslashes($param_args['help'])); ?>
                        </div>
                        <?php
                    }
                    ?>					
                </div>
            </div>
        </div>
        <?php
    }

    public function get_field_text($params) {
        ?>
        <input
            name="<?php echo esc_attr($params['name']); ?>"
            value="<?php echo esc_attr($params['value']); ?>"
            type="text"
            class="a_ui a_ui_text a_size_50p"
            autocomplete="off">
            <?php
        }

        public function get_field_number($params) {
            ?>
        <input
            name="<?php echo esc_attr($params['name']); ?>"
            value="<?php echo esc_attr($params['value']); ?>"
            type="text"
            class="a_ui a_ui_number a_size_20p"
            autocomplete="off">
            <?php if ($params['affix']): ?>
            <i><?php echo esc_attr($params['affix']); ?></i>
            <?php
        endif;
    }

    public function get_field_color($params) {
        $default = isset($params['default']) ? $params['default'] : '';
        ?>
        <input 
            name="<?php echo esc_attr($params['name']); ?>" 
            value="<?php echo esc_attr($params['value']); ?>" 
            type="text" 
            class="a_ui a_ui_color a_size_20p" 
            data-default-color="<?php echo esc_attr($default); ?>" 
            autocomplete="off">
            <?php
        }

        public function get_field_checkbox($params) {
            $params['value'] = isset($params['value']) ? $params['value'] : isset($params['default']) ? $params['default'] : 'false';
            ?>
        <input
        <?php checked($params['value'], 'true'); ?>
            name="<?php echo esc_attr($params['name']); ?>"  		
            value="true"
            type="checkbox"
            class="a_ui_checbox"
            autocomplete="off">
            <?php
        }

        public function get_field_radio($params) {
            foreach ($params['options'] as $value => $title):
                $radio_id = sprintf('%s_%s', $params['name'], $value);
                ?>
            <label for="<?php echo esc_attr($radio_id); ?>">
                <span><?php echo esc_attr($title); ?></span>
                <input
                <?php checked($params['value'], $value, true); ?>
                    id="<?php echo esc_attr($radio_id); ?>" 
                    name="<?php echo esc_attr($params['name']); ?>"
                    value="<?php echo esc_attr($value); ?>"
                    type="radio"
                    class="a_ui_radio"
                    autocomplete="off">    	
            </label>			
            <?php
        endforeach;
    }

    public function get_field_select($params) {
        ?>
        <select 
            name="<?php echo esc_attr($params['name']); ?>" 
            class="a_ui a_ui_select a_size_30p"
            autocomplete=off>
                <?php foreach ($params['options'] as $value => $title): ?>
                <option <?php selected($params['value'], $value, true); ?> value="<?php echo $value ?>">
                    <?php echo esc_attr($title); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    public function get_field_image($params) {
        $value = !empty($params['value']) ? do_shortcode($params['value']) : '';
        ?>  	
        <div class="a_row a_ui_image">
            <div class="a_col_10">
                <input class="a_size_100p a_image_url"           
                       name="<?php echo esc_attr($params['name']); ?>"
                       type="text" 
                       value="<?php echo esc_url($value); ?>">
            </div>

            <div class="a_col_1">
                <span class="a_size_100p a_image_add button button-secondary"><?php _e('+', 'enliven'); ?></span>
            </div>

            <div class="a_col_1">
                <span class="a_size_100p a_image_remove button button-secondary"><?php _e('-', 'enliven'); ?></span>
            </div>
        </div>
        <?php
    }

    public function get_field_textarea($params) {
        $class = isset($params['class']) && !empty($params['class']) ? $params['class'] : '';
        $rows = isset($params['rows']) && !empty($params['rows']) ? (int) $params['rows'] : 3;
        ?>
        <textarea 
            name="<?php echo esc_attr($params['name']); ?>"
            class="a_ui a_ui_textarea a_size_100p <?php echo esc_attr($class); ?>"
            rows="<?php echo esc_attr($rows); ?>"
            autocomplete="off"><?php echo htmlspecialchars_decode(stripslashes($params['value'])); ?></textarea>
            <?php
        }

        public function get_field_spacing($params) {

            $params['value'] = wp_parse_args($params['value'], array(
                'margin' => array('top' => '', 'bottom' => '', 'left' => '', 'right' => ''),
                'padding' => array('top' => '', 'bottom' => '', 'left' => '', 'right' => '')
            ));

            $positions = array('top', 'bottom', 'left', 'right');
            ?>

            <div class="a_ui_spacing">
                <div class="a_first a_row a_clearfix">
                    <div class="a_col_2">
                        <i><?php _e('Margin', 'aegis'); ?></i>
                    </div>
                    <div class="a_col_10">
                        <div class="a_row a_clearfix">                            
                            <?php
                            foreach ($positions as $position):
                                $_name = sprintf('%s[margin][%s]', $params['name'], $position);
                                $_value = $params['value']['margin'][$position];
                                ?>
                                <div class="a_col_3">
                                     <p class="a_sub_control">
                                        <code><?php echo esc_attr($position); ?></code>
                                        <input
                                            name="<?php echo esc_attr($_name); ?>" 
                                            value="<?php echo esc_attr($_value); ?>" 
                                            type="text" class="a_ui a_ui_text" autocomplete="off">
                                    </p>
                                </div>
                            <?php endforeach; ?>                                     
                        </div>
                    </div>
                </div>

                <div class="a_row a_clearfix">
                    <div class="a_col_2">
                        <i><?php _e('Padding', 'aegis'); ?></i>
                    </div>
                    <div class="a_col_10">
                        <div class="a_row a_clearfix">                           
                            <?php
                            foreach ($positions as $position):
                                $_name = sprintf('%s[padding][%s]', $params['name'], $position);
                                $_value = $params['value']['padding'][$position];
                                ?>
                                <div class="a_col_3">
                                    <p class="a_sub_control">
                                        <code><?php echo esc_attr($position); ?></code>
                                        <input
                                            name="<?php echo esc_attr($_name); ?>" 
                                            value="<?php echo esc_attr($_value); ?>" 
                                            type="text" class="a_ui a_ui_text" autocomplete="off">
                                    </p>
                                </div>
                            <?php endforeach; ?>  
                            
                        </div>
                    </div>
                </div>                

            </div>
        <?php
    }

    public function esc_data($param_args, $value) {
        switch ($param_args['type']) {
            case 'color':
                $value = esc_attr($value);
                break;
            case 'image':
                if (!empty($value)) {
                    $value = str_replace(get_site_url(), '[aegis_site_url]', $value);
                }
                break;
            case 'select':
                $value = esc_attr($value);
                break;
            case 'text':
                $value = esc_attr($value);
                break;
            case 'number':
                if (trim($value) != '') {
                    $value = floatval($value);
                }
                break;
            case 'textarea':
                $value = htmlspecialchars_decode(stripslashes($value));
                break;
        }
        return $value;
    }

    public function get_site_url() {
        return get_site_url();
    }

}
