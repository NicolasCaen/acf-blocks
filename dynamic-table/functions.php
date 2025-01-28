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



    /**
     * Retourne les données formatées pour le tableau en fonction de la source de données selectionnée
     * 
     * @param string $data_source La source de données selectionnée
     * @return array Les données formatées pour le tableau
     */
    public function get_table_data($data_source,$block_fields) {

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
                if(!empty($block_fields["query_id"])){
                    $query = new WP_Query(apply_filters("dynamic_table_query_".$block_fields["query_id"],$args));
                }else{
                    $query = new WP_Query($args);
                }

         
                $posts = $query->posts;
                
                $formatted_data = array();
                foreach ($posts as $post) {
                    // Récupérer toutes les métadonnées du post
                    $all_meta = get_post_meta($post->ID);
                    $row = array(
                        'ID' => $post->ID,
                        'title' => $post->post_title,
                        'excerpt' => wp_trim_words(get_the_excerpt($post), 20),
                        'date' => get_the_date('d/m/Y', $post),
                        'author' => get_the_author_meta('display_name', $post->post_author),
                        'thumbnail' => get_the_post_thumbnail($post->ID, 'thumbnail'),
                    );
                    foreach ($all_meta as $meta_key => $meta_values) {
                        // Prendre la première valeur si c'est un tableau
                        $meta_value = is_array($meta_values) ? $meta_values[0] : $meta_values;
                        $row[$meta_key] = $meta_value;
                    }
                    $selected_columns = get_field('selected_columns');

                  
              
                    foreach ($selected_columns as $column) {
                      
                        if ($column['type'] === 'meta') {
                           
                           
                                $value = get_post_meta($post->ID, $column['meta'], true);
                                $row[$column['meta']] = $value;

                        } else if ($column['type'] === 'taxonomy') {
                            $terms = get_the_terms($post->ID, $column['taxonomy']);
                            if ($terms && !is_wp_error($terms)) {
                                $row[$column['taxonomy']] = implode(', ', wp_list_pluck($terms, 'name'));
                            } else {
                                $row[$column['taxonomy']] = '';
                            }
                        }
                    }
                    
         
                    
                    $formatted_data[] = $row;
                }

                return $formatted_data;

           
            case 'acf_repeater':
                    // Récupérer le nom du champ répéteur
                    $repeater_field = get_field('acf_repeater_field');
                    
                    // Récupérer les colonnes sélectionnées
                    $selected_columns = get_field('selected_columns',$block);
                    
                    // Initialiser un tableau pour stocker les données formatées
                    $formatted_data = array();
                    
                    // Vérifier si le champ répéteur existe et n'est pas vide
                    if (!empty($repeater_field)) {
                        // Récupérer les lignes du champ répéteur
                        $fields = get_field($repeater_field, get_the_ID());
                        var_dump($fields);
                        var_dump($selected_columns);
                        // Vérifier si des lignes existent
                        if (!empty($fields)) {
                            // Parcourir chaque ligne du répéteur
                            foreach ($fields as $row) {
                                // Initialiser un tableau pour stocker les données de la ligne courante
                                $row_data = array();
                                
                                // Parcourir les colonnes sélectionnées
                                foreach ($selected_columns as $column) {
                                    // Vérifier si la colonne existe dans la ligne et n'est pas vide
                                    if (!empty($row[$column['meta']])) {
                                        // Récupérer la valeur de la colonne
                                        $value = $row[$column['meta']];
                                        
                                        // Appliquer un filtre spécifique à la colonne
                                        $value = apply_filters('up_dynamic_table_repeater_' . $column['meta'], $value, get_the_ID(), $field_index);
                                        
                                        // Ajouter la valeur au tableau de la ligne
                                        $row_data[$column['meta']] = $value;
                                    }
                                }
                                
                                // Ajouter les données de la ligne au tableau formaté
                                if (!empty($row_data)) {
                                    $formatted_data[] = $row_data;
                                }
                            }
                        }
                    }
                    
                    // Retourner les données formatées
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