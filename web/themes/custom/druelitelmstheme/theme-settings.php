<?php

use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Implements hook_form_system_theme_settings_alter().
 *
 * Form override for theme settings.
 */
function druelitelmstheme_form_system_theme_settings_alter(&$form, FormStateInterface &$form_state, $form_id = NULL)
{
  // Work-around for a core bug affecting admin themes. See issue #943212.
  if (isset($form_id)) {
    return;
  }

  // Load file before running process (prevent not found on ajax, validate and submit handlers)
  $build_info = $form_state->getBuildInfo();
  $theme_settings_files[] = drupal_get_path('theme', 'druelitelmstheme') . '/theme-settings.php';
  $theme_settings_files[] = drupal_get_path('theme', 'druelitelmstheme') . '/druelitelmstheme.theme';
  foreach ($theme_settings_files as $theme_settings_file) {
    if (!in_array($theme_settings_file, $build_info['files'])) {
      $build_info['files'][] = $theme_settings_file;
    }
  }
  $form_state->setBuildInfo($build_info);

  // Load external library
  if (theme_get_setting('browser_sync.enabled')) {
    $form['#attached']['library'][] = 'druelitelmstheme/druelitelmstheme_settings_dev';
  } else {
    $form['#attached']['library'][] = 'druelitelmstheme/druelitelmstheme_settings';
  }

  // if (\Drupal::moduleHandler()->moduleExists('color')) {
  //   // Add some descriptions to clarify what each color is used for.
  //   foreach (array(
  //      'white' => t("e.g. main menu active menu link background"),
  //      'very_light_gray' => t("e.g. body background color"),
  //      'light_gray' => t('e.g content background color'),
  //      'medium_gray' => t('e.g. title background color, table background color'),
  //      'dark_gray' => t('e.g. forum tools background'),
  //      'light_blue' => t('e.g. link hover color, tabs background, fieldset titles'),
  //      'dark_blue' => t('e.g. link color, tabs active/hover background color'),
  //      'deep_blue' => t('e.g. header background color, footer background color'),
  //      'leaf_green' => t('e.g. form submit buttons, local actions'),
  //      'blood_red' => t('e.g. form delete buttons'),
  //   ) as $color => $description) {
  //     $form['color']['palette'][$color]['#description'] = $description;
  //   }
  //
  //   // Hide the base and link ones. They're just there to prevent Notices.
  //   $form['color']['palette']['base']['#type'] = 'hidden';
  //   $form['color']['palette']['link']['#type'] = 'hidden';
  //
  //   // Make color section collapsible.
  //   $form['color']['#type'] = 'details';
  //   $form['color']['#open'] = FALSE;
  //
  //   $form['#submit'][] = 'druelitelmstheme_form_system_theme_settings_alter_color_submit';
  // }

  // druelitelmstheme header settings
  $form['druelitelmstheme_header_settings'] = [
    '#type' => 'details',
    '#title' => t('Header background'),
    '#open' => TRUE,
  ];

  $form['druelitelmstheme_header_settings']['druelitelmstheme_use_header_background'] = [
    '#type' => 'checkbox',
    '#title' => t('Use another image for the header background'),
    '#description' => t('Check here if you want the theme to use a custom image for the header background.'),
    '#default_value' => theme_get_setting('druelitelmstheme_use_header_background'),
  ];

  $form['druelitelmstheme_header_settings']['image'] = [
    '#type' => 'container',
    '#states' => [
      'invisible' => [
        'input[name="druelitelmstheme_use_header_background"]' => ['checked' => FALSE],
      ],
    ],
  ];

  $form['druelitelmstheme_header_settings']['image']['druelitelmstheme_header_image_path'] = [
    '#type' => 'textfield',
    '#title' => t('The path to the header background image.'),
    '#description' => t('The path to the image file you would like to use as your custom header background (relative to sites/default/files). The suggested size for the header background is 3000x134.'),
    '#default_value' => theme_get_setting('druelitelmstheme_header_image_path'),
  ];

  $form['druelitelmstheme_header_settings']['image']['druelitelmstheme_header_image_upload'] = [
    '#type' => 'managed_file',
    '#upload_location' => 'public://',
    '#upload_validators' => [
      'file_validate_is_image' => ['gif png jpg jpeg'],
    ],
    '#title' => t('Upload an image'),
    '#description' => t('If you don\'t have direct file access to the server, use this field to upload your header background image.'),
  ];

  // druelitelmstheme homepage settings
  $druelitelmstheme_home_page_settings = theme_get_setting('druelitelmstheme_home_page_settings');

  $form['druelitelmstheme_home_page_settings'] = [
    '#type' => 'details',
    '#title' => t('Homepage settings'),
    '#tree' => TRUE,
    '#open' => TRUE,
  ];

  $form['druelitelmstheme_home_page_settings']['druelitelmstheme_use_home_page_markup'] = [
    '#type' => 'checkbox',
    '#title' => t('Use a different homepage for anonymous users.'),
    '#description' => t('Check here if you want the theme to use a custom page for users that are not logged in.'),
    '#default_value' => $druelitelmstheme_home_page_settings['druelitelmstheme_use_home_page_markup'],
  ];

  if (!$form_state->get('num_slides') && isset($druelitelmstheme_home_page_settings['druelitelmstheme_home_page_slides'])) {
    $nb_slides = isset($druelitelmstheme_home_page_settings['druelitelmstheme_home_page_slides']['actions']) ? count($druelitelmstheme_home_page_settings['druelitelmstheme_home_page_slides']) - 1 : count($druelitelmstheme_home_page_settings['druelitelmstheme_home_page_slides']);
    $form_state->set('num_slides', $nb_slides);
  }

  $num_slides = $form_state->get('num_slides');
  if (!$num_slides) {
    $form_state->set('num_slides', DRUELITELMSTHEME_HOMEPAGE_DEFAULT_NUM_SLIDES);
    $num_slides = DRUELITELMSTHEME_HOMEPAGE_DEFAULT_NUM_SLIDES;
  }

  $form['druelitelmstheme_home_page_settings']['druelitelmstheme_home_page_slides'] = [
    '#type' => 'container',
    '#prefix' => '<div id="druelitelmstheme-home-page-settings-slides-wrapper">',
    '#suffix' => '</div>',
  ];

  for ($i = 1; $i <= $num_slides; $i++) {
    $form['druelitelmstheme_home_page_settings']['druelitelmstheme_home_page_slides'][$i] = [
      '#type' => 'details',
      '#title' => t('Slide @num', ['@num' => $i]),
      '#tree' => TRUE,
      '#open' => TRUE,
      '#states' => [
        'invisible' => [
          'input[name="druelitelmstheme_home_page_settings[druelitelmstheme_use_home_page_markup]"]' => ['checked' => FALSE],
        ],
      ],
    ];

    $form['druelitelmstheme_home_page_settings']['druelitelmstheme_home_page_slides'][$i]['druelitelmstheme_home_page_markup_wrapper'] = [
      '#type' => 'text_format',
      '#base_type' => 'textarea',
      '#title' => t('Home page content'),
      '#description' => t('Set the content for the home page. This will be used for users that are not logged in.'),
      '#format' => isset($druelitelmstheme_home_page_settings['druelitelmstheme_home_page_slides'][$i]['druelitelmstheme_home_page_markup_wrapper']['format']) ? $druelitelmstheme_home_page_settings['druelitelmstheme_home_page_slides'][$i]['druelitelmstheme_home_page_markup_wrapper']['format'] : filter_default_format(),
      '#default_value' => isset($druelitelmstheme_home_page_settings['druelitelmstheme_home_page_slides'][$i]['druelitelmstheme_home_page_markup_wrapper']['value']) ? $druelitelmstheme_home_page_settings['druelitelmstheme_home_page_slides'][$i]['druelitelmstheme_home_page_markup_wrapper']['value'] : NULL,
    ];

    $form['druelitelmstheme_home_page_settings']['druelitelmstheme_home_page_slides'][$i]['druelitelmstheme_home_page_image_path'] = [
      '#type' => 'textfield',
      '#title' => t('The path to the home page background image.'),
      '#description' => t('The path to the image file you would like to use as your custom home page background (relative to sites/default/files).'),
      '#default_value' => isset($druelitelmstheme_home_page_settings['druelitelmstheme_home_page_slides'][$i]['druelitelmstheme_home_page_image_path']) ? $druelitelmstheme_home_page_settings['druelitelmstheme_home_page_slides'][$i]['druelitelmstheme_home_page_image_path'] : NULL,
    ];

    $form['druelitelmstheme_home_page_settings']['druelitelmstheme_home_page_slides'][$i]['druelitelmstheme_home_page_image_upload'] = [
      '#name' => 'druelitelmstheme_home_page_image_upload_'. $i,
      '#type' => 'managed_file',
      '#upload_location' => 'public://',
      '#upload_validators' => [
        'file_validate_is_image' => ['gif png jpg jpeg'],
      ],
      '#title' => t('Upload an image'),
      '#description' => t('If you don\'t have direct file access to the server, use this field to upload your background image.'),
    ];
  }

  $form['druelitelmstheme_home_page_settings']['druelitelmstheme_home_page_slides']['actions'] = [
    '#type' => 'actions',
    '#states' => [
      'invisible' => [
        'input[name="druelitelmstheme_home_page_settings[druelitelmstheme_use_home_page_markup]"]' => ['checked' => FALSE],
      ],
    ],
  ];

  $form['druelitelmstheme_home_page_settings']['druelitelmstheme_home_page_slides']['actions']['add_name'] = [
    '#type' => 'submit',
    '#value' => ($num_slides < 1) ? t('Add a slide') : t('Add another slide'),
    '#submit' => ['druelitelmstheme_form_system_theme_settings_add_slide_callback'],
    '#ajax' => [
      'callback' => 'druelitelmstheme_form_system_theme_settings_slide_callback',
      'wrapper' => 'druelitelmstheme-home-page-settings-slides-wrapper',
    ],
  ];

  if ($num_slides > 1) {
    $form['druelitelmstheme_home_page_settings']['druelitelmstheme_home_page_slides']['actions']['remove_name'] = [
      '#type' => 'submit',
      '#value' => t('Remove latest slide'),
      '#submit' => ['druelitelmstheme_form_system_theme_settings_remove_slide_callback'],
      '#ajax' => [
        'callback' => 'druelitelmstheme_form_system_theme_settings_slide_callback',
        'wrapper' => 'druelitelmstheme-home-page-settings-slides-wrapper',
      ],
    ];
  }

  // Main menu settings.
  if (\Drupal::moduleHandler()->moduleExists('menu_ui')) {

    $form['druelitelmstheme_menu_settings'] = [
      '#type' => 'details',
      '#title' => t('Menu settings'),
      '#open' => TRUE,
    ];

    $form['druelitelmstheme_menu_settings']['druelitelmstheme_menu_source'] = [
      '#type' => 'select',
      '#title' => t('Main menu source'),
      '#options' => [0 => t('None')] + menu_ui_get_menus(),
      '#description' => t('The menu source to use for the tile navigation. If \'none\', Drupal de Elite will use a default list of tiles.'),
      '#default_value' => theme_get_setting('druelitelmstheme_menu_source'),
    ];

    $form['druelitelmstheme_menu_settings']['druelitelmstheme_menu_show_for_anonymous'] = [
      '#type' => 'checkbox',
      '#title' => t('Show menu for anonymous users'),
      '#description' => t('Show the main menu for users that are not logged in. Only links that users have access to will show up.'),
      '#default_value' => theme_get_setting('druelitelmstheme_menu_show_for_anonymous'),
    ];
  }

  // CSS settings.
  $form['druelitelmstheme_css_settings'] = [
    '#type' => 'details',
    '#title' => t('CSS overrides'),
    '#open' => true,
  ];

  $form['druelitelmstheme_css_settings']['druelitelmstheme_css_override_content'] = [
    '#type' => 'textarea',
    '#title' => t('CSS overrides'),
    '#description' => t('You can write CSS rules here. They will be stored in a CSS file in your public files directory. Change it\'s content to alter the display of your site.'),
    '#default_value' => _druelitelmstheme_get_css_override_file_content(),
  ];

  $form['druelitelmstheme_css_settings']['druelitelmstheme_css_override_fid'] = [
    '#type' => 'hidden',
    '#value' => (_druelitelmstheme_get_css_override_file()) ? _druelitelmstheme_get_css_override_file()->id() : NULL,
  ];

  // browser sync
  $form['eaufeel_browser_sync'] = [
    '#type' => 'fieldset',
    '#title' => t('BrowserSync Settings'),
  ];
  $form['eaufeel_browser_sync']['browser_sync']['#tree'] = TRUE;
  $form['eaufeel_browser_sync']['browser_sync']['enabled'] = [
    '#type' => 'checkbox',
    '#title' => t('Enable BrowserSync support for theme'),
    '#default_value' => theme_get_setting('browser_sync.enabled'),
  ];

  $form['eaufeel_browser_sync']['browser_sync']['host'] = [
    '#type' => 'textfield',
    '#title' => t('BrowserSync host'),
    '#default_value' => theme_get_setting('browser_sync.host'),
    '#description' => t("Default: localhost. Enter 'HOST' which will be replaced by your site's hostname."),
    '#states' => [
      'visible' => [':input[name="browser_sync[enabled]"]' => ['checked' => TRUE]],
    ],
  ];

  $form['eaufeel_browser_sync']['browser_sync']['port'] = [
    '#type' => 'number',
    '#title' => t('Enable BrowserSync support for theme'),
    '#default_value' => theme_get_setting('browser_sync.port'),
    '#description' => t("Default: '3000'."),
    '#states' => [
      'visible' => [':input[name="browser_sync[enabled]"]' => ['checked' => TRUE]],
    ],
  ];

  // Validate and submit
  $form['#validate'][] = 'druelitelmstheme_form_system_theme_settings_alter_validate';
  $form['#submit'][] = 'druelitelmstheme_form_system_theme_settings_alter_submit';

  $form_state->setCached(FALSE);
}

