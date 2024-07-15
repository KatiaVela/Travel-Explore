<?php

namespace App;
use WP_Query;
use Timber\Timber;

class Setup {

    public function theme_supports() {
        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');       
        add_image_size('small', 600, 600, false);
        add_theme_support('html5', [
            'comment-form',
            'comment-list',
            'gallery',
        ]);
        add_theme_support('post-formats', [
            'aside',
            'image',
            'video',
            'quote',
            'link',
            'gallery',
            'audio',
        ]);
        add_theme_support('menus');
    }

    public function register_navigation_menus() {
        register_nav_menus([
            'top-menu' => __('Top Menu', 'theme'),
            'footer-menu' => __('Footer Menu', 'theme'),
            'footer-menu-product' => __('Footer Menu Product', 'theme'),
            'footer-menu-company' => __('Footer Menu Company', 'theme'),
            'footer-menu-legals' => __('Footer Menu Legals', 'theme'),
            'footer-menu-social-media' => __('Footer Menu Social Media', 'theme'),
        ]);
    }

    public function my_acf_json_save_point($path) {
        return get_stylesheet_directory() . '/acf-json';
    }

    public function my_acf_json_load_point($paths) {
        unset($paths[0]);
        $paths[] = get_stylesheet_directory() . '/acf-json';
        return $paths;
    }

    public function live_search_handler() {
        if (!isset($_GET['query'])) {
            wp_die('No query parameter');
        }

        $query = sanitize_text_field($_GET['query']);
        $args = [
            's' => $query,
            'posts_per_page' => 10,
            'post_type' => ['post', 'page', 'alternative_tourism'],
            'post_status' => 'publish',
        ];

        $search_query = new \WP_Query($args);

        if ($search_query->have_posts()) {
            echo '<ul>';
            while ($search_query->have_posts()) {
                $search_query->the_post();
                echo '<li class="search-result-item m-2 flex item-center p-2 gap-5 hover:bg-light-hover cursor-pointer" onclick="window.location.href=\'' .  get_permalink() . '\'">';
                if(get_the_post_thumbnail()){ echo '<div class="search-result-thumbnail w-1/12">' . get_the_post_thumbnail(null, 'full', ['class' => 'w-full h-8']) . '</div>';};
                echo '<div class="search-result-content flex items-center">';
                echo '<a href="' . get_permalink() . '" class="font-roboto font-medium">' . get_the_title() . '</a>';
                echo '</div>';
                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo 'No results found';
        }

        wp_reset_postdata();
        wp_die();
    }

    public function advanced_custom_search($where, $wp_query) {
        global $wpdb;

        if (empty($where)) {
            return $where;
        }

        $terms = $wp_query->query_vars['s'];
        $exploded = explode(' ', $terms);

        if ($exploded === FALSE || count($exploded) == 0) {
            $exploded = [$terms];
        }

        foreach ($exploded as $tag) {
            $where .= " AND (
                ( $wpdb->posts.post_title LIKE '%$tag%' )
                OR ( $wpdb->posts.post_content LIKE '%$tag%' )
            )";
        }

        return $where;
    }
    function remove_max_image_preview($output, $meta_key, $meta_value) {
        if ($meta_key === 'robots' && $meta_value === 'max-image-preview:large') {
            return '';
        }
        return $output;
    }
   
    public function acf_load_post_types_choices( $field ) {

        $field['choices'] = array();
    
        $post_types = get_post_types( array( 'public' => true ), 'objects' );
    
        foreach ( $post_types as $post_type ) {
            $field['choices'][ $post_type->name ] = $post_type->label;
        }
    
        return $field;
    }

