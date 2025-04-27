<?php

namespace Drupal\commerce_wishlist_button\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\commerce_wishlist\Plugin\Block\WishlistBlock;

/**
 * Provides a wishlist block.
 *
 * @Block(
 *   id = "wishlist_button_block",
 *   admin_label = @Translation("Wishlist button block"),
 *   category = @Translation("Commerce")
 * )
 */
class CommerceWishlistBlock extends WishlistBlock {
  
  /**
   *
   * {@inheritdoc}
   * @see \Drupal\Component\Plugin\ConfigurableInterface::defaultConfiguration()
   */
  public function defaultConfiguration() {
    return [
      'button_icon' => '❤️',
      'buttom_class' => ''
    ] + parent::defaultConfiguration();
  }
  
  /**
   *
   * {@inheritdoc}
   * @see \Drupal\Core\Block\BlockPluginInterface::blockForm()
   */
  public function blockForm($form, $form_state) {
    $form = parent::blockForm($form, $form_state);
    $form['button_icon'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Button icon'),
      '#default_value' => $this->configuration['button_icon']
    ];
    $form['buttom_class'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Button class'),
      '#default_value' => $this->configuration['buttom_class']
    ];
    return $form;
  }
  
  public function blockSubmit($form, $form_state) {
    $this->configuration['button_icon'] = $form_state->getValue('button_icon');
    $this->configuration['buttom_class'] = $form_state->getValue('buttom_class');
  }
  
  /**
   * Builds the wishlist block.
   *
   * @return array A render array.
   */
  public function build() {
    /** @var \Drupal\commerce_wishlist\Entity\WishlistInterface[] $wishlists */
    $wishlist = $this->wishlistProvider->getWishlist('default');
    $count = $wishlist ? count($wishlist->getItems()) : 0;
    $build['content'] = [
      '#theme' => 'commerce_wishlist_button_block',
      '#count' => $count,
      '#button_icon' => $this->configuration['button_icon'],
      '#wishlist_entity' => $wishlist,
      '#url' => Url::fromRoute('commerce_wishlist.page'),
      '#attributes' => [
        'class' => [
          $this->configuration['buttom_class']
        ]
      ]
    ];
    // dd($build);
    return $build;
  }
}