<?php

namespace Drupal\content_kanban;

class NodeTypeConfig {

  /**
   * @var string
   */
  public $id = '';

  /**
   * @var string
   */
  public $label = '';

  /**
   * @var string
   */
  public $color = '';

  /**
   * NodeTypeConfig constructor.
   *
   * @param string $node_type
   * @param string $label
   * @param string $color
   */
  public function __construct($node_type, $label, $color) {
    $this->id = $node_type;
    $this->label = $label;
    $this->color = $color;
  }

  /**
   * Set color
   *
   * @param string $value
   */
  public function setColor($value) {
    $this->color = $value;
  }

  /**
   * Get Label
   *
   * @return string
   */
  public function getLabel() {
    return $this->label;
  }

}