    public static function get_all_posts_by_type($post_type) {
        $args = [
            'post_type' => $post_type,
            'posts_per_page' => -1,
        ];

        $query = new WP_Query($args);
        $posts = Timber::get_posts($query);

        foreach ($posts as $key => $post) {
            $posts[$key]->thumbnail = get_the_post_thumbnail_url($post->ID, 'full');
            $posts[$key]->permalink = get_permalink($post->ID);
            $posts[$key]->author_name = get_the_author_meta('display_name', $post->post_author);
            $posts[$key]->author_image = get_avatar_url(get_the_author_meta('ID', $post->post_author));
            $posts[$key]->ribbon = get_field('ribbon', $post->ID);
            $posts[$key]->author_ribbon = get_field('author_ribbon', 'user_' . $post->post_author);
            $posts[$key]->author_url = get_author_posts_url($post->post_author);
        }

        return $posts;
    }
    
    public static function get_post_type_from_flexible_content($flexible_content) {
        foreach ($flexible_content as $module) {
            if ($module['acf_fc_layout'] === 'cards_module') {
                return $module['cards_module']['post_type_selector'];
            }
        }
        return 'post'; 
    }

    function setup_404_template_redirect() {
        if (is_404()) {
            $context = Timber::context();
            $context['site_url'] = home_url('/');
            Timber::render('views/templates/errors/404.twig', $context);
            exit();
        }
    }

    public function add_to_context($context) {
        $context['alternative_tourism_categories'] = $this->get_alternative_tourism_categories();
        return $context;
    }

    private function get_alternative_tourism_categories() {
        $categories = Timber::get_terms([
            'taxonomy' => 'alternative_tourism_category',
            'hide_empty' => false,
        ]);
    
        return $categories;
    }

    
    public function get_breadcrumbs() {
        global $post;
        $breadcrumbs = [];
    
        if (!is_front_page()) {
            $home_url = home_url('/');
            $breadcrumbs[] = [
                'title' => 'Home',
                'url' => $home_url
            ];
        }

        if (is_search()) {
            $breadcrumbs[] = [
                'title' => 'Search Results',
                'url' => ''
            ];
        } elseif (is_singular()) {
            $post_type = get_post_type_object(get_post_type());
            if ($post_type && !in_array($post_type->name, ['post', 'page'])) {
                $breadcrumbs[] = [
                    'title' => $post_type->labels->name,
                    'url' => get_post_type_archive_link($post_type->name)
                ];
            }
    
            $breadcrumbs[] = [
                'title' => get_the_title(),
                'url' => get_permalink()
            ];
        } elseif (is_tax()) {
            if (is_tax('alternative_tourism_category')) {
                $term = get_queried_object();
                $tourism_page_url = '';
        
                $tourism_page = get_page_by_path('tourism-category');
                if ($tourism_page) {
                    $tourism_page_url = get_permalink($tourism_page);
                }
        
                $breadcrumbs[] = [
                    'title' => 'Tourism Categories',
                    'url' => $tourism_page_url
                ];
        
                $breadcrumbs[] = [
                    'title' => $term->name,
                    'url' => get_term_link($term->term_id)
                ];
            }  else{
            $term = get_queried_object();
            if ($term) {
                $taxonomy = get_taxonomy($term->taxonomy);
                if ($taxonomy) {
                    $breadcrumbs[] = [
                        'title' => $taxonomy->labels->name,
                        'url' => get_term_link($term->term_id)
                    ];
                }
    
                $breadcrumbs[] = [
                    'title' => $term->name,
                    'url' => get_term_link($term->term_id)
                ];
            }}
        } elseif (is_page()) {
            $breadcrumbs[] = [
                'title' => get_the_title(),
                'url' => get_permalink()
            ];
        } elseif (is_home()) {
            $breadcrumbs[] = [
                'title' => single_post_title('', false),
                'url' => get_permalink()
            ];
        } elseif (is_archive()) {
            if (is_post_type_archive()) {
                $post_type = get_queried_object();
                $breadcrumbs[] = [
                    'title' => post_type_archive_title('', false),
                    'url' => get_post_type_archive_link($post_type->name)
                ];
            } elseif (is_category() || is_tag() || is_tax()) {
                $term = get_queried_object();
                $breadcrumbs[] = [
                    'title' => single_term_title('', false),
                    'url' => get_term_link($term)
                ];
            }
        }
    
        return $breadcrumbs;
    }

