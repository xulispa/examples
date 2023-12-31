<?php

use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ExtensionDiscovery;
use Drupal\system\Controller\ThemeController;
use Drupal\Core\Theme\ThemeManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\file\Entity\File;
use Drupal\Core\Form\FormStateInterface;

function xulispa_theme_form_system_theme_settings_alter(&$form, &$form_state) {

  $theme = \Drupal::theme()->getActiveTheme()->getName();
  $config = \Drupal::config($theme . '.settings');

  // Setting the build info because of this issue
  // https://www.drupal.org/node/1862892
  $theme_file = drupal_get_path('theme', $theme) . '/theme-settings.php';
  $build_info = $form_state->getBuildInfo();

  if (!in_array($theme_file, $build_info['files'])) {
    $build_info['files'][] = $theme_file;
  }

  $form_state->setBuildInfo($build_info);

  // Unset options from base theme/
  $form['upload_backgrounds']['#access'] = FALSE;
  $form['#submit'] = array('xulispa_theme_form_system_theme_settings_submit');
  $form['druppiosmallbusiness']['#access'] = FALSE;
  $form['druppiosmallbusinessstyle']['#access'] = FALSE;
  $form['advanced']['#access'] = FALSE;

  // 0. Vertical tabs.
  $form['xulispatheme_settings'] = array(
    '#type' => 'vertical_tabs',
    '#title' => 'Xulispa theme Settings',
  );

  $form['xulispa_theme_upload_backgrounds'] = array(
    '#type' => 'details',
    '#title' => t('Upload background images'),
    '#description' => '<strong>' . t('Background image settings') . '</strong><br /><br />' . t('These are the images which will appear on the front page as a background for each section on the right side. <br /><strong>The recommended width is 700 pixels and the height is minimum 2000 pixels (the height mainly depends on the content size). </strong><br />You can upload images with different sizes, but their sides might not align properly in line if they differ in proportions.'),
    '#group' => 'xulispatheme_settings',
  );

  $form['xulispa_theme_upload_backgrounds']['bg_upload_first'] = array(
    '#type' => 'managed_file',
    '#title' => t('Upload image for the first section'),
    '#upload_location' => 'public://background_images/',
    '#default_value' => $config->get('bg_upload_first'),
    '#suffix' => '<br />',
  );

  $form['xulispa_theme_upload_backgrounds']['bg_upload_second'] = array(
    '#type' => 'managed_file',
    '#title' => t('Upload image for the second section'),
    '#upload_location' => 'public://background_images/',
    '#default_value' => $config->get('bg_upload_second'),
    '#suffix' => '<br />',
  );

  $form['xulispa_theme_upload_backgrounds']['bg_upload_third'] = array(
    '#type' => 'managed_file',
    '#title' => t('Upload image for the third section'),
    '#upload_location' => 'public://background_images/',
    '#default_value' => $config->get('bg_upload_third'),
    '#suffix' => '<br />',
  );

  $form['xulispa_theme_upload_backgrounds']['bg_upload_fourth'] = array(
    '#type' => 'managed_file',
    '#title' => t('Upload image for the fourth section'),
    '#upload_location' => 'public://background_images/',
    '#default_value' => $config->get('bg_upload_fourth'),
    '#suffix' => '<br />',
  );

  $form['xulispa_theme_upload_backgrounds']['bg_upload_fifth'] = array(
    '#type' => 'managed_file',
    '#title' => t('Upload image for the fifth section'),
    '#upload_location' => 'public://background_images/',
    '#default_value' => $config->get('bg_upload_fifth'),
    '#suffix' => '<br />',
  );

  $form['#submit'][] = 'xulispa_theme_form_system_theme_settings_submit';

  // 1. Style settings tab.
  $form['xulispatheme_settings_style'] = array(
    '#type' => 'details',
    '#title' => t('Style'),
    '#description' => '<strong>' . t('Custom CSS Settings') . '</strong>',
    '#group' => 'xulispatheme_settings',
  );

  $form['xulispatheme_settings_style']['xulispa_theme_custom_css_enable'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable custom CSS'),
    '#description' => t('Enable this option if you want to add custom CSS without
    modifying theme. When you enable it, a new text area will appear below, where
    you can enter CSS. Entire content of text area will be saved in the following file: "/sites/default/files/xulispa-theme-custom.css"
    after you click on the "Save configuration" button. If you disable this option you will
    not loose any of your existing custom CSS, but it will not be applied to pages.'),
    '#default_value' => theme_get_setting('xulispa_theme_custom_css_enable'),
    '#group' => 'xulispatheme_settings_style',
  );

  // Load custom CSS from file.
  if (file_exists('public://xulispa-theme-custom.css'))
    $custom_css = file_get_contents('public://xulispa-theme-custom.css');
  else
    $custom_css = '';

  $form['xulispatheme_settings_style']['xulispa_theme_custom_css'] = array(
    '#type' => 'textarea',
    '#title' => t('Custom CSS'),
    '#rows' => 10,
    '#resizable' => TRUE,
    '#default_value' => $custom_css,
    '#description' => t('Enter you custom CSS here or navigate to "/sites/default/files/xulispa-theme-custom.css" and edit the file directly.
    Note: remember not to change the file name.'),
    '#states' => array(
      "visible" => array(
        "input[name='xulispa_theme_custom_css_enable']" => array("checked" => TRUE)),
    ),
    '#group' => 'xulispatheme_settings_style',
  );

  $form['xulispatheme_settings_advanced'] = array(
    '#type' => 'details',
    '#title' => t('Advanced settings'),
    '#group' => 'xulispatheme_settings',
  );

  $form['xulispatheme_settings_advanced']['xulispa_theme_cache'] = array(
    '#type' => 'checkbox',
    '#title' => t('Locally cache external JS files'),
    '#description' => t("If checked, external JS files will be cached locally. They are updated daily to ensure updates are reflected in the local copy."),
    '#default_value' => $config->get('xulispa_theme_cache'),
  );

  $form['#validate'][] = 'xulispa_theme_form_system_theme_settings_validate';
  $form['#submit'][] = 'xulispa_theme_form_system_theme_settings_submit';

}

function xulispa_theme_form_system_theme_settings_validate(&$form, FormStateInterface $form_state) {
  // Clear obsolete local cache if cache has been disabled.
  if ($form_state->isValueEmpty('xulispa_theme_cache') && $form['xulispatheme_settings_advanced']['xulispa_theme_cache']['#default_value']) {
    xulispa_theme_clear_js_cache();
  }
}

/**
 * Save custom CSS to file on form submit.
 */
function xulispa_theme_form_system_theme_settings_submit(&$form, FormStateInterface $form_state) {
  $css = $form_state->getValue('xulispa_theme_custom_css');
  file_put_contents('public://xulispa-theme-custom.css', $css);

  $theme = \Drupal::theme()->getActiveTheme()->getName();
  $sections = array('first', 'second', 'third', 'fourth', 'fifth');

  foreach ($sections as $section) {
    $image = $form_state->getValue('bg_upload_' . $section);
    if (!empty($image)) {
      $file = File::load($image[0]);
      $file->setPermanent();
      $file->save();
      $file_usage = \Drupal::service('file.usage');
      $file_usage->add($file, $theme, 'node', 1);
    }
  }
}
