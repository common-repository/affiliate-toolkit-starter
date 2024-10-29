<?php
if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

class atkp_posttypes_shop
{
    /**
     * Construct the plugin object
     */
    public function __construct($pluginbase)
    {
        $this->register_shopPostType();

        add_action('add_meta_boxes', array(&$this, 'list_boxes'));
        add_action('save_post', array(&$this, 'list_detail_save'));

        add_action('admin_enqueue_scripts', array($this, 'image_enqueue'));
        add_action('admin_head', array($this, 'hidey_admin_head'));
        add_action('atkp_shop_to_trash', array($this, 'atkp_shop_to_trash'), 10, 1);

        $this->post_parent_qv();

        ATKPTools::add_column(ATKP_SHOP_POSTTYPE, esc_html__('Status', ATKP_PLUGIN_PREFIX), function ($post_id) {
            $selwebservice = ATKPTools::get_post_setting($post_id, ATKP_SHOP_POSTTYPE . '_access_webservice');

            $shop_provider = atkp_shop_provider_base::retrieve_provider($selwebservice);
            if ($shop_provider != null) {
                echo '<span style="font-weight:bold">' . esc_html__('Type', ATKP_PLUGIN_PREFIX) . ':</span> <span >' . esc_html__($shop_provider->get_caption(), ATKP_PLUGIN_PREFIX) . '</span><br />';
            }
            echo '<span style="font-weight:bold">' . esc_html__('ID', ATKP_PLUGIN_PREFIX) . ':</span> <span >' . esc_html__($post_id, ATKP_PLUGIN_PREFIX) . '</span>';


            if ($selwebservice != ATKP_SUBSHOPTYPE) {
                $error = ATKPTools::get_post_setting($post_id, ATKP_SHOP_POSTTYPE . '_access_message');
                echo ', ';
                if ($error == null || empty($error)) {
                    echo '<span style="color:green">' . esc_html__('Connected', ATKP_PLUGIN_PREFIX) . '</span>';
                } else {
                    echo '<span style="color:red">' . esc_html__('Not connected', ATKP_PLUGIN_PREFIX) . ' (' . esc_html__($error . ATKP_PLUGIN_PREFIX) . ')</span>';
                }
            } else {
                $parent_id = wp_get_post_parent_id($post_id);
                $parent_name = get_the_title($parent_id);

                if ($parent_id && $parent_id != 0) {
                    $pp_url = add_query_arg(array('post_type' => 'atkp_shop', 'post_parent' => $parent_id), 'edit.php');

                    echo '<br /><span style="font-weight:bold">' . esc_html__('Parent shop', ATKP_PLUGIN_PREFIX) . ':</span> <span><a href="' . esc_url(get_edit_post_link($parent_id)) . '">' . esc_html__($parent_name, ATKP_PLUGIN_PREFIX) . '</a><a href="' . esc_url($pp_url) . '"><span class="dashicons dashicons-filter"></span></a></span>';

                }
            }

            do_action('atkp_shop_status_column', $post_id);

            $holdontop = ATKPTools::get_post_setting($post_id, ATKP_SHOP_POSTTYPE . '_holdshopontop');

            if ($holdontop == '' || !is_numeric(($holdontop))) {
                if ($holdontop == true) {
                    $holdontop = 10;
                } else {
                    $holdontop = 100;
                }
            }
            $holdontop = intval($holdontop);

            echo '<br />' . sprintf(esc_html__('Sort order: %s', ATKP_PLUGIN_PREFIX), esc_html($holdontop));


        }, 3);

        ATKPTools::add_column(ATKP_SHOP_POSTTYPE, esc_html__('Logo', ATKP_PLUGIN_PREFIX), function ($post_id) {

            $shps = atkp_shop::load($post_id);


            try {


                $imageurl = $shps->get_smalllogourl();
                if ($imageurl == '') {
                    $imageurl = $shps->get_logourl();
                }

                if ($imageurl != '') {
                    echo '<img src="' . esc_url($imageurl) . '" alt="' . esc_attr($shps->get_title()) . '"  title="' . esc_html__($shps->get_title(), ATKP_PLUGIN_PREFIX) . '"style="max-width:60px" />';
                }
            } catch (Exception $e) {
                echo esc_html__($e->getMessage(), ATKP_PLUGIN_PREFIX);
            }

        }, 1);

        add_filter('map_meta_cap', function ($caps, $cap, $user_id, $args) {
            // Nothing to do
            if ('delete_post' !== $cap || empty($args[0])) {
                return $caps;
            }

            // Target the payment and transaction post types
            if (in_array(get_post_type($args[0]), [ATKP_SHOP_POSTTYPE], true) && wp_get_post_parent_id(get_the_ID()) > 0) {
                $caps[] = 'do_not_allow';
            }

            return $caps;
        }, 10, 4);
    }





    function post_parent_qv()
    {
        if (is_admin()) {
            $GLOBALS['wp']->add_query_var('post_parent');
        }
    }



    /**
     * Loads the image management javascript
     */
    function image_enqueue()
    {
        global $typenow;
        if ($typenow == ATKP_SHOP_POSTTYPE) {
            wp_enqueue_media();

            // Registers and enqueues the required javascript.
            wp_register_script('meta-box-image', plugin_dir_url(ATKP_PLUGIN_FILE) . 'js/meta-box-image.js', array('jquery'));
            wp_localize_script(
                'meta-box-image',
                'meta_image',
                array(
                    'title' => esc_html__('Choose or Upload an image', ATKP_PLUGIN_PREFIX),
                    'button' => esc_html__('Use this image', ATKP_PLUGIN_PREFIX),
                )
            );
            wp_enqueue_script('meta-box-image');
        }
    }

    function hidey_admin_head()
    {
        echo '<style type="text/css">';
        echo '.column-' . esc_html(sanitize_title(esc_html__('Logo', ATKP_PLUGIN_PREFIX))) . ' { width: 70px; }';
        echo '</style>';
    }

    function atkp_shop_to_trash($shop_id)
    {
        $args = array(
            'post_parent' => $shop_id,
            'post_type' => 'atkp_shop'
        );

        $posts = get_posts($args);

        if (is_array($posts) && count($posts) > 0) {

            foreach ($posts as $post) {
                wp_trash_post($post->ID);
            }
        }
    }