    public static function get_latest_posts($selected_post_type) {
        $post_types = get_post_types(['public' => true], 'names');
        $latest_posts = [];
    
        if (in_array($selected_post_type, $post_types)) {
            $args = [
                'post_type' => $selected_post_type,
                'posts_per_page' => 6,
                'post_status' => 'publish',
            ];
    
            $query = new \WP_Query($args);
            $posts = $query->posts;
    
            foreach ($posts as $post) {
                $post->post_thumbnail_url = get_the_post_thumbnail_url($post->ID);
                
            }
    
            $latest_posts[$selected_post_type] = $posts;
        }
    
        return $latest_posts;
    }

    public function get_categories_and_posts() {
        $modules = get_field('flexible_content');
        $all_modules_categories_and_posts = [];
    
        if ($modules) {
            foreach ($modules as $module) {
                if ($module['acf_fc_layout'] === 'post_display_settings') {
                    switch ($module['display_mode']) {
                        case 'filter_by_category':
                            $categories_and_posts = [];
                            $category_items = $module['category_filter'] ?? [];
                            foreach ($category_items as $category_id) {
                                $category_name = get_term($category_id)->name;
                                $posts = get_posts([
                                    'post_type' => 'alternative_tourism', 
                                    'tax_query' => [
                                        [
                                            'taxonomy' => 'alternative_tourism_category',
                                            'field'    => 'term_id',
                                            'terms'    => $category_id,
                                        ],
                                    ],
                                ]);
    
                                $post_data = [];
                                foreach ($posts as $post) {
                                    $thumbnail_id = get_post_thumbnail_id($post->ID);
                                    $flexible_content = get_field('flexible_content', $post->ID);
                                    $post_data[] = [
                                        'post' => $post,
                                        'image' => get_the_post_thumbnail_url($post->ID, 'thumbnail'),
                                        'image_alt' => get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true),
                                        'image_title' => get_the_title($thumbnail_id),
                                        'flexible_content' => $flexible_content,
                                    ];
                                }
                                
                                $categories_and_posts[] = [
                                    'category' => $category_name,
                                    'posts' => $post_data,
                                ];
                            }
                            $all_modules_categories_and_posts[] = [
                                'module' => $module,
                                'categories_and_posts' => $categories_and_posts
                            ];
                            break;
                        default:
                            break;
                    }
                }
            }
        }
        
