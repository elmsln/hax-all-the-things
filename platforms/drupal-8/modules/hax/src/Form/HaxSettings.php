<?php

namespace Drupal\hax\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\Element;
use Drupal\Core\Url;
use Drupal\hax\HaxService;

/**
 * Class HaxSettings.
 *
 * @package Drupal\hax\Form
 */
class HaxSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'hax_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('hax.settings');

    foreach (Element::children($form) as $variable) {
      $config->set($variable, $form_state->getValue($form[$variable]['#parents']));
    }
    $config->save();

    if (method_exists($this, '_submitForm')) {
      $this->_submitForm($form, $form_state);
    }

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('hax.settings');

    $form['hax_offset_left'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Offset'),
      '#default_value' => $config->get('hax_offset_left'),
      '#description' => $this->t("Helps with theme compatibility when positioning the context menu. Adjust this if HAX context menu doesn't correctly align with the side of your content when editing. Value is in pixels but should not include px. Some themes that mess with box-model may or may not have this issue."),
    ];
    $form['hax_project_location'] = [
      '#type' => 'textfield',
      '#title' => $this->t('HAX Location'),
      '#default_value' => $config->get('hax_project_location'),
      '#maxlength' => 1000,
      '#description' => $this->t("Use this to point to CDNs or if you've installed your web components some place else. Start without a slash and end with a slash."),
    ];

    $form['hax_autoload_element_list'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Elements to autoload'),
      '#default_value' => $config->get('hax_autoload_element_list'),
      '#maxlength' => 1000,
      '#description' => $this->t("This allows for auto-loading elements known to play nice with HAX. If you've written any webcomponents that won't automatically be loaded into the page via that module this allows you to attempt to auto-load them when HAX loads. For example, if you have a video-player element in your bower_components directory and want it to load on this interface, this would be a simple way to do that. Spaces only between elements, no comma"),
    ];

    $hax = new HaxService();
    $baseApps = $hax->baseSupportedApps();
    foreach ($baseApps as $key => $app) {
      $form['hax_' . $key . '_key'] = [
        '#type' => 'textfield',
        '#title' => $this->t('@name API key', [
          '@name' => $app['name'],
        ]),
        '#default_value' => $config->get('hax_' . $key . '_key'),
        '#description' => Link::fromTextAndUrl($this->t('See @name developer docs',
          ['@name' => $app['name']]), Url::fromUri($app['docs'])),
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['hax.settings'];
  }

}
