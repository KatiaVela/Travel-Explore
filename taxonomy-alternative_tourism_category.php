<?php
/*
Template Name: Tourism Categories*/

namespace App;

use Timber\Timber;
use Timber\PostQuery;

$context = Timber::context();

if (is_tax('alternative_tourism_category')) {
    $context = Timber::context();

    $term = get_queried_object();
    $term_slug = $term->slug;

    $query_args = array(
        'post_type' => 'alternative_tourism',
        'tax_query' => array(
            array(
                'taxonomy' => 'alternative_tourism_category',
                'field'    => 'slug',
                'terms'    => $term_slug,
            ),
        ),
    );

    $wp_query = new \WP_Query($query_args);

    $context['posts'] = new PostQuery($wp_query);

    $category_data = array();

    if ($wp_query->have_posts()) {
        while ($wp_query->have_posts()) {
            $wp_query->the_post();

            $post_id = get_the_ID();

            $terms = wp_get_post_terms($post_id, 'alternative_tourism_category');

            $categories_data = array();
            foreach ($terms as $term) {
                $categories_data[] = array(
                    'name' => $term->name,
                    'slug' => $term->slug,
                    'icon' => get_field('icon', $term),
                    'image' => get_field('image', $term),
                );
            }
            $category_data[$post_id] = $categories_data;
        }

        wp_reset_postdata();
    }

    $keys = array_keys($category_data);
    $first_key = reset($keys);
    $context['current_category'] = $category_data[$first_key] ?? array();

    $context['title'] = single_term_title('', false);
    $context['term_description'] = term_description();

    $templates = ['templates/alt-tourism/taxonomy-alternative_tourism_category.twig'];

} else {
    $context['title'] = 'Tourism Categories';

    $terms = get_terms([
        'taxonomy' => 'alternative_tourism_category',
        'hide_empty' => false,
    ]);

    if (is_wp_error($terms)) {
        echo 'Error retrieving terms: ' . $terms->get_error_message();
        die();
    } else {
        $categories = [];

        foreach ($terms as $term) {
            $image_url = get_field('image', $term);

            $categories[] = [
                'term' => [
                    'name' => $term->name,
                    'description' => $term->description,
                    'link' => get_term_link($term),
                    'image' => $image_url,
                ]
            ];
        }

        $context['categories'] = $categories;
    }

    $templates = ['templates/alt-tourism/page-tourism-categories.twig'];
}

Timber::render($templates, $context);