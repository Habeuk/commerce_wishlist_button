<?php

namespace Drupal\commerce_wishlist_button\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\commerce_wishlist\WishlistProviderInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 *
 * @FieldFormatter(
 *   id = "commerce_wishlist_button",
 *   label = @Translation("Commerce wishlist Button"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class WishlistButtonFormatter extends FormatterBase {
  /**
   *
   * @var \Drupal\commerce_wishlist\WishlistProvider
   */
  protected $wishlistProvider;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, WishlistProviderInterface $wishlist_provider, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->wishlistProvider = $wishlist_provider;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($plugin_id, $plugin_definition, $configuration['field_definition'], $configuration['settings'], $configuration['label'], $configuration['view_mode'], $configuration['third_party_settings'], $container->get(
      'commerce_wishlist.wishlist_provider'), $container->get('entity_type.manager'));
  }

  /**
   *
   * {@inheritdoc}
   * @see \Drupal\Core\Field\FormatterInterface::viewElements()
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $entity = $items->getEntity();
    $variation = NULL;
    // Cas 1 : le champ est sur une variation
    if ($entity->getEntityTypeId() === 'commerce_product_variation') {
      $variation = $entity;
    }
    // Cas 2 : le champ est sur un produit
    elseif ($entity->getEntityTypeId() === 'commerce_product') {
      /** @var \Drupal\commerce_product\Entity\ProductInterface $entity */
      $variation = $entity->getDefaultVariation();
    }
    if ($variation) {
      $user = \Drupal::currentUser();
      $config = \Drupal::config('commerce_wishlist.settings');
      $wishlist_type = $config->get('default_type');
      //
      $datas = [
        '#theme' => 'commerce_wishlist_button',
        '#entity_id' => $variation->id(),
        '#settings' => $this->getSettings(),
        '#attributes' => [
          'class' => [
            'commerce-wishlist-button--wrapper'
          ],
          'data-variation-id' => $variation->id()
        ],
        '#attached' => [
          'library' => [
            'commerce_wishlist_button/button'
          ]
        ],
        '#cache' => [
          'contexts' => [
            'user.permissions'
          ],
          'tags' => $variation->getCacheTags()
        ]
      ];
      /**
       *
       * @var \Drupal\commerce_wishlist\Entity\Wishlist $wishlist
       */
      $wishlist = $this->wishlistProvider->getWishlist($wishlist_type, $user);
      if ($wishlist) {
        $WishlistItems = $this->entityTypeManager->getStorage('commerce_wishlist_item')->loadByProperties([
          'wishlist_id' => $wishlist->id(),
          'purchasable_entity' => $variation->id()
        ]);
        if ($WishlistItems) {
          $datas['#attributes']['class'][] = 'add';
        }
      }
      $elements[] = $datas;
    }
    return $elements;
  }

  public static function defaultSettings() {
    return [
      'button_label' => t('Add to Wishlist'),
      'button_icon' => '<svg width="3rem" height="3rem" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" stroke-width="3" stroke="#000000" fill="none"><path d="M9.06,25C7.68,17.3,12.78,10.63,20.73,10c7-.55,10.47,7.93,11.17,9.55a.13.13,0,0,0,.25,0c3.25-8.91,9.17-9.29,11.25-9.5C49,9.45,56.51,13.78,55,23.87c-2.16,14-23.12,29.81-23.12,29.81S11.79,40.05,9.06,25Z"/></svg>',
      'button_css_class' => 'wishlist-button'
    ] + parent::defaultSettings();
  }

  /**
   *
   * {@inheritdoc}
   * @see \Drupal\Core\Field\FormatterBase::settingsForm()
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];
    $elements['button_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Button Label'),
      '#default_value' => $this->getSetting('button_label')
    ];
    $elements['button_css_class'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CSS class'),
      '#default_value' => $this->getSetting('button_css_class'),
      '#description' => $this->t('CSS classes to apply to the button.')
    ];
    $elements['button_icon'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Button icon'),
      '#default_value' => $this->getSetting('button_icon')
    ];
    return $elements + parent::settingsForm($form, $form_state);
  }

  /**
   *
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Label: @button_label', [
      '@label' => $this->getSetting('button_label')
    ]);
    $summary[] = $this->t('CSS classes: @button_css_class', [
      '@classes' => $this->getSetting('button_css_class')
    ]);

    return $summary;
  }

  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    // Autoriser uniquement les champs entity_reference
    if ($field_definition->getType() !== 'entity_reference') {
      return FALSE;
    }
    // Autoriser uniquement les références vers les variations
    return $field_definition->getSetting('target_type') === 'commerce_product_variation';
  }
}