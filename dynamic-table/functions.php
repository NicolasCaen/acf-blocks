<?php
class UpDynamicTable {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // add_filter('acf/load_field', array($this, 'populate_custom_post_type_field'));
        // add_filter('acf/load_field', array($this, 'populate_cpt_columns_field'));
        // add_filter('acf/load_field', array($this, 'populate_taxonomies_field'));
        // add_filter('acf/load_field', array($this, 'populate_taxonomy_field'));
    }

    // public function populate_custom_post_type_field($field) {
    //     if ($field['name'] === 'selected_cpt') {
    //         $post_types = get_post_types(array('public' => true), 'objects');
            
    //         $field['choices'] = array();
            
    //         foreach ($post_types as $post_type) {
    //             $field['choices'][$post_type->name] = $post_type->label;
    //         }
    //     }
        
    //     return $field;
    // }

    // public function populate_cpt_columns_field($field) {
    //     if ($field['name'] === 'selected_cpt_columns') {
    //         $field['choices'] = array(
    //             'title' => 'Titre',
    //             'date' => 'Date',
    //             'author' => 'Auteur',
    //             'excerpt' => 'Extrait',
    //             'thumbnail' => 'Image mise en avant'
    //         );
            
    //         // Récupération du CPT sélectionné
    //         $selected_cpt = get_field('selected_cpt');
    //         if ($selected_cpt) {
    //             $acf_fields = get_fields($selected_cpt);
    //             if ($acf_fields) {
    //                 foreach ($acf_fields as $key => $value) {
    //                     $field['choices']['acf_' . $key] = $key;
    //                 }
    //             }
                
    //             global $wpdb;
    //             $meta_keys = $wpdb->get_col($wpdb->prepare("
    //                 SELECT DISTINCT meta_key 
    //                 FROM $wpdb->postmeta pm 
    //                 JOIN $wpdb->posts p ON p.ID = pm.post_id 
    //                 WHERE p.post_type = %s 
    //                 AND meta_key NOT LIKE '\_%'
    //             ", $selected_cpt));
                
    //             if ($meta_keys) {
    //                 foreach ($meta_keys as $meta_key) {
    //                     $field['choices']['meta_' . $meta_key] = $meta_key;
    //                 }
    //             }
    //         }
    //     }
        
    //     return $field;
    // }

    // public function populate_taxonomies_field($field) {
    //     if ($field['name'] === 'selected_taxonomies') {
    //         $taxonomies = get_taxonomies(array('public' => true), 'objects');
            
    //         $field['choices'] = array();
            
    //         foreach ($taxonomies as $taxonomy) {
    //             $field['choices'][$taxonomy->name] = $taxonomy->label;
    //         }
    //     }
        
    //     return $field;
    // }

    // public function populate_taxonomy_field($field) {
    //     if ($field['name'] === 'taxonomy') {
    //         $taxonomies = get_taxonomies(array('public' => true), 'objects');
            
    //         $field['type'] = 'select';
    //         $field['choices'] = array();
    //         $field['multiple'] = 0;
    //         $field['allow_null'] = 0;
    //         $field['ui'] = 1;
    //         $field['ajax'] = 0;
    //         $field['return_format'] = 'value';
            
    //         foreach ($taxonomies as $taxonomy) {
    //             $field['choices'][$taxonomy->name] = $taxonomy->label;
    //         }
    //     }
        
    //     return $field;
    // }

    public function get_table_data($data_source) {

        switch($data_source) {
            case 'query':
                $query_string = get_field('query');
                if (empty($query_string)) {
                    $query_string ='{
                        "post_type": "page",
                        "posts_per_page": 1,
                        "orderby": "date",
                        "order": "DESC"
                    }';
                }
                $args = json_decode($query_string, true);
                if(!empty(get_field('selected_cpt'))) {
                    $args['post_type'] = get_field('selected_cpt');
                }
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log('Erreur de parsing JSON: ' . json_last_error_msg());
                    return array();
                }

                $query = new WP_Query($args);
                $posts = $query->posts;
                
                $formatted_data = array();
                foreach ($posts as $post) {
                    $row = array(
                        'ID' => $post->ID,
                        'title' => $post->post_title,
                        'excerpt' => wp_trim_words(get_the_excerpt($post), 20),
                        'date' => get_the_date('d/m/Y', $post),
                        'author' => get_the_author_meta('display_name', $post->post_author),
                        'thumbnail' => get_the_post_thumbnail($post->ID, 'thumbnail')
                    );

                    $selected_columns = get_field('selected_columns');
                    $fields = get_fields($post->ID);
                  
                    foreach ($selected_columns as $column) {
                      
                        if ($column['type'] === 'meta') {
                            if(isset($fields[$column['meta']])) {
                                $value = $fields[$column['meta']];
                                $row[$column['meta']] = $value;
                            } else {
                                $value = get_post_meta($post->ID, $column['meta'], true);
                                $row[$column['meta']] = $value;
                            }
                        } else if ($column['type'] === 'taxonomy') {
                            $terms = get_the_terms($post->ID, $column['taxonomy']);
                            if ($terms && !is_wp_error($terms)) {
                                $row[$column['taxonomy']] = implode(', ', wp_list_pluck($terms, 'name'));
                            } else {
                                $row[$column['taxonomy']] = '';
                            }
                        }
                    }
                    
                    // Application des filtres sur chaque champ
                    foreach ($row as $key => $value) {
                        if (in_array($key, ['ID', 'title', 'excerpt', 'date', 'author', 'thumbnail'])) {
                            $row[$key] = apply_filters('up_dynamic_table_field_' . $key, $value, $post->ID);
                        } elseif (isset($column['type']) && $column['type'] === 'meta') {
                            $row[$key] = apply_filters('up_dynamic_table_meta_' . $key, $value, $post->ID);
                        } elseif (isset($column['type']) && $column['type'] === 'taxonomy') {
                            $terms = get_the_terms($post->ID, $key);
                            $row[$key] = apply_filters('up_dynamic_table_taxonomy_' . $key, $value, $terms, $post->ID);
                        }
                    }
                    
                    $formatted_data[] = $row;
                }

                return $formatted_data;

           
            case 'acf_repeater':
                $repeater_field = get_field('acf_repeater_field');
                $selected_columns = get_field('selected_columns');
                $formatted_data = array();
                
                if (!empty($repeater_field)) {
                    $fields = get_field($repeater_field, get_the_ID());
                    
                    if (!empty($fields)) {
                        foreach ($fields as $field_index => $field) {
                            $row = array();
                            
                            foreach ($selected_columns as $column) {
                                if(!empty($field[$column['meta']])) {
                                    $value = $field[$column['meta']];
                                    $row[$column['meta']] = $value;
                                }
                            }
                            
                            // Application des filtres sur chaque champ
                            foreach ($row as $key => $value) {
                                $row[$key] = apply_filters('up_dynamic_table_repeater_' . $key, $value, get_the_ID(), $field_index);
                            }
                            
                            $formatted_data[] = $row;
                        }
                    }
                }
                
                return $formatted_data;
                
            case 'csv':
                $csv_file = get_field('csv_file');
                if ($csv_file) {
                    // Logique de parsing du fichier CSV
                    // Utiliser wp_remote_get() ou file_get_contents()
                }
                return array();
                    
        }
    }
}

// Initialisation
add_action('init', function() {
    UpDynamicTable::get_instance();
});