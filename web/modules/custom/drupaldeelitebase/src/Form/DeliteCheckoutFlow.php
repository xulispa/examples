<?php
/**
 * @file
 * Contains \Drupal\drupaldeelitebase\Form\ContributeForm.
 */
namespace Drupal\drupaldeelitebase\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


class DeliteCheckoutFlow extends FormBase {

/**
 * {@inheritdoc}
 */
public function getFormId() {
return 'drupaldeelitebase_default_form';
}

/**
 * {@inheritdoc}
 */
public function buildForm(array $form, FormStateInterface $form_state) {
$form['name'] = [
'#type' => 'textfield',
 '#title' => t('Name'),
 '#required' => TRUE,
];
$form['email'] = [
'#type' => 'email',
 '#title' => t('E-mail'),
 '#required' => TRUE,
];
$form['telephone'] = [
'#type' => 'textfield',
 '#title' => t('Telephone'),
 '#required' => TRUE,
];
$form['message'] = [
'#type' => 'textarea',
 '#title' => t('Message'),
 '#required' => TRUE,
];

$form['submit'] = [
'#type' => 'submit',
 '#value' => t('Submit'),
];
return $form;
}

/**
 * {@inheritdoc}
 */
public function validateForm(array &$form, FormStateInterface $form_state) {

}

/**
 * {@inheritdoc}
 */
public function submitForm(array &$form, FormStateInterface $form_state) {
// Display result.
foreach ($form_state->getValues() as $key => $value) {
drupal_set_message($key . ': ' . $value);
}

}
}
?>