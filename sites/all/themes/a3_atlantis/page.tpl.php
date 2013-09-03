<?php
// $Id: page.tpl.php,v 1.1.4.4 2008/08/23 20:58:56 johnforsythe Exp $
?>
<div id="container">
<div id="page"<?php if (!$page['sidebar_first']) { print " class='wide-page'"; } ?>>
  <div id="main">
    <div id="content">
      <?php print drupal_render( $page['header'] ) ?>
      <div class="tabs"><?php drupal_render( $tabs )?></div>
      <?php if ($title) { ?><h1 class="title"><?php print $title ?></h1><?php } ?> 
      <?php print drupal_render( $page['help'] )?>
      <?php if ($show_messages && $messages) { drupal_render( $messages ); } ?>
      <?php print drupal_render( $page['content'] )?>
      <?php print drupal_render( $breadcrumb )?>
    </div>
    <div id="right">
      <?php if ($page['sidebar_first']) { ?>
        <div id="sidebar-left">
          <?php print drupal_render( $page['sidebar_first'] )?>
        </div>
      <?php } ?>
      <?php if ($page['sidebar_second']) { ?>
        <div id="sidebar-right">
          <?php print $search_box ?>
          <?php print drupal_render( $page['sidebar_second'] )?>
        </div>
      <?php } ?>
    </div>
    <div class="clear-both"></div>
  </div>
  <div id="header">
    <div id="title">
      <?php if ($site_name) { ?><h1><a href="<?php print $base_path ?>" title="<?php print t('Home') ?>"><?php print $site_name ?></a></h1><?php } ?>
      <?php if ($site_slogan) { ?><div class='site-slogan'><?php print $site_slogan ?></div><?php } ?>
      <div id="title-spacer"></div>
    </div>
    <div id="nav">
      <?php if (isset($main_menu)) { ?><?php print theme('links', $main_menu, array('class' =>'links', 'id' => 'navlist')) ?><?php } ?>
    </div>
  </div>
  <div id="footer">
    <?php print drupal_render( $page['footer'] ) ?>
    <p><a href="http://blamcast.net/">Drupal theme</a> designed by Blamcast.</p>
  </div>
</div>
</div>