    function register_shopPostType()
    {
        $labels = array(
            'name' => esc_html__('Shops', ATKP_PLUGIN_PREFIX),
            'singular_name' => esc_html__('Shop', ATKP_PLUGIN_PREFIX),
            'add_new_item' => esc_html__('Add new Shop', ATKP_PLUGIN_PREFIX),
            'edit_item' => esc_html__('Edit Shop', ATKP_PLUGIN_PREFIX),
            'new_item' => esc_html__('New Shop', ATKP_PLUGIN_PREFIX),
            'all_items' => esc_html__('Shops', ATKP_PLUGIN_PREFIX),
            'view_item' => esc_html__('View Shop', ATKP_PLUGIN_PREFIX),
            'search_items' => esc_html__('Search Shops', ATKP_PLUGIN_PREFIX),
            'not_found' => esc_html__('No lists found', ATKP_PLUGIN_PREFIX),
            'not_found_in_trash' => esc_html__('No lists found in the Trash', ATKP_PLUGIN_PREFIX),
            'parent_item_colon' => '',
            'menu_name' => esc_html__('Shops', ATKP_PLUGIN_PREFIX),
        );
        $args = array(
            'labels' => $labels,
            'description' => 'Holds our Shop',

            'public' => false,  // it's not public, it shouldn't have it's own permalink, and so on
            'publicly_queriable' => true,  // you should be able to query it
            'show_ui' => true,  // you should be able to edit it in wp-admin
            'exclude_from_search' => true,  // you should exclude it from search results
            'show_in_nav_menus' => false,  // you shouldn't be able to add it to menus
            'has_archive' => false,  // it shouldn't have archive page
            'rewrite' => false,  // it shouldn't have rewrite rules
            'hierarchical' => true,
            'capability_type' => 'page',

            'menu_position' => 200,
            'supports' => array('title'),
            'show_in_menu' => ATKP_PLUGIN_PREFIX . '_affiliate_toolkit-plugin',
        );

        $args = apply_filters('atkp_shop_register_post_type', $args);

        register_post_type(ATKP_SHOP_POSTTYPE, $args);
    }

    function list_boxes()
    {

        add_meta_box(
            ATKP_SHOP_POSTTYPE . '_detail_box',
            esc_html__('Shop Information', ATKP_PLUGIN_PREFIX),
            array(&$this, 'shop_detail_box_content'),
            ATKP_SHOP_POSTTYPE,
            'normal',
            'default'
        );

        add_meta_box(
            ATKP_SHOP_POSTTYPE . '_queue_box',
            esc_html__('Queue History', ATKP_PLUGIN_PREFIX),
            array(&$this, 'shop_queue_box_content'),
            ATKP_SHOP_POSTTYPE,
            'normal',
            'low'
        );

        add_meta_box(
            ATKP_SHOP_POSTTYPE . '_copy_settings_box',
            esc_html__('Copy Settings', ATKP_PLUGIN_PREFIX),
            array(&$this, 'shop_copy_settings_box_content'),
            ATKP_SHOP_POSTTYPE,
            'side',
            'low'
        );

    }

    function shop_copy_settings_box_content()
    {

        $postmetas = get_post_meta(get_the_ID());

        $array_fields = array();
        $array_fields['post_id'] = get_the_ID();
        foreach ($postmetas as $meta_key => $meta_value) {
            if (substr($meta_key, 0, 5) == 'atkp_') {
                $array_fields[$meta_key] = ($meta_value);
            }
        }

        ?>
        <strong><?php echo esc_html__('Copy settings:', ATKP_PLUGIN_PREFIX) ?></strong>
        <textarea readonly="readonly" rows="10" style="width: 100%"><?php echo (json_encode($array_fields)) ?></textarea>
        <strong><?php echo esc_html__('Paste settings:', ATKP_PLUGIN_PREFIX) ?></strong>
        <textarea name="atkp_paste_settings" rows="10" style="width: 100%"></textarea>

        <?php
    }

    function shop_queue_box_content($post)
    {
        $atkp_queuetable_helper = new atkp_queuetable_helper();
        if (!$atkp_queuetable_helper->exists_table()[0]) {
            echo esc_html__('database table does not exists: ' . $atkp_queuetable_helper->get_queuetable_tablename(), ATKP_PLUGIN_PREFIX);
            return;
        }

        $entries = $atkp_queuetable_helper->get_list_entry(0, $post->ID, null, 100, 1, 'id', 'desc');

        ?>
        <table class="wp-list-table widefat fixed striped table-view-list queueentries">
            <thead>
                <tr>
                    <th><?php echo esc_html__('ID', ATKP_PLUGIN_PREFIX) ?></th>
                    <th><?php echo esc_html__('Queue', ATKP_PLUGIN_PREFIX) ?></th>
                    <th><?php echo esc_html__('Shop', ATKP_PLUGIN_PREFIX) ?></th>
                    <th><?php echo esc_html__('Status', ATKP_PLUGIN_PREFIX) ?></th>
                    <th><?php echo esc_html__('Function', ATKP_PLUGIN_PREFIX) ?></th>
                    <th><?php echo esc_html__('Parameter', ATKP_PLUGIN_PREFIX) ?></th>
                    <th><?php echo esc_html__('Last update', ATKP_PLUGIN_PREFIX) ?></th>
                    <th><?php echo esc_html__('Message', ATKP_PLUGIN_PREFIX) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php

                foreach ($entries as $entry) {

                    ?>

                    <tr>
                        <td class="id column-id has-row-actions column-primary" data-colname="ID">
                            <?php echo esc_html__($entry['id'], ATKP_PLUGIN_PREFIX); ?>
                        </td>
                        <td class="queue_id column-id has-row-actions column-primary" data-colname="ID">
                            <?php
                            $queueid = $entry['queue_id'];
                            if ($queueid > 0) {
                                $link = admin_url('admin.php?page=ATKP_viewqueue&action=detail&queueid=' . $queueid);
                                if ($link == null) {
                                    echo esc_html__($queueid, ATKP_PLUGIN_PREFIX);
                                } else {
                                    $title = esc_html__('Queue', ATKP_PLUGIN_PREFIX);

                                    echo '<a href="' . esc_url($link) . '" target="_blank">' . esc_html__($title, ATKP_PLUGIN_PREFIX) . ' (' . esc_html__($queueid, ATKP_PLUGIN_PREFIX) . ')</a>';
                                }
                            }

                            ?>

                        </td>
                        <td class="shop_id column-shop_id" data-colname="Shop">

                            <?php
                            $shopid = $entry['shop_id'];
                            if ($shopid > 0) {
                                $link = get_edit_post_link($shopid);
                                if ($link == null) {
                                    echo esc_html__($shopid, ATKP_PLUGIN_PREFIX);
                                } else {
                                    $title = get_the_title($shopid);

                                    echo '<a href="' . esc_url($link) . '" target="_blank">' . esc_html__($title, ATKP_PLUGIN_PREFIX) . ' (' . esc_html__($shopid, ATKP_PLUGIN_PREFIX) . ')</a>';
                                }
                            }

                            ?>

                        </td>
                        <td class="status column-status" data-colname="Status">
                            <?php

                            switch ($entry['status']) {
                                case atkp_queue_entry_status::SUCCESSFULLY:
                                    echo '<span style="color:green;font-weight:bold;">' . esc_html__('Successfully', ATKP_PLUGIN_PREFIX) . '</span>';
                                    break;
                                case atkp_queue_entry_status::ERROR:
                                    echo '<span style="color:red;font-weight:bold;">' . esc_html__('Error', ATKP_PLUGIN_PREFIX) . '</span>';
                                    break;
                                case atkp_queue_entry_status::NOT_PROCESSED:
                                    echo '<span style="color:orange;font-weight:bold;">' . esc_html__('Not processed', ATKP_PLUGIN_PREFIX) . '</span>';
                                    break;
                                case atkp_queue_entry_status::PROCESSED:
                                    echo '<span style="font-weight:bold;">' . esc_html__('Processed', ATKP_PLUGIN_PREFIX) . '</span>';
                                    break;
                                case atkp_queue_entry_status::FINISHED:
                                    echo '<span style="color:green;font-weight:bold;">' . esc_html__('Finalized', ATKP_PLUGIN_PREFIX) . '</span>';
                                    break;
                                case atkp_queue_entry_status::PREPARED:
                                    echo '<span style="color:orange;font-weight:bold;">' . esc_html__('Prepared for processing', ATKP_PLUGIN_PREFIX) . '</span>';
                                    break;
                            }

                            ?>
                        </td>
                        <td class="functionname column-functionname" data-colname="Function">
                            <?php echo esc_html__($entry['functionname'], ATKP_PLUGIN_PREFIX) ?>
                        </td>
                        <td class="functionparameter column-functionparameter" data-colname="Parameter">
                            <?php echo esc_html__($entry['functionparameter'], ATKP_PLUGIN_PREFIX) ?>
                        </td>
                        <td class="updatedon column-updatedon" data-colname="Last update">
                            <?php echo esc_html__(ATKPTools::get_formatted_date(strtotime($entry['updatedon'])), ATKP_PLUGIN_PREFIX) . esc_html__(' at ', ATKP_PLUGIN_PREFIX) . esc_html__(ATKPTools::get_formatted_time(strtotime($entry['updatedon'])), ATKP_PLUGIN_PREFIX); ?>
                        </td>
                        <td class="updatedmessage column-updatedmessage" data-colname="Message">
                            <?php echo esc_html__($entry['updatedmessage'], ATKP_PLUGIN_PREFIX) ?>
                        </td>
                    </tr>


                    <?php

                }

                ?>
            </tbody>
        </table>

        <?php


    }

