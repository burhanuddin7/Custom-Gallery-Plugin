<?php
/*
Plugin Name: Custom BS Image Gallery Plugin
Description: Custom BS Image Gallery Plugin with option for image upload any images with custom class addition and backend for managing the images and showing on a page or post with the help of shortcode [image_gallery].
Plugin URI: https://burhanuddin7.github.io/portfolio/
Author URI: https://burhanuddin7.github.io/portfolio/
Author: Burhanuddin Madraswala
License: GNU
Version: 1.5
*/

defined( 'ABSPATH' ) or die();
require_once( ABSPATH . 'wp-admin/includes/file.php' ); 


/**
    * admin_menu hook implementation
*/
function custom_table_example_admin_menu()
{
    add_menu_page('Custom BS Gallery', 'Custom BS Gallery', 'administrator', 'custom_bs_gallery_settings', 'custom_bs_gallery_page'); 
}

add_action('admin_menu', 'custom_table_example_admin_menu');

/*Add custom css and Js */

function bs_custom_admin_styles() {
    wp_enqueue_style('bs-bootstrap', plugins_url('/css/bootstrap.min.css', __FILE__ ));
    wp_enqueue_style( 'custom-style', plugins_url( '/css/style.css', __FILE__ ), array(), '13022021', 'all' );
}
add_action('admin_enqueue_scripts', 'bs_custom_admin_styles');


function bs_custom_admin_scripts() {
    wp_enqueue_script( 'scripts', plugins_url( '/scripts/main.js', __FILE__ ), array('jquery'), '1.0.0', true );
	wp_enqueue_script( 'dropzone', plugins_url( '/scripts/dropzone.js', __FILE__ ), array( 'jquery' ), '13022021' );
    
	// wp_localize_script for custom.js and use the jquery code in custom.js. And it will work for functions
	wp_localize_script( 'scripts', 'action_url_ajax', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}
add_action( 'admin_enqueue_scripts', 'bs_custom_admin_scripts' );

/*Add a Custom file for Form */
function custom_bs_gallery_page(){
    include "includes/custom_bs_gallery_page.php";
}

global $bs_db_version;
$bs_db_version = '1.0'; 


function bs_install_db()
{
    global $wpdb;
    global $bs_db_version;

    $table_name = $wpdb->prefix . 'bs_image_gallery_table'; 


    $sql = "CREATE TABLE " . $table_name . " (
      id int(11) NOT NULL AUTO_INCREMENT,
      uploaddate VARCHAR(100) NOT NULL,
      userip VARCHAR(100) NOT NULL,
      classname VARCHAR(100) NOT NULL,
      packagephoto text NULL,
      PRIMARY KEY  (id)
    );";


    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    add_option('bs_db_version', $bs_db_version);

    $installed_ver = get_option('bs_db_version');
    if ($installed_ver != $bs_db_version) {
        $sql = "CREATE TABLE " . $table_name . " (
          id int(11) NOT NULL AUTO_INCREMENT,
          uploaddate VARCHAR(100) NOT NULL,
          userip VARCHAR(100) NOT NULL,
          classname VARCHAR(100) NOT NULL,
		  packagephoto text NULL,
          PRIMARY KEY  (id)
        );";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        update_option('bs_db_version', $bs_db_version);
    }
}

register_activation_hook(__FILE__, 'bs_install_db');


function bs_install_db_data()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'bs_image_gallery_table'; 

}

register_activation_hook(__FILE__, 'bs_install_db_data');


function bs_update_db_check()
{
    global $bs_db_version;
    if (get_site_option('bs_db_version') != $bs_db_version) {
        bs_install_db();
    }
}

add_action('plugins_loaded', 'bs_update_db_check');


/*Wp admin ajax call for file upload images */
add_action("wp_ajax_get_gallery_images", "get_gallery_images");
add_action("wp_ajax_nopriv_get_gallery_images", "get_gallery_images");

function get_gallery_images() {
	add_filter( 'upload_dir', 'bs_upload_dir' );
    $upload_dirc = wp_upload_dir();	
    global $wpdb;
	$tablename = $wpdb->prefix . 'bs_image_gallery_table';
	
	$uploadedfile = $_FILES['packagephoto_name'];
	$packagephoto_name = $_FILES["packagephoto_name"]["name"];
    $upload_overrides = array( 
        'test_form' => false, /* this was in your existing override array */
        'unique_filename_callback' => 'bs_rename_file' // Function for image rename
    );
    $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
    $output = '<table><thead><tr><th> </th><th>Date</th><th>User Ip Address</th><th>ID</th><th>Class Name</th><th class="sort-by-img-path">Image Path<span class="sort-asc"></span><span class="sort-desc"></span></th><th>Image Preview</th></tr></thead><tbody>';
	if ($movefile && !isset($movefile['error'])) {
    $data = array( 
    'uploaddate' => date("Y-m-d"),
    'userip' => $_SERVER['REMOTE_ADDR'],
    'classname' => $_POST['classname'],
    'packagephoto' => bs_rename_file('', $packagephoto_name, '')
    );

    // FOR database SQL injection security, set up the formats
    $formats = array( 
        '%s', // uploaddate should be an string
        '%s', // userip should be an string
        '%s', // classname should be an string
        '%s', // packagephoto should be a integer
    ); 
    // Actually attempt to insert the data
    $insert = $wpdb->insert($tablename, $data, $formats);
    // this will get the data from your table
    $retrieve_data = $wpdb->get_results( "SELECT * FROM $tablename" );
    foreach ($retrieve_data as $retrieved_data){ 
        $id= $retrieved_data->id;
        $upload_date = $retrieved_data->uploaddate;
        $user_ip = $retrieved_data->userip;
        $class_name = $retrieved_data->classname;
        $file_name = $retrieved_data->packagephoto;

        if($insert){
            $output .= '<tr>
            <td><input type="checkbox" name="imageGalleryCB" id="'. $id .'"></td>
            <td>'.$upload_date.'</td>
            <td>'.$user_ip.'</td>
            <td>'. $id .'</td>
            <td>'. $class_name .'</td>
            <td class="sort-by-img-path">'. $file_name .'</td>
            <td><img src="'.$upload_dirc['baseurl'].'/customImages/'.$file_name.'" class="img-thumbnail" width="175" height="175" style="height:175px;" /></td>
            </tr>';
        }
    }
	}else{
		$output .= '<tr><td colspan="7">No Results Found</td></tr>';
	}
    $output .= '</tbody></table>';
    echo $output;

	remove_filter( 'upload_dir', 'bs_upload_dir' );
    die();
}

