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
    $variation = $items->getEntity();
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
      'button_label' => 'Add to Wishlist',
      'button_icon' => '❤️',
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
}