    function shop_detail_box_content($post)
    {

        wp_nonce_field(plugin_basename(__FILE__), 'shop_detail_box_content_nonce');

        $selwebservice = ATKPTools::get_post_setting($post->ID, ATKP_SHOP_POSTTYPE . '_access_webservice');

        $alreadysaved = (bool) $selwebservice != '';

        if ($selwebservice == ATKP_SUBSHOPTYPE) {

            $parentid = wp_get_post_parent_id($post->ID);

            if ($parentid == 0) {
                $access_test = '<span style="color:red">parent shop not found: ' . $post->ID . '</span>';
            } else {

                $title = get_the_title($parentid);

                if ($title == '') {
                    $title = esc_html__('open shop', ATKP_PLUGIN_PREFIX);
                }

                $access_test = '<a href="' . esc_url(admin_url('/post.php?post=' . $parentid . '&action=edit')) . '" target="_blank">' . esc_html__($title, ATKP_PLUGIN_PREFIX) . '</a>';

                $shopid = ATKPTools::get_post_setting($post->ID, ATKP_SHOP_POSTTYPE . '_shopid');
                $programid = ATKPTools::get_post_setting($post->ID, ATKP_SHOP_POSTTYPE . '_programid');

                $access_test .= '<pre>';
                $access_test .= '<br />' . esc_html__('Shopid: ', ATKP_PLUGIN_PREFIX) . $shopid;
                $access_test .= '<br />' . esc_html__('Programid: ', ATKP_PLUGIN_PREFIX) . $programid;
                $access_test .= '</pre>';
            }
        } else {
            $error = ATKPTools::get_post_setting($post->ID, ATKP_SHOP_POSTTYPE . '_access_message');


            if (!$alreadysaved) {
                $error = esc_html__('Access data not defined.', ATKP_PLUGIN_PREFIX);
            }

            if (($error == null || empty($error))) {
                $access_test = '<span style="color:green">' . esc_html__('Connected', ATKP_PLUGIN_PREFIX) . '</span>';
            } else {
                $access_test = '<span style="color:red">' . esc_html__('Not connected', ATKP_PLUGIN_PREFIX) . ' (' . esc_html($error) . ')</span>';
            }
        }

        ?>

        <?php $locations = atkp_shop_provider_base::retrieve_providers(); ?>

        <?php

        if (!$locations || count($locations) == 0) {
            ?>
            <div style="text-align:center">
                <a href="https://www.affiliate-toolkit.com/extensions/?_type=connectors-api" class="button-primary" target="_blank"
                    style="padding:10px"><?php echo esc_html__('» Click here to download your first shop extension «', ATKP_PLUGIN_PREFIX) ?></a>
            </div>
            <?php
        } else {
            ?>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="">
                            <?php echo esc_html__('Data supplier', ATKP_PLUGIN_PREFIX) ?> <span
                                class="description"><?php echo esc_html__('(required)', ATKP_PLUGIN_PREFIX) ?></span>
                        </label>
                    </th>
                    <td>

                        <select <?php echo ($alreadysaved ? 'disabled' : '') ?>
                            name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_access_webservice') ?>"
                            id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_access_webservice') ?>">
                            <?php

                            if ($selwebservice == ATKP_SUBSHOPTYPE) {
                                echo '<option value="' . esc_attr(ATKP_SUBSHOPTYPE) . '" selected>' . esc_html__('Subshop', ATKP_PLUGIN_PREFIX) . '</option>';
                            } else {




                                $found = false;
                                foreach ($locations as $value => $provider) {
                                    if ($value == $selwebservice) {
                                        $sel = ' selected';
                                        $found = true;
                                    } else {
                                        $sel = '';
                                    }


                                    echo '<option value="' . esc_attr($value) . '"' . esc_attr($sel) . '>' . esc_textarea($provider->get_caption()) . '</option>';
                                }
                                if (!$found && $alreadysaved) {
                                    echo '<option value="' . esc_attr($selwebservice) . '" selected>' . esc_textarea(sprintf('unknown: %s', $selwebservice)) . '</option>';
                                }

                            }
                            ?>
                        </select>
                        <?php ATKPTools::display_helptext('Please select the API you want to use for this shop. You can download more APIs as extensions.', get_admin_url() . 'admin.php?page=ATKP_affiliate_toolkit-Extensions', esc_html__('View extensions', ATKP_PLUGIN_PREFIX)) ?>


                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="">
                            <?php if ($selwebservice == ATKP_SUBSHOPTYPE) {
                                echo esc_html__('Parent shop', ATKP_PLUGIN_PREFIX);
                            } else {
                                echo esc_html__('Status', ATKP_PLUGIN_PREFIX);
                            } ?>
                        </label>
                    </th>
                    <td>
                        <?php echo wp_kses($access_test, array(
                            'span' => array('style' => array()),
                            'a' => array('href' => array(), 'target' => array()),
                            'pre' => array(),
                            'br' => array()
                        )); ?>
                    </td>
                </tr>
            </table>

            <?php
            if ($selwebservice == ATKP_SUBSHOPTYPE) {

                ?>
                <table class="form-table">



                    <?php

                    $this->output_detail($post);

            } else {

                foreach ($locations as $value => $provider) {

                    if ($alreadysaved) {
                        if ($value != $selwebservice) {
                            continue;
                        }
                    }

                    echo '<div id="api-' . esc_attr($value) . '">';
                    echo '<table class="form-table">';
                    echo esc_html__($provider->get_configuration($post));
                    echo '</table>';
                    echo '</div>';
                }


                ?>
                    <table class="form-table">


                        <?php
                        $s = atkp_shop::load($post->ID);

                        if ($s->type == atkp_shop_type::MULTI_SHOPS) {
                            ?>

                            <tr>
                                <th scope="row">

                                </th>
                                <td>
                                    <input type="checkbox" id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_auto_generate_subshops') ?>"
                                        name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_auto_generate_subshops') ?>"
                                        value="1" <?php echo checked( 1, $s->autogeneratesubshops, true ); ?>>
                                    <label for="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_auto_generate_subshops') ?>">
                                        <?php echo esc_html__('Automatic generation of subshops if a offer was found', ATKP_PLUGIN_PREFIX) ?>
                                    </label>
                                    <?php ATKPTools::display_helptext('If you want that the plugin creates the sub shops automatic (without selecting the shop before) you can enable this option.') ?>
                                </td>
                            </tr>

                            <?php
                        }

                        if ($s->type == atkp_shop_type::MULTI_SHOPS || $s->type == atkp_shop_type::SUB_SHOPS) {
                            ?>
                            <tr>
                                <th scope="row">
                                    <label for="">
                                        <?php echo esc_html__('Subshops', ATKP_PLUGIN_PREFIX) ?>:
                                    </label>
                                </th>
                                <td>
                                    <div style="border:1px solid #ccc; width:600px; height: 250px; overflow-y: scroll;padding:5px"> <?php
                                    $defaultshops = ATKPTools::get_post_setting($post->ID, ATKP_SHOP_POSTTYPE . '_default_shops');

                                    $selectedshops = ATKPTools::get_post_setting($post->ID, ATKP_SHOP_POSTTYPE . '_selected_shops');
                                    $allselected = true;
                                    if (is_array($defaultshops)) {
                                        foreach ($defaultshops as $subshop) {
                                            $found = false;
                                            if (is_array($selectedshops)) {
                                                foreach ($selectedshops as $selectedsubshop) {
                                                    if ($subshop->shopid == $selectedsubshop->shopid && $subshop->programid == $selectedsubshop->programid) {
                                                        $found = true;
                                                        break;
                                                    }
                                                }
                                            } else {
                                                //wenn selectedshop nicht gesetzt ist dann ist es noch von der alten subshop logik..
                                                if ($subshop->enabled) {
                                                    $found = true;
                                                }
                                            }

                                            if (!$found) {
                                                $allselected = false;
                                            }

                                            ?>


                                                <input class="atkp-subshop" type="checkbox"
                                                    id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_subshop-' . $subshop->shopid . '-' . $subshop->programid) ?>"
                                                    name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_subshop-' . $subshop->shopid . '-' . $subshop->programid) ?>"
                                                    value="1" <?php echo checked(1, $found, true); ?> />
                                                <label
                                                    for="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_subshop-' . $subshop->shopid . '-' . $subshop->programid) ?>">
                                                    <?php echo esc_html__($subshop->title, ATKP_PLUGIN_PREFIX) . ($subshop->title2 != '' ? ' (' . esc_html__($subshop->title2, ATKP_PLUGIN_PREFIX) . ')' : '') ?>
                                                </label><br />

                                                <?php
                                        }
                                    }
                                    ?>


                                    </div>

                                    <input type="checkbox" id="atkp-selectall" name="atkp-selectall" value="1" <?php echo checked(1, $allselected, true); ?> />
                                    <label for="atkp-selectall">
                                        <?php echo esc_html__('Select all', ATKP_PLUGIN_PREFIX) ?>
                                    </label>

                                </td>
                            </tr>

                            <?php

                        } else if ($alreadysaved) {
                            $this->output_detail($post);
                        }
                        if ($alreadysaved) {
                            ?>


                            <tr>
                                <th scope="row">

                                </th>
                                <td>
                                    <input type="checkbox" id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_displayshoplogo') ?>"
                                        name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_displayshoplogo') ?>" value="1" <?php echo checked(1, ATKPTools::get_post_setting($post->ID, ATKP_SHOP_POSTTYPE . '_displayshoplogo'), false); ?>>
                                    <label for="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_displayshoplogo') ?>">
                                        <?php echo esc_html__('Display shop logo', ATKP_PLUGIN_PREFIX) ?>
                                    </label>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">

                                </th>
                                <td>
                                    <input type="checkbox" <?php echo !ATKPTools::has_eanpricecompare($selwebservice) ? 'disabled' : '' ?>
                                        id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_enableofferload') ?>"
                                        name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_enableofferload') ?>" value="1" <?php echo checked(1, ATKPTools::get_post_setting($post->ID, ATKP_SHOP_POSTTYPE . '_enableofferload')); ?>>
                                    <label for="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_enableofferload') ?>">
                                        <?php echo esc_html__('Automatic loading of offers for price comparison', ATKP_PLUGIN_PREFIX) ?>
                                    </label>
                                    <?php ATKPTools::display_helptext('If you want that the plugin searches for more offers (by EAN, GTIN and ISBN) you can enable this option.') ?>
                                </td>
                            </tr>


                            <tr>
                                <th scope="row">
                                    <label for="">
                                        <?php echo esc_html__('Tooltip', ATKP_PLUGIN_PREFIX) ?>
                                    </label>
                                </th>
                                <td>
                                    <input style="width:30%" type="text" id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_text_tooltip') ?>"
                                        name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_text_tooltip') ?>"
                                        value="<?php echo esc_attr(ATKPTools::get_post_setting($post->ID, ATKP_SHOP_POSTTYPE . '_text_tooltip')); ?>">
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="">
                                        <?php echo esc_html__('"Buy at" button', ATKP_PLUGIN_PREFIX) . ' (html)' ?>
                                    </label>
                                </th>
                                <td>
                                    <input style="width:30%" type="text" id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_text_buyat') ?>"
                                        name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_text_buyat') ?>"
                                        value="<?php echo esc_attr(ATKPTools::get_post_setting($post->ID, ATKP_SHOP_POSTTYPE . '_text_buyat')); ?>">
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="">
                                        <?php echo esc_html__('"Add to Cart" button', ATKP_PLUGIN_PREFIX) . ' (html)' ?>
                                    </label>
                                </th>
                                <td>
                                    <input style="width:30%" type="text" id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_text_addtocart') ?>"
                                        name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_text_addtocart') ?>"
                                        value="<?php echo esc_attr(ATKPTools::get_post_setting($post->ID, ATKP_SHOP_POSTTYPE . '_text_addtocart')); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="">
                                        <?php echo esc_html__('Currency symbol', ATKP_PLUGIN_PREFIX) ?>
                                    </label>
                                </th>
                                <td>
                                    <select id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_currencysign') ?>"
                                        name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_currencysign') ?>" style="width:300px">
                                        <?php
                                        $selected = ATKPTools::get_post_setting($post->ID, ATKP_SHOP_POSTTYPE . '_currencysign');

                                        echo '<option value="1" ' . ($selected == '' || $selected == 1 ? 'selected' : '') . ' >' . esc_html__('&euro; symbol', ATKP_PLUGIN_PREFIX) . '</option>';

                                        echo '<option value="2" ' . ($selected == 2 ? 'selected' : '') . '>' . esc_html__('EUR', ATKP_PLUGIN_PREFIX) . '</option>';

                                        echo '<option value="3" ' . ($selected == 3 ? 'selected' : '') . '>' . esc_html__('&#36; symbol', ATKP_PLUGIN_PREFIX) . '</option>';

                                        echo '<option value="4" ' . ($selected == 4 ? 'selected' : '') . '>' . esc_html__('USD', ATKP_PLUGIN_PREFIX) . '</option>';

                                        echo '<option value="5" ' . ($selected == 5 ? 'selected' : '') . '>' . esc_html__('Use format from merchant', ATKP_PLUGIN_PREFIX) . '</option>';
                                        echo '<option value="6" ' . ($selected == 6 ? 'selected' : '') . '>' . esc_html__('Custom Sign', ATKP_PLUGIN_PREFIX) . '</option>';

                                        ?>

                                    </select>
                                    <div id="customcurrencysign"><br />
                                        <input style="width:40px" type="text"
                                            id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_currencysign_customprefix') ?>"
                                            name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_currencysign_customprefix') ?>"
                                            value="<?php echo esc_attr(ATKPTools::get_post_setting($post->ID, ATKP_SHOP_POSTTYPE . '_currencysign_customprefix')); ?>">
                                        0,00 <input style="width:40px" type="text"
                                            id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_currencysign_customsuffix') ?>"
                                            name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_currencysign_customsuffix') ?>"
                                            value="<?php echo esc_attr(ATKPTools::get_post_setting($post->ID, ATKP_SHOP_POSTTYPE . '_currencysign_customsuffix')); ?>">
                                    </div>
                                </td>
                            </tr>







                            <tr>
                                <th scope="row">
                                    <label for="">
                                        <?php echo esc_html__('Redirection type', ATKP_PLUGIN_PREFIX) ?>:
                                    </label>
                                </th>
                                <td>
                                    <select id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_redirectiontype') ?>"
                                        name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_redirectiontype') ?>" style="width:300px">
                                        <?php
                                        $selected = ATKPTools::get_post_setting($post->ID, ATKP_SHOP_POSTTYPE . '_redirectiontype');

                                        echo '<option value="' . esc_attr(atkp_redirection_type::DISABLED) . '" ' . ($selected == '' || $selected == 1 || $selected == atkp_redirection_type::DISABLED ? 'selected' : '') . ' >' . esc_html__('Disabled', ATKP_PLUGIN_PREFIX) . '</option>';

                                        echo '<option value="' . esc_attr(atkp_redirection_type::INTERNAL_REDIRECTION) . '" ' . ($selected == atkp_redirection_type::INTERNAL_REDIRECTION ? 'selected' : '') . '>' . esc_html__('internal redirection', ATKP_PLUGIN_PREFIX) . '</option>';
                                        echo '<option value="' . esc_attr(atkp_redirection_type::INTERNAL_REDIRECTION_NAME) . '" ' . ($selected == atkp_redirection_type::INTERNAL_REDIRECTION_NAME ? 'selected' : '') . '>' . esc_html__('internal redirection by name', ATKP_PLUGIN_PREFIX) . '</option>';
                                        echo '<option value="' . esc_attr(atkp_redirection_type::BIT_LY) . '" ' . ($selected == atkp_redirection_type::BIT_LY ? 'selected' : '') . '>' . esc_html__('bit.ly shortener', ATKP_PLUGIN_PREFIX) . '</option>';
                                        ?>

                                    </select>
                                    <div id="customapikey"><br />
                                        <label for="">
                                            <?php echo esc_html__('Api key', ATKP_PLUGIN_PREFIX) ?>
                                        </label>
                                        <input style="width:30%" type="text" id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_apikey') ?>"
                                            name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_apikey') ?>"
                                            value="<?php echo esc_attr(ATKPTools::get_post_setting($post->ID, ATKP_SHOP_POSTTYPE . '_apikey')); ?>">
                                    </div>
                                    <?php ATKPTools::display_helptext('You can shorten the URLs from this shop. We don\'t recommend this because some affiliate networks are decline it.') ?>
                                </td>
                            </tr>

                        <?php }
            }

            do_action('atkp_shop_after_fields', $post->ID);
            ?>


                </table>
            <?php } ?>



            <script type="text/javascript">
                var $j = jQuery.noConflict();
                /*
                 * Attaches the image uploader to the input field
                 */
                $j(document).ready(function ($) {


                    //selectall
                    //atkp-subshop

                    $j('#atkp-selectall').change(function () {
                        var val = $(this).is(':checked');

                        $j('.atkp-subshop').prop('checked', val);


                    });


                    $j('#<?php echo esc_js(ATKP_SHOP_POSTTYPE . '_currencysign') ?>').change(function () {

                        if ($j('#<?php echo esc_js(ATKP_SHOP_POSTTYPE . '_currencysign') ?>').val() == '6')
                            $j('#customcurrencysign').show();
                        else
                            $j('#customcurrencysign').hide();
                    });

                    $j('#<?php echo esc_js(ATKP_SHOP_POSTTYPE . '_currencysign') ?>').trigger("change");


                    $j('#<?php echo esc_js(ATKP_SHOP_POSTTYPE . '_redirectiontype') ?>').change(function () {

                        if ($j('#<?php echo esc_js(ATKP_SHOP_POSTTYPE . '_redirectiontype') ?>').val() == '3')
                            $j('#customapikey').show();
                        else
                            $j('#customapikey').hide();
                    });

                    $j('#<?php echo esc_js(ATKP_SHOP_POSTTYPE . '_redirectiontype') ?>').trigger("change");

                    $j('#<?php echo esc_js(ATKP_SHOP_POSTTYPE . '_access_webservice') ?>').change(function () {

                        switch ($j('#<?php echo esc_js(ATKP_SHOP_POSTTYPE . '_access_webservice') ?>').val()) {
                                                <?php
                                                if ($selwebservice != ATKP_SUBSHOPTYPE) {
                                                    foreach ($locations as $value => $provider) {

                                                        echo 'case \'' . esc_html($value) . '\':';
                                                        foreach ($locations as $value2 => $provider2) {
                                                            if ($value2 == $value) {
                                                                echo '$j(\'#api-' . esc_html($value2) . '\').show();';
                                                            } else {
                                                                echo '$j(\'#api-' . esc_html($value2) . '\').hide();';
                                                            }
                                                        }
                                                        echo 'break;';
                                                    }
                                                }
                                                ?>



                    }





                                    });

                $j('#<?php echo esc_js(ATKP_SHOP_POSTTYPE . '_access_webservice') ?>').trigger("change");


                                });

            </script>

            <?php
    }

