# Documentation du Slider GSAP

Ce document explique le fonctionnement des classes `GSAPSlider` et `Lightbox` utilisées pour créer un slider d'images avec lightbox.

## Structure HTML requise

Structure HTML nécessaire pour le slider :

```html
    <div class="wp-block-ng1-slider-gsap__container">
        <div class="wp-block-ng1-slider-gsap__wrapper">
            <div class="wp-block-ng1-slider-gsap__slide">
                <div class="wp-block-ng1-slider-gsap__image-container">
                    <img src="..." alt="..." class="wp-block-ng1-slider-gsap__img" data-full-src="...">
                </div>
            </div>
            <!-- Autres slides... -->
        </div>
        
        <div class="wp-block-ng1-slider-gsap__nav">
            <button class="wp-block-ng1-slider-gsap__prev">Précédent</button>
            <button class="wp-block-ng1-slider-gsap__next">Suivant</button>
        </div>
    </div>
```

## Classe GSAPSlider

### Initialisation

```javascript
const slider = new GSAPSlider(element, options);
```

### Options disponibles

```javascript
{
    autoplay: true,          // Activer/désactiver l'autoplay
    autoplaySpeed: 5000,     // Délai entre les slides en ms
    slideSpeed: 0.8,         // Durée de l'animation en secondes
    slideEase: 'power2.out'  // Type d'easing GSAP
}
```

### Fonctionnalités
- Navigation manuelle (boutons prev/next)
- Autoplay avec pause au survol
- Navigation tactile (swipe)
- Intégration avec lightbox
- Animation GSAP pour les transitions

### Méthodes principales
- `nextSlide()` : Passe au slide suivant
- `prevSlide()` : Passe au slide précédent
- `startAutoplay()` : Démarre l'autoplay
- `stopAutoplay()` : Arrête l'autoplay

## Classe Lightbox

### Initialisation

    const lightbox = new Lightbox(baseClass, slides);

### Fonctionnalités
- Affichage plein écran des images
- Navigation entre les images
- Fermeture par overlay ou bouton
- Support tactile (swipe)
- Transitions fluides
- Chargement progressif des images

### Méthodes principales
- `open(index)` : Ouvre la lightbox à l'index spécifié
- `close()` : Ferme la lightbox
- `next()` : Image suivante
- `prev()` : Image précédente
- `updateImage(index)` : Met à jour l'image affichée

## Utilisation des data attributes
Les attributs data suivants peuvent être utilisés sur le container :
```javascript
    data-autoplay="true|false"        <!-- Active/désactive l'autoplay -->
    data-autoplay-speed="5000"        <!-- Définit la vitesse de l'autoplay en ms -->
    data-ratio="16:9"                 <!-- Définit le ratio des images -->
```

## Dépendances

- GSAP (GreenSock Animation Platform)

## Exemple d'utilisation personnalisée

```javascript
    // Initialisation avec options personnalisées
    const monSlider = new GSAPSlider(element, {
        autoplay: false,
        slideSpeed: 1.2,
        slideEase: 'power3.inOut'
    });

    // Utilisation de la lightbox séparément
    const maLightbox = new Lightbox('ma-classe-base', mesSlides);
    maLightbox.open(0);
```

## Notes techniques

1. La classe utilise une structure BEM pour les noms de classes CSS
2. Les animations sont gérées par GSAP pour de meilleures performances
3. Le code inclut des vérifications de sécurité pour éviter les erreurs
4. Support tactile natif pour mobile
5. Gestion automatique des événements de nettoyage

## Bonnes pratiques

- Toujours fournir des images optimisées
- Utiliser les data attributes pour la configuration
- Respecter la structure HTML requise
- Vérifier que GSAP est chargé avant l'initialisation

