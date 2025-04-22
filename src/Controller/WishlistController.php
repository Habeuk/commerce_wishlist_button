<?php

namespace Drupal\commerce_wishlist_button\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Session\AccountInterface;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_wishlist\WishlistProviderInterface;
use Drupal\commerce_wishlist\WishlistManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class WishlistController extends ControllerBase {
  
  /**
   *
   * @var \Drupal\commerce_wishlist\WishlistProvider
   */
  protected $wishlistProvider;
  
  /**
   *
   * @var \Drupal\commerce_wishlist\WishlistManager
   */
  protected $wishlistManager;
  
  /**
   *
   * {@inheritdoc}
   */
  public function __construct(WishlistProviderInterface $wishlist_provider, WishlistManagerInterface $wishlist_manager) {
    $this->wishlistProvider = $wishlist_provider;
    $this->wishlistManager = $wishlist_manager;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('commerce_wishlist.wishlist_provider'), $container->get('commerce_wishlist.wishlist_manager'));
  }
  
  public function access(AccountInterface $account, ProductVariation $commerce_product_variation) {
    return AccessResult::allowedIfHasPermission($account, 'access wishlist');
  }
  
  public function add(ProductVariation $commerce_product_variation) {
    $response = new AjaxResponse();
    $config = $this->config('commerce_wishlist.settings');
    $wishlist_type = $config->get('default_type');
    
    try {
      // Récupération/création de la wishlist
      $wishlist = $this->wishlistProvider->getWishlist($wishlist_type) ?? $this->wishlistProvider->createWishlist($wishlist_type);
      
      // Ajout de la variation
      $this->wishlistManager->addEntity($wishlist, $commerce_product_variation);
      
      // Mise à jour du bouton
      $selector = '.wishlist-button-wrapper[data-variation-id="' . $commerce_product_variation->id() . '"]';
      $response->addCommand(
        new ReplaceCommand($selector, [
          '#theme' => 'commerce_wishlist_button',
          '#variation' => $commerce_product_variation,
          '#settings' => [
            'button_label' => $this->t('Added!')
          ]
        ]));
    }
    catch (\Exception $e) {
      $this->getLogger('commerce_wishlist_button')->error($e->getMessage());
      $response->addCommand(new ReplaceCommand($selector, $this->t('Error occurred')));
    }
    
    return $response;
  }
}