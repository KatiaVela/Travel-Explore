<?php

namespace App;

use Timber\Timber;
use Timber\PostQuery;

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

$templates = array('templates/taxonomy-alternative_tourism_category.twig');

$context['title'] = single_term_title('', false);
$context['term_description'] = term_description();

Timber::render($templates, $context);