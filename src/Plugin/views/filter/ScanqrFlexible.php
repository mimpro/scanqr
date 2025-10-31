<?php

namespace Drupal\scanqr\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Drupal\views\Views;

/**
 * Configurable QR Scanner filter.
 *
 * @ViewsFilter("scanqr_flexible")
 */
class ScanqrFlexible extends FilterPluginBase {

  /**
   * {@inheritdoc}
   */
  public function defineOptions() {
    $options = parent::defineOptions();
    $options['target_table'] = ['default' => ''];
    $options['target_field'] = ['default' => ''];
    $options['relationship'] = ['default' => 'none'];
    $options['operator'] = ['default' => '='];
    $options['value_mode'] = ['default' => 'raw']; // raw|digits|regex
    $options['regex'] = ['default' => ''];
    $options['regex_group'] = ['default' => 1];
    // Keep exposed by default.
    $options['expose'] = ['default' => TRUE];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function adminSummary() {
    return $this->t('QR to %table.%field', [
      '%table' => $this->options['target_table'] ?: '?',
      '%field' => $this->options['target_field'] ?: '?',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    // Relationships available in this view.
    $relationships = ['none' => $this->t('None (base table)')];
    $rel_handlers = [];
    if (!empty($this->view->display_handler)) {
      $rel_handlers = $this->view->display_handler->getHandlers('relationship');
      foreach ($rel_handlers as $id => $handler) {
        $relationships[$id] = $handler->adminLabel();
      }
    }

    // Tables available: base table + relationship tables + tables used by
    // fields already added to this View (helps expose field tables like
    // node__field_foo, commerce_product__sku, etc.).
    $tables = [];
    $base_table = $this->view->storage->get('base_table');
    if ($base_table) {
      $tables[$base_table] = $base_table . ' (base)';
    }
    if (!empty($rel_handlers)) {
      foreach ($rel_handlers as $handler) {
        $t = $handler->definition['table'] ?? '';
        if ($t) {
          $tables[$t] = $t;
        }
      }
    }
    // Include tables from fields already on the display.
    if (!empty($this->view->display_handler)) {
      $field_handlers = $this->view->display_handler->getHandlers('field');
      foreach ($field_handlers as $fh) {
        // Handler exposes ->table when applicable.
        if (!empty($fh->table)) {
          $tables[$fh->table] = $fh->table;
        }
      }
    }

    // Fields for currently selected table.
    $selected_table = $form_state->getValue(['options', 'target_table']) ?? $this->options['target_table'] ?? $base_table;
    $field_options = [];
    if ($selected_table) {
      $data = Views::viewsData()->get($selected_table);
      if (is_array($data)) {
        foreach ($data as $column => $definition) {
          // Skip meta entries.
          if ($column === 'table') {
            continue;
          }
          if (is_array($definition) && (isset($definition['filter']) || isset($definition['field']) || isset($definition['argument']))) {
            $label = $definition['title'] ?? $column;
            $field_options[$column] = $label . " ($column)";
          }
        }
      }
      // Fallback: if still empty, try to list columns from field handlers bound to this table.
      if (empty($field_options) && !empty($field_handlers)) {
        foreach ($field_handlers as $id => $fh) {
          if (!empty($fh->table) && $fh->table === $selected_table && !empty($fh->field)) {
            $label = $fh->adminLabel() ?: $fh->field;
            $field_options[$fh->field] = $label . ' (' . $fh->field . ')';
          }
        }
      }
      asort($field_options);
    }

    $form['target_table'] = [
      '#type' => 'select',
      '#title' => $this->t('Target table'),
      '#options' => $tables,
      '#default_value' => $this->options['target_table'] ?: $base_table,
      '#required' => TRUE,
      '#ajax' => [
        'callback' => [get_class($this), 'optionsAjaxRefresh'],
        'wrapper' => 'scanqr-flexible-field-wrapper',
      ],
      '#description' => $this->t('Table containing the field to compare the scanned value against.'),
    ];

    $form['target_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Target field/column'),
      '#options' => $field_options,
      '#default_value' => $this->options['target_field'] ?: '',
      '#required' => TRUE,
      '#prefix' => '<div id="scanqr-flexible-field-wrapper">',
      '#suffix' => '</div>',
      '#description' => $this->t('Column to filter by (e.g., nid, field_sku_value).'),
    ];

    $form['relationship'] = [
      '#type' => 'select',
      '#title' => $this->t('Relationship'),
      '#options' => $relationships,
      '#default_value' => $this->options['relationship'] ?? 'none',
      '#description' => $this->t('If the target field comes from a relationship, choose it here.'),
    ];

    $form['operator'] = [
      '#type' => 'select',
      '#title' => $this->t('Operator'),
      '#options' => [
        '=' => '=',
        '<>' => '<>',
        'LIKE' => 'LIKE',
        'IN' => 'IN',
      ],
      '#default_value' => $this->options['operator'] ?? '=',
    ];

    $form['value_processing'] = [
      '#type' => 'details',
      '#title' => $this->t('Value processing'),
      '#open' => FALSE,
    ];
    $form['value_processing']['value_mode'] = [
      '#type' => 'radios',
      '#title' => $this->t('Transform scanned value'),
      '#options' => [
        'raw' => $this->t('Use as-is'),
        'digits' => $this->t('Keep digits only'),
        'regex' => $this->t('Regex capture'),
      ],
      '#default_value' => $this->options['value_mode'] ?? 'raw',
    ];
    $form['value_processing']['regex'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Regex pattern'),
      '#default_value' => $this->options['regex'] ?? '',
      '#states' => [
        'visible' => [
          ':input[name="options[value_mode]"]' => ['value' => 'regex'],
        ],
      ],
      '#description' => $this->t('e.g. /nid:(\\d+)/i or /(\\d+)/'),
    ];
    $form['value_processing']['regex_group'] = [
      '#type' => 'number',
      '#title' => $this->t('Capture group'),
      '#default_value' => $this->options['regex_group'] ?? 1,
      '#min' => 0,
      '#states' => [
        'visible' => [
          ':input[name="options[value_mode]"]' => ['value' => 'regex'],
        ],
      ],
    ];
  }

  /**
   * AJAX callback to refresh fields dropdown when table changes.
   */
  public static function optionsAjaxRefresh(array &$form, FormStateInterface $form_state) {
    return $form['options']['target_field'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildExposedForm(&$form, FormStateInterface $form_state) {
    parent::buildExposedForm($form, $form_state);

    $identifier = $this->options['expose']['identifier'] ?? 'scanqr';
    $form[$identifier] = [
      '#type' => 'scanqr',
      '#title' => $this->t('Scan'),
      '#placeholder' => $this->t('Scan or type…'),
      '#attributes' => [
        'data-views-autosubmit' => '1',
        'class' => ['scanqr-views-input'],
      ],
      '#attached' => [
        'library' => ['scanqr/scanqr.views'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function acceptExposedInput($input) {
    $identifier = $this->options['expose']['identifier'] ?? 'scanqr';
    if (!isset($input[$identifier]) || $input[$identifier] === '') {
      return FALSE;
    }
    $value = trim((string) $input[$identifier]);

    switch ($this->options['value_mode'] ?? 'raw') {
      case 'digits':
        $value = preg_replace('/\D+/', '', $value ?? '');
        break;

      case 'regex':
        $pattern = $this->options['regex'] ?? '';
        $group = (int) ($this->options['regex_group'] ?? 1);
        if ($pattern && @preg_match($pattern, '') !== FALSE && preg_match($pattern, $value, $m)) {
          $value = $m[$group] ?? '';
        }
        break;
    }

    if ($value === '') {
      return FALSE;
    }

    // IN expects array.
    if (($this->options['operator'] ?? '=') === 'IN') {
      $this->value = array_map('trim', explode(',', $value));
    }
    else {
      $this->value = $value;
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    if (!isset($this->value)) {
      return;
    }
    $table = $this->options['target_table'] ?: $this->view->storage->get('base_table');
    $field = $this->options['target_field'];
    if (!$table || !$field) {
      return;
    }

    $relationship = $this->options['relationship'] ?? 'none';
    $relationship = $relationship === 'none' ? NULL : $relationship;

    $alias = $this->query->ensureTable($table, $relationship);
    $operator = $this->options['operator'] ?? '=';

    $this->query->addWhere(0, "$alias.$field", $this->value, $operator);
  }

}
