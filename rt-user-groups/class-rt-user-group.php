<?php

/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of class-rt-user-groups
 *
 * @author udit
 */
//add_action( 'plugins_loaded', array( 'RT_User_Group', 'load' ), 1 );

if ( ! class_exists( 'RT_User_Group' ) ) {

    class RT_User_Group {

        /**
         * @var $user_texonomy_slug - User taxonomy slug
         */
        var $user_texonomy_slug = '';

        /**
         * @var $user_texonomy_slug - User taxonomy slug
         */
        var $labels = array();
        
        /**
         * @var $user_texonomy_slug - User taxonomy slug
         */
        var $caps = array();
        
        /**
         * Constructor
         * 
         * @global type $user_group_model
         * @param type $slug
         * @param type $label
         */
        public function __construct( $slug, $label, $caps) {
            global $user_group_model;

            $this->user_texonomy_slug = $slug;
            $this->labels = $label;
            $this->caps = $caps;

            $this->auto_loader();

            $this->init();

            $this->db_upgrade();

            add_action( 'init', array( &$this, 'register_user_taxonomy' ) );
            add_action( 'create_term', array( &$this, 'save_user_taxonomy' ), 10, 2 );
            add_action( 'edit_term', array( &$this, 'save_user_taxonomy' ), 10, 2 );

            add_filter( $this->user_texonomy_slug . '_row_actions', array( &$this, 'row_actions' ), 1, 2 );
            add_action( 'manage_' . $this->user_texonomy_slug . '_custom_column', array( &$this, 'manage_user_taxonomy_user_column_ui' ), 10, 3 );
            add_filter( 'manage_edit-' . $this->user_texonomy_slug . '_columns', array( &$this, 'manage_user_taxonomy_user_column' ) );

            add_action( 'admin_print_scripts', array( &$this, 'js_includes' ) );
            add_action( 'admin_print_styles', array( &$this, 'css_includes' ) );
            add_action( 'admin_head', array( &$this, 'colorpicker' ) );
            add_action( 'admin_head', array( &$this, 'hide_slug' ) );

            add_action( 'admin_init', array( &$this, 'add_remove_user_texonomy_field' ), 1000 );

            //User.php
            add_filter( 'manage_users_columns', array( &$this, 'add_taxonomy_columns_user' ), 15, 1 );
            add_action( 'manage_users_custom_column', array( &$this, 'add_taxonomy_columns_user_ui' ), 15, 3 );

            /* Bulk edit */
            add_action( 'admin_init', array( &$this, 'bulk_edit_action' ) );
            add_filter( 'views_users', array( &$this, 'bulk_edit' ) );

            /* Add section to the edit user page in the admin to select profession. */
            add_action( 'show_user_profile', array( &$this, 'edit_user_user_taxonomy_section' ), 99999 );
            add_action( 'edit_user_profile', array( &$this, 'edit_user_user_taxonomy_section' ), 99999 );

            /* Update the user groups when the edit user page is updated. */
            add_action( 'personal_options_update', array( &$this, 'save_user_user_taxonomy' ) );
            add_action( 'edit_user_profile_update', array( &$this, 'save_user_user_taxonomy' ) );

            add_action( 'pre_user_query', array( &$this, 'user_query' ) );

            /* Achieve filtering by User Group. on User page. */
            add_filter( 'views_users', array( &$this, 'user_view_filters' ) );

            add_action( 'delete_user', array( &$this, 'delete_term_relationships' ) );
            add_filter( 'sanitize_user', array( &$this, 'disable_username' ) );

        }

        /**
         * Auto loader for model classes
         */
        function auto_loader() {
            $this->auto_loader = new RT_WP_Autoload( trailingslashit( dirname( __FILE__ ) ) . 'model/' );
        }

        /**
         * Init helper objects
         * @global RT_User_Group_Relationships_Model $rtlib_user_group_model
         */
        function init(){
            global $rtlib_user_group_model;
            $rtlib_user_group_model = new RT_User_Group_Relationships_Model();
        }

        /**
         * Update database for user taxonomy
         */
        function db_upgrade() {
            $updateDB = new  RT_DB_Update( WP_HELPER_FILE, trailingslashit( dirname( __FILE__ ) ) . 'schema/' );
            $updateDB->db_version_option_name .= '_ATTRIBUTES';
            $updateDB->install_db_version = $updateDB->get_install_db_version();
            $updateDB->do_upgrade();
        }
        
        /**
         * Register User taxonomy
         */
        function register_user_taxonomy() {

            $arg = array(
                'public' => TRUE,
                'labels' => $this->labels,
                'rewrite' => false,
                'capabilities' => $this->caps,
            );

            $supports = apply_filters('rtbiz_department_support', array( 'user' ) );
            register_taxonomy( $this->user_texonomy_slug, $supports, $arg );
        }

        /**
         * Save User taxonomy
         * @param type $term_id
         */
        function save_user_taxonomy( $term_id )
        {
            if ( isset( $_POST[ $this->user_texonomy_slug ] ) ){
                $term_meta = (array)get_option( $this->user_texonomy_slug . '-meta' );
                $term_meta[ $term_id ] = (array)$_POST[ $this->user_texonomy_slug ];
                update_option( $this->user_texonomy_slug . '-meta', $term_meta );
                if ( isset( $_POST[ '_wp_original_http_referer' ] ) ){
                    wp_safe_redirect( $_POST[ '_wp_original_http_referer' ] );
                    exit();
                }
            }
        }

        /**
         * add view acrion for taxonomy
         */
        function row_actions( $actions, $term )
        {
            $actions[ 'view' ] = sprintf( __( '%sView%s', 'user-groups' ), '<a href="' . add_query_arg( array( $this->user_texonomy_slug => $term->slug ), admin_url( 'users.php' ) ) . '">', '</a>' );

            return $actions;
        }

        /**
         * add taxonomy List View columns
         * @param type $columns
         * @return type
         */
        function manage_user_taxonomy_user_column( $columns )
        {

            unset( $columns[ 'posts' ], $columns[ 'slug' ] );

            $columns[ 'users' ]         = __( 'Users', 'user-group' );
            $columns[ 'color' ]         = __( 'Color', 'user-group' );
            $columns[ 'email_address' ] = __( 'Email Address', 'user-group' );

            return $columns;
        }

        /**
         * UI for taxonomy List View custom Columns
         * @param type $display
         * @param type $column
         * @param type $term_id
         * @return type
         */
        function manage_user_taxonomy_user_column_ui( $display, $column, $term_id )
        {
            switch ( $column ) {
                case 'users':
                    $term = get_term( $term_id, $this->user_texonomy_slug );
                    echo '<a href="' . admin_url( 'users.php?' . $this->user_texonomy_slug . '=' . $term->slug ) . '">' . sprintf( _n( __( '%s User' ), __( '%s Users' ), $term->count ), $term->count ) . '</a>';
                    break;
                case 'color':
                    $color = self::get_taxonomy_meta( 'group-color', $term_id );
                    if ( ! empty( $color ) ){
                            echo '<div style="width:3.18em; height:3em; background-color:' . self::get_taxonomy_meta( 'group-color', $term_id ) . ';"></div>';
                    }
                    break;
                case 'email_address';
                    $term_meta     = get_option( 'user-group-meta' );
                    if ( isset( $term_meta[ $term_id ] ) && !empty( $term_meta[ $term_id ] ) ){
                        $email_address = $term_meta[ $term_id ][ 'email_address' ];
                        echo esc_html( $email_address );
                    }
                    break;
            }
            return;
        }

        /**
         * Add jQuery for User taxonomy
         * @return type
         */
        function js_includes()
        {
            if ( ! $this->is_edit_user_taxonomy() ){
                return;
            }
            wp_enqueue_script( 'farbtastic', array( 'jquery' ) );
        }

        /**
         * Add CSS for User taxonomy
         * @return type
         */
        function css_includes()
        {
            if ( ! $this->is_edit_user_taxonomy() ){
                return;
            }
            wp_enqueue_style( 'farbtastic', array( 'jquery' ) );
        }

        /**
         * Configure Colorpicker
         * @return type
         */
        function colorpicker()
        {

            if ( ! $this->is_edit_user_taxonomy() ){
                return;
            } ?>

            <script type="text/javascript">
                /* <![CDATA[ */
                var farbtastic;
                var default_color = '#333';
                var old_color = null;

                function pickColor(color) {
                        jQuery('#group-color').val(color).css('background', color);
                        farbtastic.setColor(color);
                        jQuery('#group-color').processColor((farbtastic.hsl[2] * 100), (farbtastic.hsl[1] * 100));
                }

                jQuery(document).ready(function () {

                    jQuery('#pickcolor,#group-color').click(function () {
                        jQuery('#color-picker').show();
                    });

                    jQuery('#defaultcolor').click(function () {
                        pickColor(default_color);
                        jQuery('#group-color').val(default_color).css('background', default_color)
                    });

                    jQuery('#group-color').keyup(function () {
                        var _hex = jQuery('#group-color').val();
                        var hex = _hex;
                        if (hex[0] != '#')
                            hex = '#' + hex;
                        hex = hex.replace(/[^#a-fA-F0-9]+/, '');
                        if (hex != _hex)
                            jQuery('#group-color').val(hex).css('background', hex);
                        if (hex.length == 4 || hex.length == 7)
                            pickColor(hex);
                    });

                    jQuery(document).mousedown(function () {
                        jQuery('#color-picker').each(function () {
                            var display = jQuery(this).css('display');
                            if (display == 'block')
                                jQuery(this).fadeOut(2);
                        });
                    });

                    farbtastic = jQuery.farbtastic('#color-picker', function (color) {
                        pickColor(color);
                    });
                    pickColor(jQuery('#group-color').val());
                });

                jQuery.fn.processColor = function (black, sat) {
                    if (sat > 40) {
                        black = black - 10;
                    }

                    if (black <= 50) {
                        jQuery(this).css('color', '#ffffff');
                    } else {
                        jQuery(this).css('color', 'black');
                    }
                };
                /* ]]> */
            </script><?php
        }

        /**
         * hide_slug
         */
        function hide_slug()
        {
            if ( $this->is_edit_user_taxonomy( 'all' ) ){ ?>
                <style type="text/css">
                        .form-wrap form span.description {
                                display: none !important;
                        }
                </style>

                <script type="text/javascript">
                    jQuery(document).ready(function ($) {
//						$('#menu-posts').removeClass('wp-menu-open wp-has-current-submenu').addClass('wp-not-current-submenu');
//						$('#menu-users').addClass('wp-has-current-submenu wp-menu-open menu-top menu-top-first').removeClass('wp-not-current-submenu');
//						$('#menu-users a.wp-has-submenu').addClass('wp-has-current-submenu wp-menu-open menu-top');
//						$('#menu-posts a.wp-has-submenu').removeClass('wp-has-current-submenu wp-menu-open menu-top');
                            $('#tag-slug').parent('div.form-field').hide();
                            $('.inline-edit-col input[name=slug]').parents('label').hide();
                    });
                </script><?php
            } elseif ( $this->is_edit_user_taxonomy( 'edit' ) ) { ?>
                <style type="text/css">
                        .form-table .form-field td span.description, .form-table .form-field {
                                display: none;
                        }
                </style>
                <script type="text/javascript">
                        jQuery(document).ready(function ($) {
//						$('#menu-posts').removeClass('wp-menu-open wp-has-current-submenu').addClass('wp-not-current-submenu');
//						$('#menu-users').addClass('wp-has-current-submenu wp-menu-open menu-top menu-top-first').removeClass('wp-not-current-submenu');
//						$('#menu-users a.wp-has-submenu').addClass('wp-has-current-submenu wp-menu-open menu-top');
//						$('#menu-posts a.wp-has-submenu').removeClass('wp-has-current-submenu wp-menu-open menu-top');
                                $('#edittag #slug').parents('tr.form-field').addClass('hide-if-js');
                                $('.form-table .form-field').not('.hide-if-js').css('display', 'table-row');
                        });
                </script> <?php
            }
        }

        /**
         * Add custom fields for user taxonomy 
         * @param type $taxonomy
         */
        function add_remove_user_texonomy_field( $taxonomy )
        {
                //remove_all_actions('after-user-group-table');
                //remove_all_actions('user-group_edit_form');
                //remove_all_actions('user-group_add_form_fields');

                // If you use Rich Text tags, go ahead!
                if ( function_exists( 'kws_rich_text_tags' ) ){
                    add_action( $this->user_texonomy_slug . '_edit_form_fields', 'kws_add_form' );
                    add_action( $this->user_texonomy_slug . '_add_form_fields', 'kws_add_form' );
                }
                add_action( $this->user_texonomy_slug . '_add_form_fields', array( &$this, 'user_group_taxonomy_add_new_meta_field' ), 10, 2 );
                add_action( $this->user_texonomy_slug . '_add_form_fields', array( &$this, 'add_form_color_field' ), 11, 2 );
                add_action( $this->user_texonomy_slug . '_edit_form_fields', array( &$this, 'user_group_taxonomy_edit_meta_field' ), 10, 2 );
                add_action( $this->user_texonomy_slug . '_edit_form', array( &$this, 'add_form_color_field' ), 11, 2 );
        }

        /**
         * Add User taxomony column on user list view
         * @param array $defaults
         * @return type
         */
        function add_taxonomy_columns_user( $defaults )
        {
            $defaults[ $this->user_texonomy_slug ] = __( $this->labels['name'], $this->user_texonomy_slug );
            return $defaults;
        }

        /**
         * Add UI for User taxomony column on user list view
         * @param type $value
         * @param type $column_name
         * @param type $user_id
         * @return boolean
         */
        function add_taxonomy_columns_user_ui( $value, $column_name, $user_id )
        {
            switch ( $column_name ) {
                case $this->user_texonomy_slug:
                    //return self::get_user_user_group_tags( $user_id );
                    $terms = $this->get_user_taxonomies( $user_id );
                    if ( empty( $terms ) ){
                            return false;
                    }
                    $in = array();
                    foreach ( $terms as $term ) {
                            $href  = empty( $page ) ? add_query_arg( array( $this->user_texonomy_slug => $term->slug ), admin_url( 'users.php' ) ) : add_query_arg( array( $this->user_texonomy_slug => $term->slug ), $page );
                            $color = self::get_taxonomy_meta( 'group-color', $term->term_id );
                            $in[ ] = sprintf( '%s%s%s', '<a style="text-decoration:none; color:white; cursor: pointer; border:0; padding:2px 3px; margin:0 .3em .2em 0; border-radius:3px; background-color:' . $color . '; color:' . self::get_text_color( $color ) . ';" href="' . $href . '" title="' . esc_attr( $term->description ) . '">', $term->name, '</a>' );
                    }
                    return implode( '', $in );
                    break;
            }
            return $value;
        }

        /**
         * Perform bulk action for User taxonomy on User.php
         * @return type
         */
        function bulk_edit_action()
        {
            if ( ! isset( $_REQUEST[ 'bulk_edit_' . $this->user_texonomy_slug . '_submit' ] ) || empty( $_POST[ $this->user_texonomy_slug ] ) ){
                    return;
            }
            check_admin_referer( 'bulk-edit-' . $this->user_texonomy_slug );

            // Get an array of users from the string
            parse_str( urldecode( $_POST[ 'users_' . $this->user_texonomy_slug ] ), $users );

            if ( empty( $users ) ){
                    return;
            }

            $action = $_POST[ 'groupaction_' . $this->user_texonomy_slug ];

            foreach ( $users[ 'users' ] as $user ) {
                    $update_groups = array();
                    $groups        = $this->get_user_taxonomies( $user );

                    foreach ( $groups as $group ) {
                            $update_groups[ $group->slug ] = $group->slug;
                    }

                    if ( $action === 'add' ){
                        if ( ! in_array( $_POST[ $this->user_texonomy_slug ], $update_groups ) ){
                            $this->set_user_taxonomy($user, $_POST[ $this->user_texonomy_slug ] );
                        }
                    } elseif ( $action === 'remove' ) {
                        $this->remove_user_taxonomy($user, $_POST[ $this->user_texonomy_slug ] );
                    }

            }
        }

        /**
         * UI for bulk action on user.php
         */
        function bulk_edit( $views )
        {
            if ( ! current_user_can( 'assign_terms' ) ){
                    return $views;
            }
            $terms = get_terms( $this->user_texonomy_slug, array( 'hide_empty' => false ) ); ?>
            <form method="post" id="bulk_edit_<?php echo $this->user_texonomy_slug ?>_form" class="alignright" style="clear:right; margin:0 10px;">
                        <fieldset>
                                <legend class="screen-reader-text"><?php printf( __( 'Update %s', 'user-group' ) , $this->labels['name']); ?></legend>
                                <div>
                                        <label for="groupactionadd_<?php echo $this->user_texonomy_slug ?>" style="margin-right:5px;">
                                            <input name="groupaction_<?php echo $this->user_texonomy_slug ?>" value="add" type="radio" id="groupactionadd_<?php echo $this->user_texonomy_slug ?>" checked="checked"/> <?php 
                                            _e( 'Add users to', 'user-group' ); ?>
                                        </label>
                                        <label for="groupactionremove_<?php echo $this->user_texonomy_slug ?>">
                                            <input name="groupaction_<?php echo $this->user_texonomy_slug ?>" value="remove" type="radio" id="groupactionremove_<?php echo $this->user_texonomy_slug ?>"/> <?php 
                                            _e( 'Remove users from', 'user-group' ); ?>
                                        </label>
                                </div>
                                <div>
                                    <input name="users_<?php echo $this->user_texonomy_slug ?>" value="" type="hidden" id="bulk_edit_<?php echo $this->user_texonomy_slug ?>_users"/>

                                    <label for="<?php echo $this->user_texonomy_slug ?>-select" class="screen-reader-text"><?php echo $this->labels['name'] ?></label>
                                    <select name="<?php echo $this->user_texonomy_slug ?>" id="<?php echo $this->user_texonomy_slug ?>-select" style="max-width: 300px;"> <?php
                                        $select = '<option value="">' . __( 'Select ' . $this->labels['name'] . '&hellip;', 'user-group' ) . '</option>';
                                        foreach ( $terms as $term ) {
                                                $select .= '<option value="' . $term->slug . '">' . $term->name . '</option>' . "\n";
                                        }
                                        echo $select; ?>
                                    </select>
                                    <?php wp_nonce_field( 'bulk-edit-' . $this->user_texonomy_slug ) ?>
                                </div>
                                <div class="clear" style="margin-top:.5em;">
                                        <?php submit_button( __( 'Update' ), 'small', 'bulk_edit_' . $this->user_texonomy_slug . '_submit', false ); ?>
                                </div>
                        </fieldset>
                </form>
                <script type="text/javascript">
                        jQuery(document).ready(function ($) {
                                $('#bulk_edit_<?php echo $this->user_texonomy_slug ?>_form').remove().insertAfter('ul.subsubsub');
                                $('#bulk_edit_<?php echo $this->user_texonomy_slug ?>_form').live('submit', function () {
                                        var users = $('.wp-list-table.users .check-column input:checked').serialize();
                                            $('#bulk_edit_<?php echo $this->user_texonomy_slug ?>_users').val(users);
                                    });
                        });
                </script>
                <?php
                return $views;
        }

        /**
         * UI of User taxonomy 
         * @param type $user
         * @return type
         */
        function edit_user_user_taxonomy_section( $user )
        {
            $tax = get_taxonomy( $this->user_texonomy_slug );

            /* Make sure the user can assign terms of the profession taxonomy before proceeding. */
            if ( ! current_user_can( $tax->cap->assign_terms ) || ! current_user_can( 'assign_terms' ) ) return;

            /* Get the terms of the 'profession' taxonomy. */
            $terms = get_terms( $this->user_texonomy_slug, array( 'hide_empty' => false ) ); ?>

            <h3 id="user-groups"><?php echo $this->labels['name']; ?></h3>
            <table class="form-table">
                <tr>
                    <th>
                        <label for="<?php echo $this->user_texonomy_slug; ?>" style="font-weight:bold; display:block;"><?php 
                            printf( __( 'Add a %s', 'user-groups' ), $this->labels['name'] ); ?></label>
                        <a href="<?php echo admin_url( 'edit-tags.php?taxonomy=' . $this->user_texonomy_slug  ); ?>"><?php _e( 'Add a User Group', 'user-groups' ); ?></a>
                    </th>

                    <td><?php

                        /* If there are any terms availabel, loop through them and display checkboxes. */
                        if ( ! empty( $terms ) ){
                            echo '<ul>';
                            foreach ( $terms as $term ) {
                                $color = self::get_taxonomy_meta( 'group-color', $term->term_id );
                                if ( ! empty( $color ) ){
                                        $color = ' style="padding:2px .5em; border-radius:3px; background-color:' . $color . '; color:' . self::get_text_color( $color ) . '"';
                                } ?>
                                <li><input type="checkbox" name="<?php echo $this->user_texonomy_slug; ?>[]"
                                                   id="<?php echo $this->user_texonomy_slug; ?>-<?php echo esc_attr( $term->slug ); ?>"
                                                   value="<?php echo esc_attr( $term->slug ); ?>" <?php checked( true, $this->is_user_has_taxonomy( $user->ID, $term->term_taxonomy_id ) ); ?> />
                                        <label
                                                for="<?php echo $this->user_texonomy_slug; ?>-<?php echo esc_attr( $term->slug ); ?>"<?php echo $color; ?>><?php echo esc_html( $term->name ); ?></label>
                                </li> <?php
                            }
                            echo '</ul>';
                        } /* If there are no user-group terms, display a message. */ else {
                            _e( 'There are no user groups defined. <a href="' . admin_url( 'edit-tags.php?taxonomy=user-group' ) . '">' . __( 'Add a User Group', 'user-groups' ) . '</a>' );
                        } ?>
                    </td>
                </tr>
            </table> <?php
        }

        /**
         * Save User taxonomy for User  
         * @global RT_User_Group_Relationships_Model $rtlib_user_group_model
         * @global type $pagenow
         * @param type $user_id
         * @param type $user_groups
         * @param type $bulk
         * @return boolean
         */
        function save_user_user_taxonomy( $user_id, $user_groups = array(), $bulk = false )
        {
            global $rtlib_user_group_model, $pagenow;

            $tax = get_taxonomy( $this->user_texonomy_slug );
            /* Make sure the current user can edit the user and assign terms before proceeding. */
            if ( ! current_user_can( 'edit_user', $user_id ) && current_user_can( $tax->cap->assign_terms ) ){
                    return false;
            }

            $user_groups = @$_POST[ $this->user_texonomy_slug ];
            

            $old_terms = $this->get_user_taxonomies( $user_id );

            $old_groups = array();

            foreach ($old_terms as $old_term) {
                $old_groups[] = $old_term->slug;
            }

            if ( !isset( $groups ) || empty( $groups ) ){
                $groups = array();
            }

            $diff = array_diff($old_groups, $groups);

            $this->remove_user_taxonomies($user_id, $diff);
            
            $this->set_user_taxonomies( $user_id, $groups );
            
        }

        /**
         * Update user query filter User list accoding to its taxonomy
         * @global type $pagenow
         * @global type $wpdb
         * @param type $Query
         * @return type
         */
        function user_query( $Query = '' )
        {
            global $pagenow, $wpdb;

            if ( $pagenow !== 'users.php' ){
                    return;
            }

            if ( ! empty( $_GET[ $this->user_texonomy_slug ] ) ){

                $groups = explode( ',', $_GET[ $this->user_texonomy_slug ] );
                $ids    = array();
                foreach ( $groups as $group ) {
                    $user_ids = $this->get_user_by_term_slug( $group );
                    if ( isset( $user_ids ) && !empty( $user_ids ) ){
                        $ids = array_merge( $user_ids, $ids );
                    }
                }
                $ids = implode( ',', wp_parse_id_list( $user_ids ) );

                if ( ! empty( $ids ) ) {
                    $Query->query_where .= " AND $wpdb->users.ID IN ( $ids )";
                }else{
                    $Query->query_where .= " AND 1=2";
                }
            }
        }

        /**
         * UI for filter User list accoding to its taxonomy
         * @global type $wp_roles
         * @param array $views
         * @return type
         */
        function user_view_filters( $views )
        {
            global $wp_roles;

            $terms = get_terms( $this->user_texonomy_slug , array( 'hide_empty' => FALSE ) );
            $select  = '<select name="' . $this->user_texonomy_slug . '" id="' . $this->user_texonomy_slug . 
                    '-select"><option value="">All ' . $this->labels['name'] . '</option>' . "\n";
            $current = false;
            foreach ( $terms as $term ) {
                $user_ids = get_objects_in_term( $term->term_id, $this->user_texonomy_slug );
                if ( isset( $_GET[ $this->user_texonomy_slug ] ) && $_GET[ $this->user_texonomy_slug ] === $term->slug ){
                    $current = $term;
                }
                $select .= '<option value="' . $term->slug . '"' . selected( true, isset( $_GET[ $this->user_texonomy_slug ] ) && $_GET[ $this->user_texonomy_slug ] === $term->slug, false ) . '>' . $term->name . '</option>' . "\n";
            }

            $select .= '</select>';

            if ( $current ){
                $bgcolor   = self::get_taxonomy_meta( 'group-color', $current->term_id );
                $color     = self::get_text_color( $bgcolor );
                $roleli    = '';
                $role      = false;
                $role_name = __( 'users', 'user-group' );
                if ( isset( $_GET[ 'role' ] ) ){
                    $role  = $_GET[ 'role' ];
                    $roles = $wp_roles->get_names();
                    if ( array_key_exists( $role, $roles ) ){
                        $role_name = $roles[ "{$role}" ];
                        if ( substr( $role_name, - 1, 1 ) !== 's' ){
                            $role_name .= 's';
                        }
                    }
                    $roleli = sprintf( '%sRemove Role Filter%s', '<li><a href="' . remove_query_arg( 'role' ) . '">', '</li>' );
                }

                $colorblock = empty( $bgcolor ) ? '' : '<span style="width:1.18em; height:1.18em; float:left; margin-right:.25em; background-color:' . self::get_taxonomy_meta( 'group-color', $current->term_id ) . ';"></span>'; ?>
                <div id="user-group-header">
                    <h2><?php echo $colorblock;
                            echo esc_html( sprintf( __( 'User Group: %s', 'user-group' ), $current->name ) ); ?> <a
                                href="<?php echo admin_url( 'edit-tags.php?action=edit&taxonomy=' . $this->user_texonomy_slug . '&tag_ID=' . $current->term_id . '&post_type=post' ); ?>"
                                    class="add-new-h2"
                                    style="background:#fefefe;"><?php printf( __( 'Edit %s', 'user-group' ) , $this->labels['name']); ?></a></h2>
                    <?php echo wpautop( $current->description ) ; ?>
                </div>
                <p class="howto" style="font-style:normal;">
                    <span><?php echo esc_html( sprintf( __( 'Showing %s in %s', 'user-group' ), $role_name, '&ldquo;' . $current->name . '&rdquo;' ) ); ?>
                        .</span>

                    <a href="<?php echo esc_html( remove_query_arg( 'user-group' ) ); ?>"
                       class="user-group-user-group-filter"><span></span> <?php echo esc_html( sprintf( __( 'Show all %s', 'user-group' ), $role_name ) ); ?>
                    </a>

                    <?php if ( ! empty( $role ) ){ ?>
                            <a href="<?php echo esc_html( remove_query_arg( 'role' ) ); ?>"
                               class="user-group-user-group-filter"><span></span> <?php echo esc_html( sprintf( __( 'Show all users in "%s"', 'user-group' ), $current->name ) ); ?>
                            </a>
                    <?php } ?>
                </p>
                <div class="clear"></div> <?php
            }

            ob_start();

            $args = array();
            if ( isset( $_GET[ 's' ] ) ){
                $args[ 's' ] = $_GET[ 's' ];
            }
            if ( isset( $_GET[ 'role' ] ) ){
                $args[ 'role' ] = $_GET[ 'role' ];
            } ?>

            <label for="<?php echo $this->user_texonomy_slug ?>-select"><?php echo $this->labels['name']; ?></label>

            <form method="get"
                  action="<?php echo preg_replace( '/(.*?)\/users/ism', 'users', add_query_arg( $args, remove_query_arg( $this->user_texonomy_slug ) ) ); ?>"
                      style="display:inline;">
                    <?php echo $select; ?>
            </form>
            <style type="text/css">
                    .subsubsub li.<?php echo $this->user_texonomy_slug ?> {
                            display: inline-block !important;
                    }
            </style>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    <?php if ( isset( $_GET[ $this->user_texonomy_slug ] ) ) { ?>
                        $('ul.subsubsub li a').each(function () {
                                var $that = $(this);
                                $(this).attr('href', function () {
                                        var sep = $that.attr('href').match(/\?/i) ? '&' : '?';
                                        return $( this ).attr( 'href' ) + sep + '<?php echo $this->user_texonomy_slug ?>=<?php echo esc_attr( $_GET[ $this->user_texonomy_slug ] ); ?>';
                                });
                        }); <?php 
                    } ?>
                    $("#<?php echo $this->user_texonomy_slug ?>-select").change(function () {
                            var action = $(this).parents("form").attr('action');
                            if (action.match(/\?/i)) {
                                    action = action + '&<?php echo $this->user_texonomy_slug ?>=' + $(this).val();
                            } else {
                                    action = action + '?<?php echo $this->user_texonomy_slug ?>=' + $(this).val();
                            }

                            window.location = action;
                    });
                });
            </script> <?php

            $form = ob_get_clean();

            $views[ $this->user_texonomy_slug ] = $form;

            return $views;

        }

        /**
         * delete taxonomy while user deleted
         * @global RT_User_Group_Relationships_Model $rtlib_user_group_model
         * @param type $user_id
         */
        function delete_term_relationships( $user_id )
        {
            global $rtlib_user_group_model;

            wp_delete_object_term_relationships( $user_id, $this->user_texonomy_slug );
        }

        /**
         * Exclude taxonomy name form user name
         * @param string $username
         * @return string
         */
        function disable_username( $username )
        {
            if ( $this->user_texonomy_slug === $username ) $username = '';

            return $username;
        }

        /**
         * get_text_color
         * Code from http://serennu.com/colour/rgbtohsl.php
         * @param type $hexcode
         * @return string
         */
        static function get_text_color( $hexcode = '' )
        {
            $hexcode = str_replace( '#', '', $hexcode );

            $redhex   = substr( $hexcode, 0, 2 );
            $greenhex = substr( $hexcode, 2, 2 );
            $bluehex  = substr( $hexcode, 4, 2 );

            // $var_r, $var_g and $var_b are the three decimal fractions to be input to our RGB-to-HSL conversion routine
            $var_r = ( hexdec( $redhex ) ) / 255;
            $var_g = ( hexdec( $greenhex ) ) / 255;
            $var_b = ( hexdec( $bluehex ) ) / 255;

            $var_min = min( $var_r, $var_g, $var_b );
            $var_max = max( $var_r, $var_g, $var_b );
            $del_max = $var_max - $var_min;

            $l = ( $var_max + $var_min ) / 2;

            if ( $del_max == 0 ){
                    $h = 0;
                    $s = 0;
            } else {
                    if ( $l < 0.5 ){
                            $s = $del_max / ( $var_max + $var_min );
                    } else {
                            $s = $del_max / ( 2 - $var_max - $var_min );
                    };

                    $del_r = ( ( ( $var_max - $var_r ) / 6 ) + ( $del_max / 2 ) ) / $del_max;
                    $del_g = ( ( ( $var_max - $var_g ) / 6 ) + ( $del_max / 2 ) ) / $del_max;
                    $del_b = ( ( ( $var_max - $var_b ) / 6 ) + ( $del_max / 2 ) ) / $del_max;

                    if ( $var_r == $var_max ){
                            $h = $del_b - $del_g;
                    } elseif ( $var_g == $var_max ) {
                            $h = ( 1 / 3 ) + $del_r - $del_b;
                    } elseif ( $var_b == $var_max ) {
                            $h = ( 2 / 3 ) + $del_g - $del_r;
                    };

                    if ( $h < 0 ){
                            $h += 1;
                    };

                    if ( $h > 1 ){
                            $h -= 1;
                    };
            };

            if ( ( $l * 100 ) < 50 ){
                    return 'white';
            } else {
                    return 'black';
            }
        }

        /**
         * Add Color picker Field
         */
        function add_colorpicker_field()
        {
            ?> <tr>
                    <th scope="row" valign="top"><label><?php _e( 'Color for the User Group', 'genesis' ); ?></label></th>
                    <td id="group-color-row">
                            <p>
                                    <input type="text" name="user-group[group-color]" id="group-color"
                                               value="<?php echo esc_html( self::get_taxonomy_meta( 'group-color' ) ); ?>"/>
                                    <span
                                            class="description hide-if-js">If you want to hide header text, add <strong>#blank</strong> as text color.</span>
                                    <input type="button" class="button hide-if-no-js" value="Select a Color" id="pickcolor"/>
                            </p>

                            <div id="color-picker"
                                     style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none;"></div>
                    </td>
            </tr> <?php
        }

        /**
         * add mail field 
         */
        function user_group_taxonomy_add_new_meta_field()
        {
            ?> <div class="form-field">
                <label for="term_meta[email_address]"><?php _e( 'Email Address', 'rtcamp' ); ?></label>
                <input type="text" name="user-group[email_address]" id="user-group[email_address]" value="">

                <p class="description"><?php _e( 'Enter a Email address for this field', 'rtcamp' ); ?></p>
            </div> <?php
        }

        /**
         * add mail field 
         * @param type $term
         */
        function user_group_taxonomy_edit_meta_field( $term )
        {
            $t_id      = $term->term_id;
            $term_meta = '';
            if ( is_multisite() ) $term_meta = get_site_option( 'user-group-meta' ); else
                    $term_meta = get_option( 'user-group-meta' );
            ?>
            <tr class="form-field">
                    <th scope="row" valign="top"><label
                                    for="term_meta[email_address]"><?php _e( 'Email Address', 'rtCamp' ); ?></label></th>
                    <td>
                            <input type="text" name="user-group[email_address]" id="user-group[email_address]"
                                       value="<?php echo esc_attr( $term_meta[ $t_id ][ 'email_address' ] ) ? esc_attr( $term_meta[ $t_id ][ 'email_address' ] ) : ''; ?>"/>

                            <p class="description"><?php _e( 'Enter a email address for this field', 'rtcamp' ); ?></p>
                    </td>
            </tr> <?php
        }

        /**
         * Add Color picker Field
         * @param type $tag
         * @param type $taxonomy
         */
        function add_form_color_field( $tag, $taxonomy = '' )
        {

            $tax = get_taxonomy( $taxonomy );

            if ( $this->is_edit_user_taxonomy( 'edit' ) ){ ?>

                <h3><?php _e( 'User Group Settings', 'user-group' ); ?></h3>

                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row" valign="top">
                                <label><?php _e( 'Color for the User Group', 'genesis' ); ?></label>
                            </th>
                            <td id="group-color-row">
                                <p>
                                    <input type="text" name="user-group[group-color]" id="group-color"
                                                value="<?php echo esc_html( self::get_taxonomy_meta( 'group-color' ) ); ?>"/>
                                    <input type="button" class="button hide-if-no-js" value="Select a Color"
                                                id="pickcolor"/>
                                </p>

                                <div id="color-picker"
                                         style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none;"></div>
                                <div class="clear"></div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            <?php } else { ?>
                <div class="form-field">
                        <p>
                                <input type="text" style="width:40%" name="user-group[group-color]" id="group-color"
                                           value="<?php echo esc_html( self::get_taxonomy_meta( 'group-color' ) ); ?>"/>
                                <input type="button" style="margin-left:.5em;width:auto!important;" class="button hide-if-no-js"
                                           value="Select a Color" id="pickcolor"/>
                        </p>
                </div>
                <div id="color-picker"
                         style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none;"></div> <?php
            }
        }

        /**
         * get meta for user taxonomy
         * @param type $key
         * @param type $term_id
         * @return boolean
         */
        static function get_taxonomy_meta( $key = '', $term_id = 0 )
        {

            if ( isset( $_GET[ 'tag_ID' ] ) ){
                    $term_id = $_GET[ 'tag_ID' ];
            }
            if ( empty( $term_id ) ){
                    return false;
            }

            $term_meta = (array)get_option( 'user-group-meta' );

            if ( ! isset( $term_meta[ $term_id ] ) ){
                    return false;
            }

            if ( ! empty( $key ) ){
                    return isset( $term_meta[ $term_id ][ $key ] ) ? $term_meta[ $term_id ][ $key ] : false;
            } else {
                    return $term_meta[ $term_id ];
            }

        }

        /**
         * is_edit_user_taxonomy
         * @global type $pagenow
         * @param type $page
         * @return boolean
         */
        function is_edit_user_taxonomy( $page = false )
        {
            global $pagenow;

            if ( ( ! $page || $page === 'edit' ) && $pagenow === 'edit-tags.php' && isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'edit' 
                    && isset( $_GET[ 'taxonomy' ] ) && $_GET[ 'taxonomy' ] === $this->user_texonomy_slug ){
                return true;
            }

            if ( ( ! $page || $page === 'all' ) && $pagenow === 'edit-tags.php' && isset( $_GET[ 'taxonomy' ] ) && $_GET[ 'taxonomy' ] === $this->user_texonomy_slug && 
                    ( ! isset( $_GET[ 'action' ] ) || $_GET[ 'action' ] !== 'edit' ) ){
                    return true;
            }

            return false;
        }

        /**
         * Assign taxonomies for given User
         * @param type $user_id
         * @param type $groups
         */
        function set_user_taxonomies( $user_id, $groups )
        {
            foreach ( $groups as $group_name ) {
                $this->set_user_taxonomy( $user_id, $group_name );
            }
        }

        /**
         * Assign given taxonomy for given User
         * @global RT_User_Group_Relationships_Model $rtlib_user_group_model
         * @param type $user_id
         * @param type $group_name
         */
        function set_user_taxonomy( $user_id, $group_name ){

            global $rtlib_user_group_model;

            $term = get_term_by('slug' , $group_name, $this->user_texonomy_slug);

            $data = array(
                    'user_id' => $user_id,
                    'term_taxonomy_id' => $term->term_taxonomy_id,
                    'term_order' => 0,
            );

            $rtlib_user_group_model->insert( $data );

        }

        /**
         * remove taxonomies for given User
         * @param type $user_id
         * @param type $groups
         */
        function remove_user_taxonomies( $user_id, $groups ){
            foreach ($groups as $group_name) {
                $this->remove_user_taxonomy( $user_id, $group_name );
            }
        }

        /**
         * remove given taxonomy for given User
         * @global RT_User_Group_Relationships_Model $rtlib_user_group_model
         * @param type $user_id
         * @param type $group_name
         */
        function remove_user_taxonomy( $user_id, $group_name){
            global $rtlib_user_group_model;
            $term = get_term_by( 'slug', esc_attr( $group_name ), $this->user_texonomy_slug );

            $where = array(
                'user_id' => $user_id,
                'term_taxonomy_id' => $term->term_taxonomy_id,
            );

            $rtlib_user_group_model->delete( $where );

        }

        /**
         * For checking given user has given taxonomy
         * @global RT_User_Group_Relationships_Model $rtlib_user_group_model
         * @param type $user_id
         * @param type $term_taxonomy_id
         * @return boolean
         */
        function is_user_has_taxonomy( $user_id, $term_taxonomy_id ){
            global $rtlib_user_group_model;

            $columns = array(
                'user_id' => $user_id,
                'term_taxonomy_id' => $term_taxonomy_id,
            );
            $all_users_group = $rtlib_user_group_model->get( $columns );

            if ( count($all_users_group) > 0 ) {
                return true;
            }else{
                return false;
            }

        }

        /**
         * Get list of taxonomy for given user
         * @global RT_User_Group_Relationships_Model $rtlib_user_group_model
         * @param type $user_id
         * @return type
         */
        function get_user_taxonomies( $user_id ){
            global $rtlib_user_group_model;

            $columns = array(
                'user_id' => $user_id,
            );

            $groups = $rtlib_user_group_model->get( $columns );
            $terms = array();
            if( isset( $groups ) && !empty( $groups ) ){
                foreach ( $groups as $group ) {
                    $temp_term = get_term_by( 'id', esc_attr( $group->term_taxonomy_id ), $this->user_texonomy_slug );
                    if( isset( $temp_term ) && !empty( $temp_term ) ){
                        $terms[] = $temp_term;
                    }
                }
            }
            return $terms;
        }

        /**
         * Get list of user for given taxonomy
         * @global RT_User_Group_Relationships_Model $rtlib_user_group_model
         * @param type $slug
         * @return type
         */
        function get_user_by_term_slug ( $slug ){
            
            global $rtlib_user_group_model;
            $user_ids = array();
            
            $term = get_term_by( 'slug', esc_attr( $slug ), $this->user_texonomy_slug );
            if ( isset( $term ) && !empty( $term ) ){
                $columns = array(
                    'term_taxonomy_id' => $term->term_taxonomy_id,
                );
                $users = $rtlib_user_group_model->get( $columns );
                foreach ( $users as $user ) {
                    $user_ids[] = $user->user_id;
                }
            }
           return $user_ids;
        }
    }

}