        return $all_modules_categories_and_posts;
    }
    
    public function exclude_pending_posts_from_frontend($query) {
        if (!is_admin() && $query->is_main_query()) {
            $query->set('post_status', 'publish');
        }
    }
    

    public function set_pending_status_for_user_posts($data, $postarr) {
        if (!current_user_can('administrator')) {
            $data['post_status'] = 'pending';
        }
        return $data;
    }

    public function notify_admin_of_pending_post($post_id, $post) {
        if ($post->post_status == 'pending' && !current_user_can('administrator')) {
            $admin_email = get_option('admin_email');
            $subject = 'New Pending Post Submission';
            $message = 'A new post titled "' . $post->post_title . '" has been submitted and is pending approval.';
            $html_message = '<html><body>';
            $html_message .= '<p>A new post titled "<strong>' . $post->post_title . '</strong>" has been submitted and is pending approval.</p>';
            $html_message .= '</body></html>';
            
            $headers = [
                'From: Notification <noreply@yoursite.com>',
                'Content-Type: text/html; charset=UTF-8'
            ];
    
            wp_mail($admin_email, $subject, $html_message, $headers);
        }
    }
    

    public function restrict_publish_to_admins($data, $postarr) {
        if ($data['post_status'] == 'publish' && !current_user_can('administrator')) {

            $data['post_status'] = 'pending';
        }
        return $data;
    }

    public function add_author_column($columns) {
        if (current_user_can('administrator')) {
            $columns['post_author'] = 'Author';
        }
        return $columns;
    }

    public function show_author_column($column_name, $post_id) {
        if ($column_name == 'post_author') {
            $post = get_post($post_id);
            $author_id = $post->post_author;
            $author_name = get_the_author_meta('display_name', $author_id);
            echo $author_name;
        }
    }

    public function add_custom_columns($columns) {
        if (current_user_can('administrator')) {
            $columns['author'] = __('Author');
        }
        return $columns;
    }

    public function custom_column_content($column_name, $post_id) {
        if ($column_name == 'author' && current_user_can('administrator')) {
            $author = get_the_author_meta('display_name', get_post_field('post_author', $post_id));
            echo esc_html($author);
        }
    }
    private function is_strong_password($password) {
        return preg_match('/[A-Z]/', $password) && 
               preg_match('/[0-9]/', $password) && 
               strlen($password) >= 8;
    }

    public function handle_user_registration($dummy_param) {
        $password = $_POST['password']; 

        if (!$this->is_strong_password($password)) {
            set_transient('user_journey_registration_message', ['type' => 'error', 'message' => 'Fjalëkalimi duhet të jetë të paktën 8 karaktere i gjatë dhe të përmbajë të paktën një shkronjë të madhe dhe një numër.'], 30);
            wp_redirect($_POST['_wp_http_referer']);
            exit;
        }

        if (!isset($_POST['user_journey_registration_nonce_field']) || !wp_verify_nonce($_POST['user_journey_registration_nonce_field'], 'user_journey_registration_nonce')) {
            set_transient('user_journey_registration_message', ['type' => 'error', 'message' => 'Verifikimi i nonce dështoi'], 30);
            wp_redirect($_POST['_wp_http_referer']);
            exit;
        }

    $username = isset($_POST['username']) ? sanitize_text_field($_POST['username']) : '';
    $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $phone_number = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
    $profile_image_id = isset($_POST['profile_image']) ? intval($_POST['profile_image']) : 0;
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $verify_password = isset($_POST['verify_password']) ? $_POST['verify_password'] : '';

    if (!is_email($email)) {
        set_transient('user_journey_registration_message', ['type' => 'error', 'message' => 'Invalid email address'], 30);
        wp_redirect($_POST['_wp_http_referer']);
        exit;
    }

    if (empty($username)) {
        set_transient('user_journey_registration_message', ['type' => 'error', 'message' => 'Username is required'], 30);
        wp_redirect($_POST['_wp_http_referer']);
        exit;
    }

    if (!validate_username($username)) {
        set_transient('user_journey_registration_message', ['type' => 'error', 'message' => 'Invalid username format'], 30);
        wp_redirect($_POST['_wp_http_referer']);
        exit;
    }

    if (email_exists($email)) {
        set_transient('user_journey_registration_message', ['type' => 'error', 'message' => 'Email address is already in use'], 30);
        wp_redirect($_POST['_wp_http_referer']);
        exit;
    }

    if (!$this->is_strong_password($password)) {
        set_transient('user_journey_registration_message', ['type' => 'error', 'message' => 'Password must be at least 8 characters long and contain at least one uppercase letter and one number.'], 30);
        wp_redirect($_POST['_wp_http_referer']);
        exit;
    }


    if ($password !== $verify_password) {
        set_transient('user_journey_registration_message', ['type' => 'error', 'message' => 'Passwords do not match'], 30);
        wp_redirect($_POST['_wp_http_referer']);
        exit;
    }

    $userdata = [
        'user_login' => $username,
        'user_pass' => $password,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'user_email' => $email,
        'meta_input' => [
            'phone_number' => $phone_number,
            'profile_image' => $profile_image_id,
        ],
    ];

    $user_id = wp_insert_user($userdata);

    if (is_wp_error($user_id)) {
        set_transient('user_journey_registration_message', ['type' => 'error', 'message' => $user_id->get_error_message()], 30);
        wp_redirect($_POST['_wp_http_referer']);
        exit;
    }

    $activation_code = wp_generate_password(20, false);
    update_user_meta($user_id, 'activation_code', $activation_code);

    $subject = 'Confirmation of registration on our site';
    $message = 'Please click on the following link to confirm your registration: ' . add_query_arg(['key' => $activation_code, 'user' => $user_id], home_url('/'));

    $html_message = '<html><body>';
    $html_message .= '<p>Please click on the following link to confirm your registration:</p>';
    $html_message .= '<p><a href="' . add_query_arg(['key' => $activation_code, 'user' => $user_id], home_url('/')) . '">Confirm registration</a></p>';

    $html_message .= '</body></html>';


    $headers = [
        'From: Notification <noreply@yoursite.com>',
        'Content-Type: text/html; charset=UTF-8'
    ];

    wp_mail($email, $subject, $html_message, $headers);

    set_transient('user_journey_registration_message', ['type' => 'success', 'message' => 'Registration successful! Check your email to confirm registration.'], 30);

    wp_redirect($_POST['_wp_http_referer']);
    exit;
}
public function redirect_after_registration_confirmation() {
    if (strpos($_SERVER['REQUEST_URI'], home_url('/')) !== false && isset($_GET['key']) && isset($_GET['user'])) {
        $key = sanitize_text_field($_GET['key']);
        $user_id = intval($_GET['user']);

        $stored_activation_code = get_user_meta($user_id, 'activation_code', true);

        if ($stored_activation_code && $key === $stored_activation_code) {
            update_user_meta($user_id, 'activation_code', ''); 
            wp_redirect(home_url('/profile')); 
            exit;
        }
    }
}
public function custom_registration_menu() {
    add_menu_page('User Registration', 'User Registration', 'manage_options', 'custom-registration', [$this, 'custom_registration_page'], 'dashicons-admin-users', 6);
}

