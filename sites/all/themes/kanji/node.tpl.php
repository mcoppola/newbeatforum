


<div id="node-<?php print $node->nid; ?>" class="<?php print $classes; ?>">
  <?php if($display_submitted && !$page): ?>
    <div class="date">
      <div class="textdate">
        <div class="day"><?php print format_date($created, 'custom', 'j'); ?></div>
        <div class="month"><?php print format_date($created, 'custom', 'M'); ?></div>
      </div>
    </div>
  <?php endif; ?>
	<?php print render($title_prefix); ?>
  <?php if (!$page): ?>
    <h2 class="title"<?php print $title_attributes; ?>><a href="<?php print $node_url; ?>"><?php print $title; ?></a></h2>
  <?php endif; ?>
	<?php print render($title_suffix); ?>

  <?php if ($display_submitted): ?>
    <div class="meta">
      <span class="submitted"><?php print t('!username -- !datetime', array('!username' => $name, '!datetime' => $date)); ?></span>
    </div>
  <?php endif; ?>
  <?php if($user_picture): print $user_picture; endif; ?>
  <div class="content clear-block"<?php print $content_attributes; ?>>
    <?php
      hide($content['comments']);
      hide($content['links']);
      print render($content);
    ?>
  </div>
  <?php if($content['links']): ?><div class="node-links"><?php print render($content['links']); ?></div><?php endif; ?>
</div>
<?php print render($content['comments']); ?>