function bs_rename_file($dir, $filename, $ext){
    $newfilename =  $filename;
    $newfilename= str_replace( " ", "-", $newfilename );
    return preg_replace('/[^A-Za-z0-9\.-]/', '', $newfilename);
}

function bs_upload_dir( $dirs ) { 
    $user = wp_get_current_user(); 
    $dirs['subdir'] = ''; 
    $dirs['path'] = $dirs['basedir'].'/customImages'.''; 
    $dirs['url'] = $dirs['baseurl'].'/customImages'.''; 
    return $dirs; 
}

/*Ajax call on load to get the uploaded images */
add_action("wp_ajax_load_gallery_images_db", "load_gallery_images_db");
add_action("wp_ajax_nopriv_load_gallery_images_db", "load_gallery_images_db");

function load_gallery_images_db(){
    $upload_dirc = wp_upload_dir();	
    global $wpdb;
	$tablename = $wpdb->prefix . 'bs_image_gallery_table';
    $output = '<table><thead><tr><th> </th><th>Date</th><th>User Ip Address</th><th>ID</th><th>Class Name</th><th class="sort-by-img-path">Image Path<span class="sort-asc"></span><span class="sort-desc"></span></th><th>Image Preview</th></tr></thead><tbody>';
    $retrieve_data = $wpdb->get_results( "SELECT * FROM $tablename" );
    foreach ($retrieve_data as $retrieved_data){ 
        $id= $retrieved_data->id;
        $upload_date = $retrieved_data->uploaddate;
        $user_ip = $retrieved_data->userip;
        $class_name = $retrieved_data->classname;
        $file_name = $retrieved_data->packagephoto;

        if($retrieved_data){
            $output .= '<tr>
            <td><input type="checkbox" name="imageGalleryCB" id="'. $id .'"></td>
            <td>'.$upload_date.'</td>
            <td>'.$user_ip.'</td>
            <td>'. $id .'</td>
            <td>'. $class_name .'</td>
            <td class="sort-by-img-path">'. $file_name .'</td>
            <td><img src="'.$upload_dirc['baseurl'].'/customImages/'.$file_name.'" class="img-thumbnail" width="175" height="175" style="height:175px;" /></td>
            </tr>';
        }else{
            $output .= '<tr><td colspan="7">No Results Found</td></tr>';
        }
    }
    $output .= '</tbody></table>';
    echo $output;
    die();
}

/*Ajax call on delete uploaded images */
add_action("wp_ajax_delete_gallery_images_db", "delete_gallery_images_db");
add_action("wp_ajax_nopriv_delete_gallery_images_db", "delete_gallery_images_db");

function delete_gallery_images_db(){
    $upload_dirc = wp_upload_dir();	
    global $wpdb;
	$tablename = $wpdb->prefix . 'bs_image_gallery_table';
    
    $post_ids = $_POST['post_id'];
    
    foreach($post_ids as $id){ 
        // Delete record 
        $wpdb->query($wpdb->prepare("DELETE FROM $tablename WHERE id = '".$id."'"));
    }
    echo 1;
    die();
}

/*Shortcode for Pages and Posts */
function bs_add_image_ajax_shortcode() {
    global $wpdb;
    $upload_dirc = wp_upload_dir();	
    $tablename = $wpdb->prefix . 'bs_image_gallery_table';
    $output = '<div class="row" style="text-align:center;">';
    $retrieve_data = $wpdb->get_results( "SELECT * FROM $tablename" );
    foreach ($retrieve_data as $retrieved_data){ 
      $class_name = $retrieved_data->classname;
      $file_name = $retrieved_data->packagephoto;
  
      if($retrieved_data){
          $output .= '<div style="display: inline-block;vertical-align: middle;margin: 20px;" class="uploaded-image '. $class_name .'">
          <img title="'.$file_name.'" src="'.$upload_dirc['baseurl'].'/customImages/'.$file_name.'" class="img-thumbnail" width="175" height="175" style="height:175px;" />
          </div>';
      }else{
          $output .= '<div>No Images uploaded in Backend of Plugin</div>';
      }
    }
    $output .= '</div>';
    echo $output;
}
  
add_shortcode( 'image_gallery', 'bs_add_image_ajax_shortcode');