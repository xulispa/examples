<?php

/**
 * @file
 * Allows users to change the color scheme of themes.
 */

use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\social_font\Entity\Font;

/**
 * Implements hook_form_FORM_ID_alter().
 */
function ecultura_theme_form_system_theme_settings_alter(&$form, FormStateInterface &$form_state, $form_id = NULL) {
  // Work-around for a core bug affecting admin themes. See issue #943212.
  if (isset($form_id)) {
    return;
  } else {
    ecultura_theme_theme_settings_add_new_colors();
    return $form;
  }
  
  $theme = \Drupal::configFactory()->get('system.theme')->get('default');
  
  // Check for the current active theme.
  $active_theme = \Drupal::theme()->getActiveTheme()->getName();
  
  // Check for the current default theme.
  $system_theme_settings = \Drupal::configFactory()->get('system.theme')->get('default');
  
  // If the default theme is either ecultura_theme or socialsaas then extend
  // the form in the appearance section.
  if (array_key_exists('socialbase', \Drupal::service('theme.manager')->getActiveTheme()->getBaseThemes())) {
    if ($active_theme == $system_theme_settings) {
      $config = \Drupal::config($theme . '.settings');
      
      $form['open_social_settings'] = [
        '#type' => 'vertical_tabs',
        '#title' => t('Opensocial settings'),
        '#weight' => -50,
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
      ];
      
      $form['os_color_settings'] = [
        '#type' => 'details',
        '#group' => 'open_social_settings',
        '#title' => t('Border radius'),
        '#weight' => 20,
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
      ];
      
      $form['os_font_settings'] = [
        '#type' => 'details',
        '#group' => 'open_social_settings',
        '#title' => t('Fonts'),
        '#weight' => 10,
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
      ];
      
      $form['os_color_settings']['card_radius'] = [
        '#type' => 'number',
        '#title' => t('Card border radius (px)'),
        '#default_value' => $config->get('card_radius'),
        '#description' => t('Define the roundness of cards corners.'),
      ];
      
      $form['os_color_settings']['form_control_radius'] = [
        '#type' => 'number',
        '#title' => t('Form control border radius (px)'),
        '#default_value' => $config->get('form_control_radius'),
        '#description' => t('Define the roundness of the corners of form-controls, like <code>input</code>, <code>textarea</code> and <code>select</code>'),
      ];
      
      $form['os_color_settings']['button_radius'] = [
        '#type' => 'number',
        '#title' => t('Button border radius (px)'),
        '#default_value' => $config->get('button_radius'),
        '#description' => t('Define the roundness of buttons corners.'),
      ];
      
      $form['os_email_settings'] = [
        '#type' => 'details',
        '#group' => 'open_social_settings',
        '#title' => t('E-mail'),
        '#weight' => 30,
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
      ];
      
      $form['os_email_settings']['email_logo'] = [
        '#type' => 'managed_file',
        '#title' => t('Logo for e-mails'),
        '#description' => t('Upload a logo which is shown in e-mail sent by the platform. This overrides the default logo that is also used in e-mails when no logo is provided here.'),
        '#default_value' => $config->get('email_logo'),
        '#upload_location' => 'public://',
        '#upload_validators' => [
          'file_validate_is_image' => [],
          'file_validate_extensions' => ['gif png jpg jpeg'],
        ],
      ];
      // Ensure we save the file permanently.
      $form['#submit'][] = 'ecultura_theme_save_email_logo';
      
      $form['os_hero_settings'] = [
        '#type' => 'details',
        '#group' => 'open_social_settings',
        '#title' => t('Hero'),
        '#weight' => 30,
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
      ];
      
      $form['os_hero_settings']['hero_gradient_opacity'] = [
        '#type' => 'range',
        '#title' => t('Hero gradient'),
        '#default_value' => $config->get('hero_gradient_opacity'),
        '#description' => t('Define the percentage of darkness of the hero gradient from 0 to 100.'),
        '#min' => 0,
        '#max' => 100,
      ];
      
      // Font tab.
      $fonts = [];
      if (\Drupal::service('module_handler')->moduleExists('social_font')) {
        
        /** @var \Drupal\social_font\Entity\Font $font_entities */
        foreach (Font::loadMultiple() as $font_entities) {
          $fonts[$font_entities->id()] = $font_entities->get('name')->value;
        }
        
      }
      
      $form['os_font_settings']['font_primary'] = [
        '#type' => 'select',
        '#title' => t('Font'),
        '#options' => $fonts,
        '#default_value' => $config->get('font_primary'),
        '#description' => t("The font family to use."),
      ];
      
    }
    
  }
  
}

/**
 * Marks the e-mail logo file as permanent.
 *
 * This ensures the image is not cleaned up by Drupal's temporary file cleaning.
 *
 * @param array $form
 *   The submitted form structure.
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 *   The state of the submitted form.
 *
 * @throws \Drupal\Core\Entity\EntityStorageException
 */
function ecultura_theme_save_email_logo(array $form, FormStateInterface $form_state) {
  $email_logo = $form_state->getValue('email_logo');
  // If an e-mail logo was uploaded then we mark the uploaded file as permanent.
  if (!empty($email_logo)) {
    $file = File::load($email_logo[0]);
    $file->setPermanent();
    $file->save();
  }
}

function ecultura_theme_theme_settings_add_new_colors() {
  // Add in any new colors that are defined in default scheme
  // but are not defined in the saved palette values.
  // Supplements logic in color.module color_scheme_form().
  $theme = 'ecultura_theme';
  $info = color_get_info($theme);
  $names = $info['fields'];
  $palette = color_get_palette($theme); //calls variable_get
  $default = color_get_palette($theme, TRUE);
  $new = array();
  foreach ($default as $name => $value) {
    if (!isset($palette[$name]) && isset($names[$name])) {
      $palette[$name] = $default[$name];
      $new[] = $names[$name];
    }
  }
  if (count($new)) {
    drupal_set_message(t('One or more new colors are now available: @colors',
      array('@colors' => implode(', ', $new))));
    variable_set('color_' . $theme . '_palette', $palette);
  }
}
