<?php
/**
 * Plugin Name: Cheker
 * Description: A custom plugin for checking WordPress content quality.
 * Version: 1.0.0
 * Author: Stephanie Jeter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function cheker_add_meta_box() {
    add_meta_box(
        'cheker_meta_box',
        'Cheker',
        'cheker_render_meta_box',
        array( 'page', 'post' ),
        'side',
        'default'
    );
}

add_action( 'add_meta_boxes', 'cheker_add_meta_box' );

function cheker_render_meta_box( $post ) {
    if ( ! empty( $post->post_title ) ) {
        echo '<p>✓ Page has a title</p>';
    } else {
        echo '<p>⚠ Page is missing a title</p>';
    }
    
    if (! empty($post->post_content)){
        echo '<p>✓ Page has content</p>';
    }else{
        echo '<p>⚠ Page has no content</p>';
    }

    if ( has_post_thumbnail( $post->ID ) ) {
        echo '<p>✓ Featured image is set</p>';
    } else {
         echo '<p>⚠ Featured image is missing</p>';
    }

    if ( has_block( 'core/image', $post->post_content ) ) {
        echo '<p>✓ Post contains an image</p>';
    } else {
        echo '<p>⚠ Post does not contain an image</p>';
    }

    $blocks = parse_blocks( $post->post_content );

    $total_images = 0;
    $images_with_alt = 0;
    $images_without_alt = 0;

    foreach ( $blocks as $block ) {

        if ( isset( $block['blockName'] ) && $block['blockName'] === 'core/image' ) {

        $total_images++;


            $image_id = $block['attrs']['id'];

            $alt_text = get_post_meta( $image_id, '_wp_attachment_image_alt', true );

            if ( ! empty( $alt_text ) ) {
                $images_with_alt++;
            } else {
                $images_without_alt++;
            }
        }

    }
    echo '<p>Images in content: ' . esc_html( $total_images ) . '</p>';

    echo '<p>✓ ' . esc_html( $images_with_alt ) . ' images have alt text</p>';

    if ( $images_without_alt > 0 ) {
        echo '<p>⚠ ' . esc_html( $images_without_alt ) . ' images are missing alt text</p>';
    }

    $previous_heading_level = null;

    foreach ( $blocks as $block ) {

        if ( isset( $block['blockName'] ) && $block['blockName'] === 'core/heading' ) {

            $level = $block['attrs']['level'] ?? 2;

            echo '<p>Found an H' . esc_html( $level ) . ' heading</p>';
            if (
                 $previous_heading_level !== null &&
                 $level > $previous_heading_level + 1
                ) {
                    echo '<p>⚠ Heading hierarchy issue: H'
                    . esc_html( $previous_heading_level )
                    . ' jumps to H'
                    . esc_html( $level )
                    . '</p>';
                  }

                $previous_heading_level = $level;
         }
    }

}