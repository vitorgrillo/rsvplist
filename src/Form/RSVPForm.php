<?php
/*
 * @file
 * Contains \Drupal\rsvplist\Form\RSVPForm
 */

namespace Drupal\rsvplist\Form;

use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides an RSVP Email form.
 */
class RSVPForm extends FormBase {
  /*
   * (@inheritdoc)
   */
  public function getFormId() {
    return 'rsvplist_email_form';
  }

  /*
 * (@inheritdoc)
 */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $nid = 1;
    $node = \Drupal::routeMatch()->getParameter('node');

    if ($node instanceof \Drupal\node\NodeInterface) {
      $nid = $node->nid->value;
    }

    $form['email'] = [
      '#title' => t('Email Address'),
      '#type' => 'textfield',
      '#size' => 25,
      '#description' => t("We'll send updates to the email address you provide."),
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('RSVP'),
    ];

    $form['nid'] = [
      '#type' => 'hidden',
      '#value' => $nid,
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $value = $form_state->getValue('email');

    if ($value == !\Drupal::service('email.validator')->isValid($value)) {
      $form_state->setErrorByName('email', t('The email address %mail is not valid', ['%mail' => $value]));
      return;
    }

    $node = \Drupal::routeMatch()->getParameter('node');
    //Check if email already is set for this node.
    $select = Database::getConnection()->select('rsvplist', 'r');
    $select->fields('r', ['nid']);
    $select->condition('nid', $node->id());
    $select->condition('mail', $value);
    $results = $select->execute();
    if (!empty($results->fetchCol())) {
      // We found a row with this nid and email.
      $form_state->setErrorByName('email', t('The address %mail is already subscribed to this list.',
      ['%mail' => $value]));
    }
  }

  /*
  * (@inheritdoc)
  */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
    db_insert('rsvplist')
      ->fields([
        'mail' => $form_state->getValue('email'),
        'nid' => $form_state->getValue('nid'),
        'uid' => $user->id(),
        'created' => time(),
      ])
      ->execute();
    drupal_set_message(t('Thank for your RSVP, you are on the list for the event.'));
  }
}
