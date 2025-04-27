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
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Component\Serialization\Json;

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
  
  public function addRemove(ProductVariation $commerce_product_variation) {
    $response = new AjaxResponse();
    $config = $this->config('commerce_wishlist.settings');
    $wishlist_type = $config->get('default_type');
    $datas = [];
    $user = \Drupal::currentUser();
    try {
      // Récupération/création de la wishlist
      /**
       *
       * @var \Drupal\commerce_wishlist\Entity\Wishlist $wishlist
       */
      $wishlist = $this->wishlistProvider->getWishlist($wishlist_type, $user) ?? $this->wishlistProvider->createWishlist($wishlist_type, $user);
      $WishlistItems = $this->entityTypeManager()->getStorage('commerce_wishlist_item')->loadByProperties(
        [
          'wishlist_id' => $wishlist->id(),
          'purchasable_entity' => $commerce_product_variation->id()
        ]);
      if ($WishlistItems) {
        $WishlistItem = reset($WishlistItems);
        $message = $this->t('remove to Wishlist');
        $this->wishlistManager->removeWishlistItem($wishlist, $WishlistItem, true);
        $datas = [
          'action' => 'remove'
        ];
      }
      else {
        $message = $this->t('add to Wishlist');
        $this->wishlistManager->addEntity($wishlist, $commerce_product_variation);
        $datas = [
          'action' => 'add'
        ];
      }
      
      $response = new JsonResponse();
    }
    catch (\Exception $e) {
      $message = $e->getMessage();
      $this->getLogger('commerce_wishlist_button')->error($message);
    }
    $response->headers->set('Access-Control-Expose-Headers', "CustomStatusText");
    $response->headers->set('CustomStatusText', $message);
    $response->setContent(Json::encode($datas));
    return $response;
  }
}