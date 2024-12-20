<?php
/**
 * Template du bloc
 *
 * @param array $block Les attributs du bloc
 * @param string $content Le contenu du bloc
 * @param bool $is_preview True durant l'aperçu du bloc dans l'éditeur
 * @param int $post_id L'ID du post étant édité
 */

// Récupère toutes les classes et attributs du bloc
$wrapper_attributes = get_block_wrapper_attributes();
$blockName = str_replace('/', '-', $block['name']); // Convertit 'ng1/slider-gsap' en 'ng1-slider-gsap'
$className = 'wp-block-' . $blockName; // Résultat : 'wp-block-ng1-slider-gsap'

$fields = get_fields( $post_id) ?: []; // évite les erreurs si les champs ne sont pas définis
// Option 1 : get_post_meta()
$all_meta = get_post_meta($post_id, '', true);

//version 1 : utilisation de get_attached_media
$attached_images = get_attached_media('image', $post_id);
$attached_images = wp_list_pluck($attached_images, 'ID');

//version 2 : utilisation de get_post_meta
// $attached_images = get_post_meta($post_id, 'attached_images', true);
// $attached_images = explode(',', $attached_images);
// Construire le nom de la classe à partir du nom du bloc

// Récupération du ratio depuis les champs ACF
$ratio = get_field('image_ratio') ?: '16:9'; // ratio par défaut
$ratio_class = 'ratio-' . str_replace(':', '-', $ratio);

?>

<div <?php echo $wrapper_attributes; ?>>
    <?php if (!empty($title)) : ?>
        <h2><?php echo esc_html($title); ?></h2>
    <?php endif; ?>
  
    <?php if (!empty($attached_images)) : ?>
        <div class="<?php echo $className; ?>__container <?php echo $ratio_class; ?>" 
             data-autoplay="true" 
             data-autoplay-speed="5000"
             data-ratio="<?php echo esc_attr($ratio); ?>">
            <div class="<?php echo $className; ?>__wrapper">
                <?php foreach ($attached_images as $image) : 
                    $full_image = wp_get_attachment_image_src($image, 'full');
                ?>
                    <div class="<?php echo $className; ?>__slide">
                        <div class="<?php echo $className; ?>__image-container">
                            <?php echo wp_get_attachment_image($image, 'large', false, [
                                'class' => $className.'__img',
                                'data-full-src' => $full_image[0],
                                'loading' => 'lazy'
                            ]); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="<?php echo $className; ?>__nav">
                <button class="<?php echo $className; ?>__prev">Précédent</button>
                <button class="<?php echo $className; ?>__next">Suivant</button>
            </div>
        </div>
    <?php endif; ?> 
</div>