    function output_detail($post)
    {
        $customtitle = ATKPTools::get_post_setting($post->ID, ATKP_SHOP_POSTTYPE . '_customtitle');
        $customsmalllogourl = ATKPTools::get_post_setting($post->ID, ATKP_SHOP_POSTTYPE . '_customsmalllogourl');
        $customlogourl = ATKPTools::get_post_setting($post->ID, ATKP_SHOP_POSTTYPE . '_customlogourl');


        $feedurl = ATKPTools::get_post_setting($post->ID, ATKP_SHOP_POSTTYPE . '_feedurl');
        $productcount = ATKPTools::get_post_setting($post->ID, ATKP_SHOP_POSTTYPE . '_productcount');
        $customfield1 = ATKPTools::get_post_setting($post->ID, ATKP_SHOP_POSTTYPE . '_customfield1');
        $customfield2 = ATKPTools::get_post_setting($post->ID, ATKP_SHOP_POSTTYPE . '_customfield2');
        $customfield3 = ATKPTools::get_post_setting($post->ID, ATKP_SHOP_POSTTYPE . '_customfield3');
        $chartcolor = ATKPTools::get_post_setting($post->ID, ATKP_SHOP_POSTTYPE . '_chartcolor');
        $ontop = intval(ATKPTools::get_post_setting($post->ID, ATKP_SHOP_POSTTYPE . '_holdshopontop'));

        $hidepricecomparision = intval(ATKPTools::get_post_setting($post->ID, ATKP_SHOP_POSTTYPE . '_hidepricecomparision'));

        $subshop = atkp_shop::load($post->ID);

        //$subshops=  ATKPTools::get_post_setting( $post->ID, ATKP_SHOP_POSTTYPE.'_default_shops');

        // if(is_array($subshops))
        //   var_dump($subshops);

        //if(is_array($subshops))
        //$subshop =  $shps[0];
        ?>

            <tr>
                <th scope="row">
                    <label for="">
                        <?php echo esc_html__('Title', ATKP_PLUGIN_PREFIX) ?>
                    </label>
                </th>
                <td>
                    <input style="width:40%" type="text" id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_customtitle') ?>"
                        name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_customtitle') ?>"
                        value="<?php echo esc_attr($customtitle == '' && isset($subshop) ? $subshop->get_title() : $customtitle); ?>">
                </td>
            </tr>

            <tr>

                <th scope="row">
                    <label for="">
                        <?php echo esc_html__('Shop Logo Small', ATKP_PLUGIN_PREFIX) ?>
                    </label>
                </th>
                <td>
                    <?php
                    $smallimageurl = '';
                    $logourl = '';

                    if (isset($subshop)) {
                        $smallimageurl = $subshop->smalllogourl;
                        $logourl = $subshop->logourl;
                    }
                    if ($customsmalllogourl != '') {

                        $smallimageurl = $customsmalllogourl;
                    }
                    if ($customlogourl != '') {
                        $logourl = $customlogourl;
                    }

                    if ($smallimageurl != '') {
                        ?>
                        <img id="logosmall-preview" src="<?php echo esc_url($smallimageurl); ?>" style="max-width:250px"><br />
                    <?php } ?>

                    <input style="width:40%" type="url" id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_customsmalllogourl') ?>"
                        name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_customsmalllogourl') ?>"
                        value="<?php echo esc_attr($smallimageurl); ?>">
                    <input type="button" id="smallimage-button" class="button meta-image-button"
                        value="<?php echo esc_html__('Choose or Upload an image', ATKP_PLUGIN_PREFIX) ?>" />
                </td>
            </tr>

            <tr>

                <th scope="row">
                    <label for="">
                        <?php echo esc_html__('Shop Logo Large', ATKP_PLUGIN_PREFIX) ?>
                    </label>
                </th>
                <td>
                    <?php if ($logourl != '') {
                        ?>
                        <img id="logo-preview" src="<?php echo esc_url($logourl); ?>" style="max-width:250px"><br />
                    <?php } ?>

                    <input style="width:40%" type="url" id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_customlogourl') ?>"
                        name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_customlogourl') ?>"
                        value="<?php echo esc_attr($logourl); ?>">
                    <input type="button" id="largeimage-button" class="button meta-image-button"
                        value="<?php echo esc_html__('Choose or Upload an image', ATKP_PLUGIN_PREFIX) ?>" />



                    <script type="text/javascript">
                        var $j = jQuery.noConflict();
                        /*
                         * Attaches the image uploader to the input field
                         */
                        $j(document).ready(function ($) {

                            // Instantiates the variable that holds the media library frame.
                            var meta_image_frame;
                            var image_button;
                            // Runs when the image button is clicked.
                            $j('.meta-image-button').click(function (e) {

                                // Prevents the default action from occuring.
                                e.preventDefault();

                                // If the frame already exists, re-open it.
                                //if ( meta_image_frame ) {
                                //    meta_image_frame.open();
                                //    return;
                                //}

                                // Sets up the media library frame
                                meta_image_frame = wp.media.frames.meta_image_frame = wp.media({
                                    title: meta_image.title,
                                    button: { text: meta_image.button },
                                    library: { type: 'image' }
                                });

                                image_button = $j(this).attr('id');

                                // Runs when an image is selected.
                                meta_image_frame.on('select', function () {

                                    // Grabs the attachment selection and creates a JSON representation of the model.
                                    var media_attachment = meta_image_frame.state().get('selection').first().toJSON();

                                    // Sends the attachment URL to our custom image input field.
                                    if (image_button == $j('#smallimage-button').attr('id'))
                                        $j('#<?php echo esc_js(ATKP_SHOP_POSTTYPE . '_customsmalllogourl') ?>').val(media_attachment.url);
                                    else if (image_button == $j('#largeimage-button').attr('id'))
                                        $j('#<?php echo esc_js(ATKP_SHOP_POSTTYPE . '_customlogourl') ?>').val(media_attachment.url);
                                });

                                // Opens the media library frame.
                                meta_image_frame.open();
                            });

                            if ($j('.atkp-colorpicker').wpColorPicker != null)
                                $j('.atkp-colorpicker').wpColorPicker();

                        });
                    </script>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_holdshopontop') ?>">
                        <?php echo esc_html__('Override Price comparision Sort order (default: 100)', ATKP_PLUGIN_PREFIX) ?>
                    </label>
                </th>
                <td>
                    <input type="number" min="1" max="200" id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_holdshopontop') ?>"
                        name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_holdshopontop') ?>"
                        value="<?php echo esc_attr($ontop <= 0 ? 100 : $ontop) ?>">
                    <?php ATKPTools::display_helptext('If you change the value to a lower value than 100 the shop is on top. If you cange it to a higher value than 100 it\'s at the end') ?>
                </td>
            </tr>
            <tr>

                <th scope="row">
                    <label for="">
                        <?php echo esc_html__('Chart color', ATKP_PLUGIN_PREFIX) ?>
                    </label>
                </th>
                <td>
                    <input style="width:40%" type="text" class="atkp-colorpicker"
                        id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_chartcolor') ?>"
                        name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_chartcolor') ?>"
                        value="<?php echo esc_attr($chartcolor); ?>">
                </td>
            </tr>

            <?php
            if ($feedurl != '') {
                ?>

                <tr>

                    <th scope="row">
                        <label for="">
                            <?php echo esc_html__('Feedurl', ATKP_PLUGIN_PREFIX) ?>
                        </label>
                    </th>
                    <td>
                        <input style="width:40%" readonly type="text" id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_feedurl') ?>"
                            name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_feedurl') ?>" value="<?php echo esc_attr($feedurl); ?>">
                    </td>
                </tr>

                <tr>

                    <th scope="row">
                        <label for="">
                            <?php echo esc_html__('Product count', ATKP_PLUGIN_PREFIX) ?>
                        </label>
                    </th>
                    <td>
                        <input style="width:40%" readonly type="text"
                            id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_productcount') ?>"
                            name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_productcount') ?>"
                            value="<?php echo esc_attr($productcount); ?>">
                    </td>
                </tr>


            <?php } ?>
            <tr>

                <th scope="row">
                    <label for="">
                        <?php echo esc_html__('Hide products form this shop', ATKP_PLUGIN_PREFIX) ?>
                    </label>
                </th>
                <td>
                    <input readonly type="checkbox" id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_hidepricecomparision') ?>"
                        name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_hidepricecomparision') ?>" value="1" <?php echo checked(1, $hidepricecomparision, true); ?> />
                </td>
            </tr>

            <tr>

                <th scope="row">
                    <label for="">
                        <?php echo esc_html__('Custom Field 1 (html)', ATKP_PLUGIN_PREFIX) ?>
                    </label>
                </th>
                <td>
                    <input style="width:40%" type="text" id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_customfield1') ?>"
                        name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_customfield1') ?>"
                        value="<?php echo esc_attr($customfield1); ?>">
                </td>
            </tr>

            <tr>

                <th scope="row">
                    <label for="">
                        <?php echo esc_html__('Custom Field 2 (html)', ATKP_PLUGIN_PREFIX) ?>
                    </label>
                </th>
                <td>
                    <input style="width:40%" type="text" id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_customfield2') ?>"
                        name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_customfield2') ?>"
                        value="<?php echo esc_attr($customfield2); ?>">
                </td>
            </tr>

            <tr>

                <th scope="row">
                    <label for="">
                        <?php echo esc_html__('Custom Field 3 (html)', ATKP_PLUGIN_PREFIX) ?>
                    </label>
                </th>
                <td>
                    <input style="width:40%" type="text" id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_customfield3') ?>"
                        name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_customfield3') ?>"
                        value="<?php echo esc_attr($customfield3); ?>">
                </td>
            </tr>
            <?php


    }

