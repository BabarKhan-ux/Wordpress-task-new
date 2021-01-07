<?php

/*
Plugin Name: Custom Post Type PRODUCTS
Plugin URI: http://ioscydiageeks.com
Description: The test plugin to create a custom post type named "PRODUCTS". This post type will have product name, a background color and a featured image.
Version: 1.0
Author: Babar Ilyas
Author URI: http://ioscydiageeks.com
Text Domain: custom-post-type-products
*/


/* 1. HOOKS */

// register custom post type
add_action('init', 'create_custom_post_type');

// registering color picker
add_action( 'admin_enqueue_scripts', 'mw_enqueue_color_picker' );



/* 2. CUSTOM POST TYPES */

// Function for creating custom post type
function create_custom_post_type() {

	// Labels
	$labels = array(
        'name'                => _x( 'Products', 'Post Type General Name' ),
        'singular_name'       => _x( 'Product', 'Post Type Singular Name' ),
        'menu_name'           => __( 'Products' ),
        'parent_item_colon'   => __( 'Parent Product' ),
        'all_items'           => __( 'All Products' ),
        'view_item'           => __( 'View Product' ),
        'add_new_item'        => __( 'Add New Product' ),
        'add_new'             => __( 'Add Product' ),
        'edit_item'           => __( 'Edit Product' ),
        'update_item'         => __( 'Update Product' ),
        'search_items'        => __( 'Search Product' ),
        'not_found'           => __( 'Not Found' ),
        'not_found_in_trash'  => __( 'Not found in Trash' ),
	);
	
	$args = array(
        'label'               => __( 'products' ),
        'description'         => __( 'This is the test product custom post type' ),
        'labels'              => $labels,
		
		// Features this Custom Post Type supports in Post Editor
		'supports'            => array('thumbnail'),
		
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'rewrite'             => array('slug' => '/products'),
		'menu_position'       => 5,
		'menu_icon'			  => 'dashicons-products',
        'can_export'          => true,
        'has_archive'         => true,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'post',
        'show_in_rest' => true,
	);

	// Registering your Custom Post Type
    register_post_type( 'products', $args );
	
}






/* 3. METABOXES */
function add_products_metabox( $post ) {

    add_meta_box(
        'products-details',
        'Product Details',
        'products_metabox',
        'products',
        'normal',
        'default'
    );

}

add_action( 'add_meta_boxes_products', 'add_products_metabox' );

function products_metabox() {
    
    global $post;

    $post_id = $post->ID;

    wp_nonce_field( basename( __FILE__ ), 'products_nonce' );

    $title_meta = ( !empty( get_post_meta( $post_id, 'products_title', true ) ) ) ? get_post_meta( $post_id, 'products_title', true ) : '';
    $bgcolor_meta = ( !empty( get_post_meta( $post_id, 'background_color', true ) ) ) ? get_post_meta( $post_id, 'background_color', true ) : '';

    ?>

    <style>
        .products-field-row {
            display: flex;
            flex-flow: row nowrap;
            flex: 1 1;
        }
        .products-field-container {
            position: relative;
            flex: 1 1;
            margin-right: 1em;
        }
        .products-field-container label {
            font-weight: bold;
        }
        .products-field-container label span {
            color: red;
        }
        .products-field-container ul {

        }
    </style>

    <div class="products-field-row">
        <div class="products-field-container">
            <p>
                <label for="Title">Product Name <span>*</span></label><br>
                <input type="text" name="products_title" require="required" class="widefat" value="<?php echo $title_meta ?>">
            </p>
        </div>
        <div class="products-field-container">
            <p>
                <label for="Title">Background Color <span>*</span></label><br>
                <input name="background_color" type="text" value="<?php echo $bgcolor_meta ?>" class="my-color-field" data-default-color="#effeff" />
            </p>
        </div>
    </div>

    <?php
}


// Save the Product into the Database
function save_products_meta( $post_id, $post ) {

    // Verify nonce
    if ( !isset($_POST['products_nonce']) || !wp_verify_nonce( $_POST['products_nonce'], basename( __FILE__ ) ) ) {
        return $post_id;
    }

    // get the post type object
    $post_type = get_post_type_object( $post->post_type );

    // check if the current user has the permission to edit
    if ( !current_user_can( $post_type->cap->edit_post, $post_id ) ) {
        return $post_id;
    }

    // Get the posted data and sanitize it
    $title = ( isset( $_POST['products_title'] ) ) ? sanitize_text_field( $_POST['products_title'] ) : '';
    $background_color = ( isset( $_POST['background_color'] ) ) ? sanitize_text_field( $_POST['background_color'] ) : '';

    // Update post meta data
    update_post_meta( $post_id, 'products_title', $title );
    update_post_meta( $post_id, 'background_color', $background_color );

}

add_action( 'save_post', 'save_products_meta', 10, 2 );





/* 4. ENQUEUING COLOR PICKER FILES */
function mw_enqueue_color_picker( $hook_suffix ) {
    // first check that $hook_suffix is appropriate for your admin page
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'my-script-handle', plugins_url('my-script.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
}

add_action( 'admin_enqueue_scripts', 'mw_enqueue_color_picker' );






/* 5. CHANGING THE TITLE */
function edit_post_change_title() {

    global $post;

    if( $post->post_type == 'products' ) {

        add_filter( 'the_title', 'products_title', 10, 2 );

    }

}

add_action( 'admin_head-edit.php', 'edit_post_change_title' );

function products_title( $title, $post_id ) {

    $new_title = get_post_meta( $post_id, 'products_title', true );
    return $new_title;

}








/* CHANGING THE ADMIN COLUMNS */
function products_column_headers( $columns ) {

    // Creating the custom column header data
    $columns = array(
        'cb' => '<input type="checkbox" />',
        'title' => __('Product Name'),
        'Background Color' => __('Background Color')
    );

    // Returning the new columns
    return $columns;

}

add_filter( 'manage_edit-products_columns', 'products_column_headers' );

function products_column_data( $column, $post_id ) {

    // Setup our return text
    $output = '';

    switch( $column ) {
        case 'title':
            // Get the product Name
            $product_name = get_post_meta( $post_id, 'products_title', true );
            $output .= $product_name;
            break;
        case 'Background Color':
            // Get the product Background Color
            $bgcolor = get_post_meta( $post_id, 'background_color', true );
            $output .= $bgcolor;
            break;
    }

    // Echo the output
    echo $output;

}

add_filter( 'manage_products_posts_custom_column', 'products_column_data',1,2 );