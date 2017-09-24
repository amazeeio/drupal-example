<?php

namespace Drupal\drupalconvienna\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'DrupalConViennaBlock' block.
 *
 * @Block(
 *  id = "drupal_con_vienna_block",
 *  admin_label = @Translation("DrupalCon Vienna Block"),
 * )
 */
class DrupalConViennaBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['drupal_con_vienna_block']['#markup'] = 'Hello DrupalCon Vienna!';

    return $build;
  }

}
