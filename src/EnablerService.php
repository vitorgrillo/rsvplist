<?php
/**
 * @file
 * Contains \Drupal\rsvplist\EnablerService
 */
namespace Drupal\rsvplist;

use Drupal\Core\Database\Database;
use Drupal\node\Entity\Node;

/**
 * Defines a service for managing RSVP List enabled for nodes.
 */
class EnablerService {

    /**
    * Sets a individual node to be RSVP enabled.
    *
    * @param \Drupal\node\Entity\Node $node
    */
    public function setEnabled(Node $node) {
      if (!$this->isEnabled($node)) {
        /** @var \Drupal\Core\Database\Connection $connection */
        $connection = \Drupal::service('database');
        $connection->insert('rsvplist_enabled')
          ->fields(['nid' => $node->id()])
          ->execute();
      }
    }

    /**
     * Checks if an individual node is RSVP enabled.
     *
     * @param \Drupal\node\Entity\Node $node
     *
     * @return bool
     *  Whether the node is enabled for the RSVP functionality.
     */
    public function isEnabled(Node $node) {
      if ($node->isNew()) {
        return FALSE;
      }

      $select = Database::getConnection()->select('rsvplist_enabled', 're');
      $select->fields('re', ['nid']);
      $select->condition('nid', $node->id());
      $results = $select->execute();
      return !empty($results->fetchCol());
    }

    /**
     * Deletes enabled settings for an individual node.
     *
     * @param \Drupal\node\Entity\Node $node
     */
    public function delEnabled(Node $node) {
      $delete = Database::getConnection()->delete('rsvplist_enabled');
      $delete->condition('nid', $node->id());
      $delete->execute();
    }

}