/**
 * Validation callback for druelitelmstheme_form_system_theme_settings_alter().
 */
function druelitelmstheme_form_system_theme_settings_alter_validate($form, &$form_state)
{
  if (in_array('druelitelmstheme_form_system_theme_settings_remove_slide_callback', $form_state->getSubmitHandlers()) ||
      in_array('druelitelmstheme_form_system_theme_settings_add_slide_callback', $form_state->getSubmitHandlers()) ||
      in_array('file_managed_file_submit', $form_state->getSubmitHandlers())
    ) {
    return;
  }

  $storage = $form_state->getStorage();
  $new_storage = [];

  $fid = $form_state->getValue('druelitelmstheme_header_image_upload');
  if (!empty($fid) && $fid[0]) {
    $file = File::load($fid[0]);
    if ($file) {
      $file->setPermanent();
      $file->save();
      $file_usage = \Drupal::service('file.usage');
      $file_usage->add($file, 'druelitelmstheme', 'user', \Drupal::currentUser()->id());
      $new_storage['druelitelmstheme_header_image_path'] = $file;
    } else {
      $form_state->setErrorByName('druelitelmstheme_header_image_upload', t('Couldn\'t upload file.'));
    }
  }

  $druelitelmstheme_home_page_settings = $form_state->getValue('druelitelmstheme_home_page_settings');

  for ($i = 1; $i <= $storage['num_slides']; $i++) {
    $fid = $druelitelmstheme_home_page_settings['druelitelmstheme_home_page_slides'][$i]['druelitelmstheme_home_page_image_upload'];

    if (!empty($fid) && $fid[0]) {
      $file = File::load($fid[0]);
      if ($file) {
        $file->setPermanent();
        $file->save();
        $file_usage = \Drupal::service('file.usage');
        $file_usage->add($file, 'druelitelmstheme', 'user', \Drupal::currentUser()->id());
        $new_storage['druelitelmstheme_home_page_image_path'][$i] = $file;
      } else {
        $form_state->setErrorByName('druelitelmstheme_home_page_settings', t('Couldn\'t upload file.'));
      }
    }
  }

  if ($form_state->getValue('druelitelmstheme_css_override_content')) {
    if ($fid = _druelitelmstheme_store_css_override_file($form_state->getValue('druelitelmstheme_css_override_content'))) {
      $new_storage['css_fid'] = $fid;
    } else {
      $form_state->setErrorByName('druelitelmstheme_css_override_content', t('Could not save the CSS in a file. Perhaps the server has no write access. Check your public files folder permissions.'));
    }
  }

  $form_state->setStorage($new_storage);
}

