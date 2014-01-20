<?php

if ( !function_exists( 'shoestrap_compiler' ) ) :
/*
 * This function can be used to compile a less file to css using the lessphp compiler
 */
function shoestrap_compiler() {
  $minimize_css = shoestrap_getVariable( 'minimize_css', true );
  $options = ( $minimize_css == 1 ) ? array( 'compress'=>true ) : array( 'compress'=>false );

  $bootstrap_location = get_template_directory() . '/assets/less/';
  $webfont_location   = get_template_directory() . '/assets/fonts/';
  $bootstrap_uri      = '';
  $custom_less_file   = get_template_directory() . '/assets/less/custom.less';

  try {

    $parser = new Less_Parser( $options );

    // The main app.less file
    $parser->parseFile( $bootstrap_location . 'app.less', $bootstrap_uri );
    // Our custom variables
    $parser->parse( shoestrap_variables() );

    // Include the Elusive Icons
    $parser->parseFile( $webfont_location . 'elusive-webfont.less', $bootstrap_uri );

    // Enable gradients
    if ( shoestrap_getVariable( 'gradients_toggle' ) == 1 )
      $parser->parseFile( $bootstrap_location . 'gradients.less', $bootstrap_uri );

    // The custom.less file
    if ( is_writable( $custom_less_file ) )
      $parser->parseFile( $bootstrap_location . 'custom.less', $bootstrap_uri );

    // Parse any custom less added by the user
    $parser->parse( shoestrap_getVariable( 'user_less' ) );
    // Add a filter to the compiler
    $parser->parse( apply_filters( 'shoestrap_compiler', '' ) );

    $css = $parser->getCss();

  } catch( Exception $e ) {
    $error_message = $e->getMessage();
  }

  // Below is just an ugly hack
  $css = str_replace( 'bootstrap/fonts/', '', $css );
  $css = str_replace( get_template_directory_uri() . '/assets/', '../', $css );
  return apply_filters( 'shoestrap_compiler_output', $css );
}
endif;


if ( !function_exists( 'shoestrap_makecss' ) ) :
function shoestrap_makecss() {
  global $wp_filesystem;
  $file = shoestrap_css();
  
  // Initialize the Wordpress filesystem.
  if ( empty( $wp_filesystem ) ) {
    require_once( ABSPATH . '/wp-admin/includes/file.php' );
    WP_Filesystem();
  }

  $content = '/********* Do not edit this file *********/

';

  $content .= shoestrap_compiler();

  if ( is_writeable( $file ) || ( !file_exists( $file ) && is_writeable( dirname( $file ) ) ) ) {
    if ( !$wp_filesystem->put_contents( $file, $content, FS_CHMOD_FILE ) )
      return apply_filters( 'shoestrap_css_output', $content );
  }
}
endif;