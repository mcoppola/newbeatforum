<?php
function kanji_preprocess_page(&$variables) {
  if (isset($variables['main_menu'])) {
    $pid = variable_get('menu_main_links_source', 'main-menu');
    $tree = menu_tree($pid);
    $variables['primary_nav'] = str_replace('menu', 'sf-menu menu', drupal_render($tree));
  } else {
    $variables['primary_nav'] = FALSE;
  }
}

function kanji_breadcrumb(&$variables) {
  $output = '';
  $breadcrumb = $variables['breadcrumb'];
  $show_breadcrumb = theme_get_setting('breadcrumb_display');
  if ($show_breadcrumb == 'yes') {
    if (!empty($breadcrumb)) {
      // Provide a navigational heading to give context for breadcrumb links to
      // screen-reader users. Make the heading invisible with .element-invisible.
      $output = '<h2 class="element-invisible">' . t('You are here') . '</h2>';

      $output .= '<div class="breadcrumb">' . implode(' | ', $breadcrumb) . '</div>';
    } else {
      $output = '<div class="breadcrumb">' . t('Home') . '</div>';
    }
  }
  return $output;
}

function kanji_feed_icon(&$variables) {
  $text = t('Subscribe to @feed-title', array('@feed-title' => $variables['title']));
  if ($image = theme('image', array('path' => path_to_theme() . '/images/rss.png', 'alt' => $text))) {
    return l($image, $variables['url'], array('html' => TRUE, 'attributes' => array('class' => array('feed-icon'), 'title' => $text)));
  }
}

function kanji_preprocess_html(&$variables) {
  // Add conditional stylesheets for IE
  drupal_add_css(path_to_theme() . '/ie_styles.css', array('group' => CSS_THEME, 'browsers' => array('IE' => 'IE', '!IE' => FALSE), 'preprocess' => FALSE));

  // Add background
  if (theme_get_setting('default_background') == 1) {
    $variables['background'] = 'background-image: url(\'/' . path_to_theme() . '/images/bg.jpg\')'; 
  }
  elseif (theme_get_setting('background_color') && theme_get_setting('background_color_value')) {
    $variables['background'] = 'background: #' . theme_get_setting('background_color_value');
  }
  elseif (theme_get_setting('path_background')) {
    $bg = variable_get('kanji:path_background', '');
    $bg = file_create_url($bg);
    $variables['background'] = 'background-image: url(\'' . $bg . '\')';
  }
}
function mytheme_preprocess_node(&$variables) {
    $patheme = drupal_get_path('theme','mytheme');
    $links = $variables['node']->links;
    global $user;
    
    //Repeat following for each $links item you wish to change
    if($links['comment_add']){
      $links['comment_add']['title'] = theme('image', $patheme.'/images/comments_add.png', t('Add a comment'),t('Add a comment'));
      $links['comment_add']['html'] = true;
    }
    
    //Remove link example: removes link to 'Original Feed' (Feed API)
    unset($links['feedapi_feed']);
    
    //Removes 'Login to post comments' link for anon users
    if (!$user->uid) {
      unset($links['comment_forbidden']);
    }
    
    //Simple way to affect order that links are rendered
    //Move addthis to end of $links
    if( isset($links['addthis']) ) {
      $addthis = $links['addthis'];
      unset($links['addthis']);
      $links = array_merge($links, array('addthis' => $addthis));
    }

    //Reset node template links
    $variables['links'] = theme_links($links, array('class' => 'links'));
}