/**
 * Submission callback for druelitelmstheme_form_system_theme_settings_alter().
 */
function druelitelmstheme_form_system_theme_settings_alter_submit($form, &$form_state)
{
  $storage = $form_state->getStorage();

  if (isset($storage['druelitelmstheme_header_image_path']) && $storage['druelitelmstheme_header_image_path']) {
    $file = $storage['druelitelmstheme_header_image_path'];
    $form_state->setValue('druelitelmstheme_header_image_path', str_replace('public://', '', $file->getFileUri()));
  }

  if (isset($storage['druelitelmstheme_home_page_image_path']) && $storage['druelitelmstheme_home_page_image_path']) {
    foreach ($storage['druelitelmstheme_home_page_image_path'] as $key => $file) {
      $druelitelmstheme_home_page_settings = $form_state->getValue('druelitelmstheme_home_page_settings');
      $druelitelmstheme_home_page_settings['druelitelmstheme_home_page_slides'][$key]['druelitelmstheme_home_page_image_path'] = str_replace('public://', '', $file->getFileUri());
      $form_state->setValue('druelitelmstheme_home_page_settings', $druelitelmstheme_home_page_settings);
    }
  }

  if ($form_state->getValue('druelitelmstheme_css_override_content')) {
    if (isset($storage['css_fid']) && $storage['css_fid']) {
      $form_state->setValue('druelitelmstheme_css_override_fid', $storage['css_fid']);
    }
  } else {
    // If there is a file existing already, we must get rid of it.
    if ($file = _druelitelmstheme_get_css_override_file()) {
      // "Store" an empty string. This will not create a file, but set the old one as a temporary one.
      _druelitelmstheme_store_css_override_file('');

      // Set the setting to 0 to not use any file.
      $form_state->setValue('druelitelmstheme_css_override_fid', 0);
    }
  }
}

/**
 * Submission callback for druelitelmstheme_form_system_theme_settings_alter().
 *
 * Specific logic for color integration. Remove white from the images to make them
 * transparent.
 */
function druelitelmstheme_form_system_theme_settings_alter_color_submit($form, $form_state)
{
  // if ($files = variable_get('color_druelitelmstheme_files', FALSE)) {
  //   foreach ($files as $generated_file) {
  //     _druelitelmstheme_color_remove_white($generated_file);
  //   }
  // }
}



/**
 * Submit handler for the "add" button.
 *
 * Increments the max counter and causes a rebuild.
 */
function druelitelmstheme_form_system_theme_settings_add_slide_callback(array &$form, FormStateInterface $form_state)
{
  $form_state->set('num_slides', $form_state->get('num_slides') + 1);
  $form_state->setRebuild();
}

/**
 * Submit handler for the "remove" button.
 *
 * Decrements the max counter and causes a form rebuild.
 */
function druelitelmstheme_form_system_theme_settings_remove_slide_callback(array &$form, FormStateInterface $form_state)
{
  if ($form_state->get('num_slides') > 1) {
    $form_state->set('num_slides', $form_state->get('num_slides') - 1);
  }
  $form_state->setRebuild();
}
