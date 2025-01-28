<?php
/**
 * Template du bloc
 *
 * @param array $block Les attributs du bloc
 * @param string $content Le contenu du bloc
 * @param bool $is_preview True durant l'aperçu du bloc dans l'éditeur
 * @param int $post_id L'ID du post étant édité
 */

$wrapper_attributes = get_block_wrapper_attributes();
$fields = get_fields() ?: []; // évite les erreurs si les champs ne sont pas définis
extract($fields);
?>

<div <?php echo $wrapper_attributes; ?>>
    <?php
    // Récupération des paramètres du bloc
    $data_source = get_field('data_source');
    $repeater_field = get_field('acf_repeater_field');
 
    $selected_columns = get_field('selected_columns');
   /* ?>
    <pre><?php var_dump($selected_columns); ?></pre> 
    <?php
    */
    $table_data = UpDynamicTable::get_instance()->get_table_data($data_source,$fields);    

    ?>

    <div class="dynamic-table-block">

        <?php if (!empty($table_data) && !empty($selected_columns) ): ?>
            <table>
                <thead>
                    <tr>
                        <?php foreach ($selected_columns as $column): ?>
                            <th><?php echo esc_html($column['label']); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($table_data as $row): ?>
                        <tr>
                            <?php foreach ($selected_columns as $column): ?>
                                <td>
                                    <?php 
                                    if(!isset($column['type'])) {
                                        $column['type'] ="value";
                                    }
                                     switch($column['type']) {

                                         case 'meta':
                                            $filter ='up_dynamic_table_meta_' . trim($column['meta']);
                                            echo apply_filters($filter,$row[$column['meta']]);
                                             break;
                                         case 'taxonomy':
                                            $filter ='up_dynamic_table_meta_' . trim($column['taxonomy']);
                                            echo apply_filters($filter,$row[$column['taxonomy']]);
                                             break;
                                        case 'value':
                                            echo  "test";
                                            break;
                                        default:
                                            $filter ='up_dynamic_table_field_' . trim($column['type']);
                                            echo apply_filters($filter,$row[$column['type']]);
                                        break;
                                     }

                       
                                    ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Aucune donnée disponible</p>
        <?php endif; ?>
    </div>
</div>

