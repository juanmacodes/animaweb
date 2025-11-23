<?php
if (!defined('ABSPATH'))
    exit;

class Anima_SEO_Manager
{

    public function __construct()
    {
        add_action('wp_head', array($this, 'output_meta_tags'), 1);
        add_filter('document_title_parts', array($this, 'custom_document_title'));
    }

    public function custom_document_title($title)
    {
        if (is_front_page()) {
            $title['tagline'] = get_bloginfo('description');
        }
        return $title;
    }

    public function output_meta_tags()
    {
        global $post;

        $title = get_bloginfo('name');
        $description = get_bloginfo('description');
        $url = home_url();
        $image = get_site_icon_url(512);

        if (is_singular() && isset($post)) {
            $title = get_the_title() . ' | ' . get_bloginfo('name');
            if (has_excerpt($post->ID)) {
                $description = strip_tags(get_the_excerpt($post->ID));
            } else {
                $description = wp_trim_words(strip_tags($post->post_content), 25);
            }
            $url = get_permalink($post->ID);
            if (has_post_thumbnail($post->ID)) {
                $image = get_the_post_thumbnail_url($post->ID, 'large');
            }
        } elseif (is_archive()) {
            $title = get_the_archive_title() . ' | ' . get_bloginfo('name');
        }

        // Standard SEO
        echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
        echo '<link rel="canonical" href="' . esc_url($url) . '" />' . "\n";

        // Open Graph (Facebook/LinkedIn)
        echo '<meta property="og:locale" content="' . get_locale() . '" />' . "\n";
        echo '<meta property="og:type" content="' . (is_singular() ? 'article' : 'website') . '" />' . "\n";
        echo '<meta property="og:title" content="' . esc_attr($title) . '" />' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($description) . '" />' . "\n";
        echo '<meta property="og:url" content="' . esc_url($url) . '" />' . "\n";
        echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '" />' . "\n";
        if ($image) {
            echo '<meta property="og:image" content="' . esc_url($image) . '" />' . "\n";
        }

        // Twitter Cards
        echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr($title) . '" />' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr($description) . '" />' . "\n";
        if ($image) {
            echo '<meta name="twitter:image" content="' . esc_url($image) . '" />' . "\n";
        }
    }
}

new Anima_SEO_Manager();
