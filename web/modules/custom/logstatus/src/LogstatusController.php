<?php

namespace Drupal\logstatus;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;


class LogstatusController extends ControllerBase {

  public function infoPage() {

    $config = \Drupal::config('lagoon_logs.settings');

    $form = [];

    $form['ll_settings_description'] = [
      '#prefix' => '<div class="ll-settings-description">',
      '#suffix' => '</div>',
      '#markup' => t('This page simply lists the current settings for the Lagoon Logs module. The defaults are set in configuration, this page is meant primarily for troubleshooting.'),
    ];

    $form['ll_settings_logs_host'] = [
      '#prefix' => '<div class="ll-settings-key-value">',
      '#suffix' => '</div>',
      '#markup' => t('Logstash host: ') . $config->get('host')
    ];

    $form['ll_settings_logs_port'] = [
      '#prefix' => '<div class="ll-settings-key-value">',
      '#suffix' => '</div>',
      '#markup' => t('Logstash port: ') . $config->get('port'),
    ];

    $form['ll_settings_logs_identifier'] = [
      '#prefix' => '<div class="ll-settings-key-value">',
      '#suffix' => '</div>',
      '#markup' => t('Logstash leading identifier: ') . $config->get('identifier'),
    ];

    return $form;
  }
  
}