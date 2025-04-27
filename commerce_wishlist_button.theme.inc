<?php

function template_preprocess_commerce_wishlist_button(&$vars) {
  $vars['entity_id'] = $vars['elements']['#entity_id'];
  $vars['settings'] = $vars['elements']['#settings'];
  $vars['attributes'] = $vars['elements']['#attributes'];
}