    public $save_child = false;

    function list_detail_save($post_id)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        $nounce = ATKPTools::get_post_parameter('shop_detail_box_content_nonce', 'string');

        if (!wp_verify_nonce($nounce, plugin_basename(__FILE__))) {
            return;
        }

        if ($this->save_child) {
            return;
        }

        $post = get_post($post_id);

        $posttype = $post->post_type; //ATKPTools::get_post_parameter('post_type', 'string');

        if (ATKP_SHOP_POSTTYPE != $posttype) {
            return;
        }

        if (isset($_POST['atkp_paste_settings']) && $_POST['atkp_paste_settings'] != '') {
            $import_settings = $_POST['atkp_paste_settings'];

            $x = json_decode(stripslashes($import_settings));

            if ($x) {
                $fields = array_keys(get_object_vars($x));

                foreach ($fields as $field) {
                    if ($field == 'post_id') {

                    } else if ($field == 'post_title') {
                        //TODO: Set post title
                    } else {
                        $unval = is_array($x->$field) ? (count($x->$field) > 0 ? $x->$field[0] : null) : $x->$field;

                        if ($unval != null) {
                            require_once(ATKP_PLUGIN_DIR . '/includes/shopproviders/subshop.php');

                            $data = @unserialize($unval);

                            if ($data !== false) {
                                $unval = $data;
                            }
                        }
                        update_post_meta($post_id, $field, $unval);
                    }
                }

                //do_action('atkp_shop_save_fields', $post_id);
                return;
            }

        }

