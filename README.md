# Commerce Wishlist Button

Ce module fournit un formateur de champ (`wishlist_button`) pour afficher un bouton permettant d'ajouter un produit à la liste de souhaits directement depuis n'importe quel champ de type `entity_reference` pointant sur des variations de produit.

## Installation
1. Téléchargez ou clonez le module dans `modules/custom/commerce_wishlist_button`.
2. Activez-le via l'interface ou Drush:
   ```bash
   drush en commerce_wishlist_button -y
   ```
3. Allez dans **Structure > Types de produits > [Votre type] > Gérer l'affichage** et sélectionnez `Wishlist Button` comme formateur pour le champ `Variations`.

## Améliorations possibles
- **Icones**: ajouter un paramètre pour afficher une icône FontAwesome ou SVG avant le texte du bouton.
- **Injection de dépendances**: refactorer la classe pour injecter les services (`current_user`, `url_generator`, etc.) via `ContainerFactoryPluginInterface` pour faciliter les tests.
- **Messages de confirmation**: ajouter un callback AJAX pour afficher un message sous le bouton après ajout.
- **Cache**: définir des tags et contexts de cache appropriés pour que le bouton réagisse aux changements de permissions et de configuration.
- **Styles**: fournir une librairie CSS dédiée avec des styles pour différents états (hover, disabled).

---
## Tests

Le module inclut deux suites de tests PHPUnit :

### Tests unitaires

Fichier : `tests/src/Unit/WishlistButtonFormatterTest.php`

```php
<?php

namespace Drupal\Tests\commerce_wishlist_button\Unit\Plugin\Field\FieldFormatter;

use Drupal\commerce_wishlist_button\Plugin\Field\FieldFormatter\WishlistButtonFormatter;
use Drupal\Tests\UnitTestCase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Session\AccountInterface;

class WishlistButtonFormatterTest extends UnitTestCase {
  /**
   * Teste les paramètres par défaut du formateur.
   */
  public function testDefaultSettings() {
    $formatter = new WishlistButtonFormatter([], '', []);
    $settings = $formatter->defaultSettings();
    $this->assertArrayHasKey('label', $settings);
    $this->assertEquals('Add to wishlist', $settings['label']);
  }
}
```

### Tests fonctionnels

Fichier : `tests/src/Functional/WishlistButtonFormatterFunctionalTest.php`

```php
<?php

namespace Drupal\Tests\commerce_wishlist_button\Functional;

use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\Tests\BrowserTestBase;

class WishlistButtonFormatterFunctionalTest extends BrowserTestBase {
  /**
   * Modules à charger pour le test.
   */
  protected static $modules = [
    'system',
    'user',
    'field',
    'entity_reference',
    'commerce',
    'commerce_product',
    'commerce_wishlist',
    'commerce_wishlist_button',
  ];

  /**
   * Configuration initiale pour les tests.
   */
  protected function setUp(): void {
    parent::setUp();
    // Définir le type de wishlist par défaut.
    $this->config('commerce_wishlist.settings')->set('default_type', 'default')->save();
    // Créer une variation et un produit.
    $variation = ProductVariation::create([
      'type' => 'default',
      'sku' => 'TEST1',
      'price' => ['amount' => '1000', 'currency_code' => 'USD'],
    ]);
    $variation->save();
    $product = Product::create([
      'type' => 'default',
      'title' => 'Test Product',
      'variations' => [$variation],
    ]);
    $product->save();
  }

  /**
   * Vérifie que le bouton s'affiche sur la page produit.
   */
  public function testButtonVisible() {
    $this->drupalGet($this->getUrl('entity.commerce_product.canonical', ['commerce_product' => 1]));
    $this->assertSession()->buttonExists('Add to wishlist');
  }
}
```