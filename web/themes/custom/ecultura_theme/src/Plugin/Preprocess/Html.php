<?php

namespace Drupal\ecultura_theme\Plugin\Preprocess;

use Drupal\socialbase\Plugin\Preprocess\Html as HtmlBase;
//use Drupal\Component\Utility\Html as HtmlBase;

/**
 * Pre-processes variables for the "Html" theme hook.
 *
 * @ingroup plugins_preprocess
 *
 * @BootstrapPreprocess("Html")
 */
class Html extends HtmlBase {

    /**
     * {@inheritdoc}
     */
    public function preprocess(array $variables, $hook, array $info) {
        parent::preprocess($variables, $hook, $info);

        $variables['colors'] = [];

        foreach (color_get_palette($variables['theme']['name']) as $key => $value) {
            $key = str_replace('-', '_', $key);

            $variables['colors'][$key] = $value;
        }
        // Get all SVG Icons.
        $variables['svg_icons'] = file_get_contents(drupal_get_path('theme', 'ecultura_theme') . '/assets/icons/icons.svg');
    }
}