        //speichern der einstellungen
        $message = '';

        ATKPTools::set_post_setting($post_id, ATKP_SHOP_POSTTYPE . '_access_message', esc_html__('Connecting...', ATKP_PLUGIN_PREFIX));

        $webservice = ATKPTools::get_post_setting($post_id, ATKP_SHOP_POSTTYPE . '_access_webservice');

        if ($webservice == '' || $webservice == null) {
            $webservice = ATKPTools::get_post_parameter(ATKP_SHOP_POSTTYPE . '_access_webservice', 'string');
            ATKPTools::set_post_setting($post_id, ATKP_SHOP_POSTTYPE . '_access_webservice', $webservice);
        }

        if ($webservice != '' && $webservice != ATKP_SUBSHOPTYPE) {
            $myprovider = atkp_shop_provider_base::retrieve_provider($webservice);

            if ($myprovider == null) {
                throw new Exception(esc_html__('provider not found: ' . $webservice, ATKP_PLUGIN_PREFIX));
            }

            $myprovider->set_configuration($post_id);

            $message = $myprovider->check_configuration($post_id);
        }

        if (ATKPTools::exists_post_parameter(ATKP_SHOP_POSTTYPE . '_customtitle')) {
            ATKPTools::set_post_setting($post_id, ATKP_SHOP_POSTTYPE . '_customtitle', ATKPTools::get_post_parameter(ATKP_SHOP_POSTTYPE . '_customtitle', 'string'));
        }

