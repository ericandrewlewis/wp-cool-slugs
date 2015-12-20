<?php
/*
Plugin Name: Cool Slugs
*/

/**
 * Assure post slug uniqueness, comparing the desired URL to existing rewrites.
 */
add_filter( 'wp_unique_post_slug', function( $slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug ) {
  global $wp_rewrite;
  $post = get_post( $post_ID );
  /*
   * The post may not exist yet, stub out a post object if so. This allows us to
   * use the native API get_permalink() to get a desired post URL.
   */
  if ( empty( $post ) ) {
    $post = new StdClass();
    $post->ID = $post_ID;
    $post->post_status = $post_status;
    $post->post_type = $post_type;
    $post->post_parent = $post_parent;
  }
  $post->post_status = 'publish';
  $post->post_name = $slug;
  if ( $post->post_type === 'page' ) {

  }
  $desired_permalink = get_permalink( $post );
  $parsed = parse_url( $desired_permalink );
  $desired_path = trim ( $parsed['path'], '/' );
  $rewrite = $wp_rewrite->wp_rewrite_rules();
  // Find a rewrite. Depends on #18877.
  $found_rewrite_match = $wp_rewrite->find_match( $desired_path, $desired_path, $rewrite );
  if ( $found_rewrite_match ) {
    $query_string = $found_rewrite_match[1];
    parse_str( $query_string, $query_args );
    // Don't check for uniqueness against self.
    $query_args['post__not_in'] = array( $post_ID );
    $query_args['post_status'] = 'any';
    // Only set `name` in case `pagename` was set in the rewrite match.
    if ( isset( $query_args['pagename'] ) ) {
      $query_args['name'] = $query_args['pagename'];
      unset( $query_args['pagename'] );
    }

    $suffix = 2;
    $alt_post_name = $slug;
    // Search for a good slug, adding an appended numeric string if necessary.
    while ( true ) {
      $query = new WP_Query( $query_args );
      $use_slug = true;
      // If there's a post at this URL already, bail.
      if ( $query->have_posts() ) {
        $use_slug = false;
      }
      // Avoid collision with `tag/tagname` even if there are no posts in the taxonomy.
      if ( $query->get_queried_object() ) {
        $use_slug = false;
      }
      if ( $post_type === 'attachment' && apply_filters( 'wp_unique_post_slug_is_bad_attachment_slug', false, $slug ) ) {
        $use_slug = false;
      }
      if ( $use_slug ) {
        break;
      } else {
        $alt_post_name = _truncate_post_slug( $slug, 200 - ( strlen( $suffix ) + 1 ) ) . "-$suffix";
        $query_args['name'] = $alt_post_name;
        $suffix++;
      }
    }
    $slug = $alt_post_name;
  }
  return $slug;
}, 10, 6 );
