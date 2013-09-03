<?php



/**
 * @file
 * Configuration forms and helper functions for VotingAPI module.
 */

/**
 * Administrative settings for VotingAPI.
 */
function votingapi_settings_form($form_state) {
  $period = array(0 => t('Immediately')) + drupal_map_assoc(array(300, 900, 1800, 3600, 10800, 21600, 32400, 43200, 86400, 172800, 345600, 604800), 'format_interval') + array(-1 => t('Never'));

  $form['votingapi_anonymous_window'] = array(
    '#type' => 'select',
    '#title' => t('Anonymous vote rollover'),
    '#description' => t('The amount of time that must pass before two anonymous votes from the same computer are considered unique. Setting this to \'never\' will eliminate most double-voting, but will make it impossible for multiple anonymous on the same computer (like internet cafe customers) from casting votes.'),
    '#default_value' => variable_get('votingapi_anonymous_window', 86400),
    '#options' => $period
  );

  $form['votingapi_calculation_schedule'] = array(
    '#type' => 'radios',
    '#title' => t('Vote tallying'),
    '#description' => t('On high-traffic sites, administrators can use this setting to postpone the calculation of vote results.'),
    '#default_value' => variable_get('votingapi_calculation_schedule', 'immediate'),
    '#options' => array(
      'immediate' => t('Tally results whenever a vote is cast'),
      'cron' => t('Tally results at cron-time'),
      'manual' => t('Do not tally results automatically: I am using a module that manages its own vote results.')
    ),
  );

  return system_settings_form($form);
}

function votingapi_rebuild_form($form_state) {
    $query = db_select('votingapi_vote')
    ->fields('votingapi_vote', array('tag'))->distinct(TRUE);
  $results = $query->execute()->fetchAll(PDO::FETCH_ASSOC);
  $tags = array('All Tags');
  foreach ($results as $tag_type) {
    $tags[] = $tag_type['tag'];
  }

  $form['results'] = array(
    '#type' => 'fieldset',
    '#title' => t('Voting Results Rebuilding'),
  );
  $form['results']['votingapi_rebuild_content_selected_tag'] = array(
    '#type' => 'select',
    '#title' => t('Content tags'),
    '#options' => $tags,
  );
   $form['results']['votingapi_rebuild_content_tags'] = array(
    '#type' => 'hidden',
    '#value' => $tags,
  );
  $form['results']['votingapi_recalculate_all'] = array(
    '#type' => 'submit',
    '#value' => t('Rebuild all Voting Results'),
    '#description' => t('This action rebuilds all voting results, and may be a lengthy process.'),
  );
  $form['results']['help'] = array(
    '#prefix' => '<div class="description">',
    '#markup' => t('This action rebuilds all voting results, and may be a lengthy process.'),
    '#suffix' => '</div>',
  );

  return $form;
}

/**
 * Developer tool to generate dummy votes.
 */
function votingapi_generate_votes_form() {
  $form['node_types'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Which node types should receive votes?'),
    '#options' => node_type_get_names(),
    '#default_value' => array_keys(node_type_get_names()),
  );

  $form['vote_type'] = array(
    '#type' => 'select',
    '#title' => t('What type of votes should be generated?'),
    '#options' => array(
      'percent' => t('Percentage (Fivestar style)'),
      'points' => t('Point-based (Digg style)'),
    ),
    '#default_value' => 'percent',
  );

  $form['kill_votes'] = array(
    '#type' => 'checkbox',
    '#title' => t('Delete existing votes before generating new ones.'),
    '#default_value' => FALSE,
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Do it!'),
  );
  return $form;
}

/**
 * Submit handler for votingapi_generate_votes_form.
 */
function votingapi_generate_votes_form_submit($form, &$form_state) {
  $options = array(
    'types' => $form_state['values']['node_types'],
    'kill' => $form_state['values']['kill_votes'],
  );
  require_once(drupal_get_path('module', 'votingapi') . '/votingapi.drush.inc');
  votingapi_generate_votes('node', $form_state['values']['vote_type'], $options);
}

/**
 * form submit function that recalculates voting results for all content
 * Sometimes the votingapi_vote table gets out of sync with the votingapi_cache
 * table and then the cache table needs to be rebuild. This can be a lengthy
 * process and it uses the batch api to rebuild those results
 *
 * http://api.drupal.org/api/group/batch/6
 */
function votingapi_rebuild_form_submit($form, &$form_state) {
  $tags = $form_state['values']['votingapi_rebuild_content_tags'];
  $selected_tag_index = $form_state['values']['votingapi_rebuild_content_selected_tag'];

  // Change the list of tags to remove accordingly.
  if ($selected_tag_index > 0) {
    $tags = array($tags[$selected_tag_index]);
  }
  else {
    unset($tags[0]);
  }

  $operations = array();
  foreach ($tags as $tag) {
    $operations[] = array('_votingapi_results_rebuild', array($tag));
  }
  $batch = array(
    'title' => t('Recalculating'),
    'operations' => $operations,
    'init_message' => 'Preparing to recalculate',
    'finished' => '_votingapi_results_rebuild_finished',
    'file' => drupal_get_path('module', 'votingapi') . '/votingapi.admin.inc',
  );
  // set the batch and let FAPI call batch_process
  batch_set($batch);
  
  // set a sensable redirect
  $form_state['redirect'] = 'admin/config/search/votingapi/rebuild';
}

/**
 * Batch API callback
 */
function _votingapi_results_rebuild($tag, &$context) {
  if (empty($context['sandbox']['max'])) {

    // set up the stuff!
    $context['sandbox']['progress'] = 0;
    $context['sandbox']['current_entity_id'] = 0;
    
    // since $context will be populated across invokations per content type
    // we only set count to 0 when it hasn't been set yet
    if (!isset($context['results']['count'])) {
      $context['results']['count'] = 0;
    }
    // get the max for this content type
    $context['sandbox']['max'] = db_query('SELECT MAX(entity_id) FROM {votingapi_vote} WHERE tag= :tag', array(':tag' => $tag))->fetchField();
  }

  $limit = 100;
  
  // grab the entity_id's
  // $result = db_query_range("SELECT DISTINCT(entity_id) FROM {votingapi_vote} WHERE content_type  '%s' AND entity_id > %d ORDER BY entity_id ASC", $type, $context['sandbox']['current_entity_id'], 0, $limit);
  $result = db_query_range('SELECT entity_type, entity_id FROM {votingapi_vote} WHERE entity_id > 0 AND tag = :tag AND entity_id > :cid ORDER BY entity_id  ASC', 0, $limit, array(':tag' => $tag, ':cid' => $context['sandbox']['current_entity_id']));

  foreach($result as $row) {
    watchdog('votingapi', 'trying type @type @entity', array('@type' => $type, '@entity' => $row->entity_id), WATCHDOG_ERROR);

    $context['sandbox']['progress']++;
    $context['sandbox']['current_entity_id'] = $row->entity_id;
    // force recalc
    votingapi_recalculate_results($row->entity_type, $row->entity_id, TRUE);
    $context['results']['count']++;
  }
  
  // check for if not finished
  if ($context['sandbox']['current_entity_id'] != $context['sandbox']['max']) {
    $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
  }
  else {
    // if we are finished with this run, delete the context[sandbox]
    unset($context['sandbox']);
  }
}

/**
 * Finish batch callback
 */
function _votingapi_results_rebuild_finished($success, $results, $operations) {
  if ($success) {
    $message = format_plural($results['count'], 'One piece of content recalculated.', '@count ieces of content recalculated');
  }
  else {
    $message = t('Finished with an error.');
  }
  drupal_set_message($message);
}