        if (ATKPTools::exists_post_parameter(ATKP_SHOP_POSTTYPE . '_customfield1')) {
            ATKPTools::set_post_setting($post_id, ATKP_SHOP_POSTTYPE . '_customfield1', ATKPTools::get_post_parameter(ATKP_SHOP_POSTTYPE . '_customfield1', 'html'));
        }
        if (ATKPTools::exists_post_parameter(ATKP_SHOP_POSTTYPE . '_customfield2')) {
            ATKPTools::set_post_setting($post_id, ATKP_SHOP_POSTTYPE . '_customfield2', ATKPTools::get_post_parameter(ATKP_SHOP_POSTTYPE . '_customfield2', 'html'));
        }
        if (ATKPTools::exists_post_parameter(ATKP_SHOP_POSTTYPE . '_customfield3')) {
            ATKPTools::set_post_setting($post_id, ATKP_SHOP_POSTTYPE . '_customfield3', ATKPTools::get_post_parameter(ATKP_SHOP_POSTTYPE . '_customfield3', 'html'));
        }
        ATKPTools::set_post_setting($post_id, ATKP_SHOP_POSTTYPE . '_holdshopontop', ATKPTools::get_post_parameter(ATKP_SHOP_POSTTYPE . '_holdshopontop', 'int'));

        if (ATKPTools::exists_post_parameter(ATKP_SHOP_POSTTYPE . '_chartcolor')) {
            ATKPTools::set_post_setting($post_id, ATKP_SHOP_POSTTYPE . '_chartcolor', ATKPTools::get_post_parameter(ATKP_SHOP_POSTTYPE . '_chartcolor', 'string'));
        }



