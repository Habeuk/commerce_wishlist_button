<?php

namespace Drupal\commerce_wishlist_button\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;

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
  
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $variation = $items->getEntity();
    // dd($variation);
    $elements[] = [
      '#theme' => 'commerce_wishlist_button',
      '#entity_id' => $variation->id(),
      '#settings' => $this->getSettings(),
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
    
    return $elements;
  }
  
  public static function defaultSettings() {
    return [
      'button_label' => 'Add to Wishlist',
      'button_icon' => '❤️',
      'button_css_class' => 'wishlist-button'
    ] + parent::defaultSettings();
  }
  
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
    ] + parent::settingsForm($form, $form_state);
    
    return $elements;
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