public function custom_registration_page() {
    ?>
    <div class="wrap">
        <h1>User Registration</h1>
        <form id="user-registration-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
            <input type="hidden" name="action" value="user_journey_registration">
            <?php wp_nonce_field('user_journey_registration_nonce', 'user_journey_registration_nonce_field'); ?>
            <table class="form-table">
                <tr>
                    <th><label for="username">Username</label></th>
                    <td><input type="text" name="username" id="username" required></td>
                </tr>
                <tr>
                    <th><label for="first_name">First Name</label></th>
                    <td><input type="text" name="first_name" id="first_name" required></td>
                </tr>
                <tr>
                    <th><label for="last_name">Last Name</label></th>
                    <td><input type="text" name="last_name" id="last_name" required></td>
                </tr>
                <tr>
                    <th><label for="email">Email</label></th>
                    <td><input type="email" name="email" id="email" required></td>
                </tr>
                <tr>
                    <th><label for="phone">Phone</label></th>
                    <td><input type="text" name="phone" id="phone" required></td>
                </tr>
                <tr>
                    <th><label for="profile_image">Profile Image</label></th>
                    <td><input type="file" name="profile_image" id="profile_image"></td>
                </tr>
            </table>
            <button type="submit" name="submit" class="button button-primary">Register</button>
        </form>
    </div>
    <?php
}
    public function restrict_user_journey_posts_to_own($query) {
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }
    
        global $pagenow;
        $post_type = isset($_GET['post_type']) ? $_GET['post_type'] : '';
    
        if ($pagenow == 'edit.php' && $post_type == 'user_journey' && !current_user_can('administrator')) {
            $query->set('author', get_current_user_id());
        }
    }
    
}