        if (ATKPTools::exists_post_parameter(ATKP_SHOP_POSTTYPE . '_currencysign')) {

            ATKPTools::set_post_setting($post_id, ATKP_SHOP_POSTTYPE . '_displayshoplogo', ATKPTools::get_post_parameter(ATKP_SHOP_POSTTYPE . '_displayshoplogo', 'bool'));
            ATKPTools::set_post_setting($post_id, ATKP_SHOP_POSTTYPE . '_enableofferload', ATKPTools::get_post_parameter(ATKP_SHOP_POSTTYPE . '_enableofferload', 'bool'));
            ATKPTools::set_post_setting($post_id, ATKP_SHOP_POSTTYPE . '_auto_generate_subshops', ATKPTools::get_post_parameter(ATKP_SHOP_POSTTYPE . '_auto_generate_subshops', 'bool'));


        } else {
            $val = ATKPTools::get_post_setting($post_id, ATKP_SHOP_POSTTYPE . '_displayshoplogo', 'hey');
            if ($val == 'hey')
                ATKPTools::set_post_setting($post_id, ATKP_SHOP_POSTTYPE . '_displayshoplogo', true);
        }


        ATKPTools::set_post_setting($post_id, ATKP_SHOP_POSTTYPE . '_hidepricecomparision', ATKPTools::get_post_parameter(ATKP_SHOP_POSTTYPE . '_hidepricecomparision', 'bool'));


        $redirectiontype = ATKPTools::get_post_parameter(ATKP_SHOP_POSTTYPE . '_redirectiontype', 'int');
        ATKPTools::set_post_setting($post_id, ATKP_SHOP_POSTTYPE . '_redirectiontype', $redirectiontype);

        $apikey = ATKPTools::get_post_parameter(ATKP_SHOP_POSTTYPE . '_apikey', 'string');
        ATKPTools::set_post_setting($post_id, ATKP_SHOP_POSTTYPE . '_apikey', $apikey);

        $tooltip = ATKPTools::get_post_parameter(ATKP_SHOP_POSTTYPE . '_text_tooltip', 'string');

        $buyattext = ATKPTools::get_post_parameter(ATKP_SHOP_POSTTYPE . '_text_buyat', 'html');
        $addtocarttext = ATKPTools::get_post_parameter(ATKP_SHOP_POSTTYPE . '_text_addtocart', 'html');


        $small_logo_url = ATKPTools::get_post_parameter(ATKP_SHOP_POSTTYPE . '_customsmalllogourl', 'url');
        $logo_url = ATKPTools::get_post_parameter(ATKP_SHOP_POSTTYPE . '_customlogourl', 'url');

        if (isset($myprovider)) {
            if ($buyattext == null || $buyattext == '') {
                $buyattext = $myprovider->get_defaultbtn1_text();
            }

            if ($addtocarttext == null || $addtocarttext == '') {
                $addtocarttext = $myprovider->get_defaultbtn2_text();
            }

            if ($tooltip == null || $tooltip == '') {
                $tooltip = esc_html__('Buy now at %s', ATKP_PLUGIN_PREFIX);
            }

            if ($small_logo_url == null || $small_logo_url == '') {
                $small_logo_url = $myprovider->get_default_small_logo($post_id);
            }
            if ($logo_url == null || $logo_url == '') {
                $logo_url = $myprovider->get_default_logo($post_id);
            }

        }

        ATKPTools::set_post_setting($post_id, ATKP_SHOP_POSTTYPE . '_text_tooltip', $tooltip);
        ATKPTools::set_post_setting($post_id, ATKP_SHOP_POSTTYPE . '_text_buyat', $buyattext);
        ATKPTools::set_post_setting($post_id, ATKP_SHOP_POSTTYPE . '_text_addtocart', $addtocarttext);

        ATKPTools::set_post_setting($post_id, ATKP_SHOP_POSTTYPE . '_customsmalllogourl', $small_logo_url);
        ATKPTools::set_post_setting($post_id, ATKP_SHOP_POSTTYPE . '_customlogourl', $logo_url);

        if (ATKPTools::exists_post_parameter(ATKP_SHOP_POSTTYPE . '_currencysign')) {
            ATKPTools::set_post_setting($post_id, ATKP_SHOP_POSTTYPE . '_currencysign', ATKPTools::get_post_parameter(ATKP_SHOP_POSTTYPE . '_currencysign', 'string'));
        }

        if (ATKPTools::exists_post_parameter(ATKP_SHOP_POSTTYPE . '_currencysign_customprefix')) {
            ATKPTools::set_post_setting($post_id, ATKP_SHOP_POSTTYPE . '_currencysign_customprefix', ATKPTools::get_post_parameter(ATKP_SHOP_POSTTYPE . '_currencysign_customprefix', 'string'));
        }
        if (ATKPTools::exists_post_parameter(ATKP_SHOP_POSTTYPE . '_currencysign_customsuffix')) {
            ATKPTools::set_post_setting($post_id, ATKP_SHOP_POSTTYPE . '_currencysign_customsuffix', ATKPTools::get_post_parameter(ATKP_SHOP_POSTTYPE . '_currencysign_customsuffix', 'string'));
        }

        if ($webservice != ATKP_SUBSHOPTYPE) {
            $defaultshops = ATKPTools::get_post_setting($post_id, ATKP_SHOP_POSTTYPE . '_default_shops');

            /** @var $selectedshops atkp_shop[] */
            $selectedshops = array();

            if (is_array($defaultshops)) {
                foreach ($defaultshops as $subshop) {

                    if (ATKPTools::get_post_parameter(ATKP_SHOP_POSTTYPE . '_subshop-' . $subshop->shopid . '-' . $subshop->programid, 'bool') == true) {
                        $selectedshops[] = $subshop;
                    }
                }
            }
            ATKPTools::set_post_setting($post_id, ATKP_SHOP_POSTTYPE . '_selected_shops', $selectedshops);

            $subshops_saved = array();

            wp_suspend_cache_addition(true);

            if (is_array($selectedshops)) {
                foreach ($selectedshops as $subshop) {

                    $subshop->parent_id = $post_id;
                    $this->save_child = true;
                    $subshopid = atkp_shop::create_subshop($subshop);
                    $this->save_child = false;
                    $subshops_saved[] = $subshopid;
                }
            }

            wp_suspend_cache_addition(false);

            $subshops_old = get_posts(array(
                'fields' => 'ids',
                'post_parent' => $post_id,
                'post_type' => ATKP_SHOP_POSTTYPE,
                'numberposts' => -1,
                'post_status' => array('draft', 'publish'),
                'exclude' => $subshops_saved
            ));

            foreach ($subshops_old as $subshop_id) {

                wp_trash_post($subshop_id);
            }
        }

        ATKPTools::set_post_setting($post_id, ATKP_SHOP_POSTTYPE . '_access_message', $message);


        do_action('atkp_shop_save_fields', $post_id);
    }

}

?>