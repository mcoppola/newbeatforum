
<div id="node-<?php print $node->nid; ?>" class="node<?php if ($sticky) { print ' sticky'; } ?><?php if (!$status) { print ' node-unpublished'; } ?> clear-block">

<?php if ($page == 0): ?>
  <h2><a href="<?php drupal_render( $node_url ) ?>" title="<?php drupal_render( $title ); ?>"><?php drupal_render( $title ); ?></a></h2>
<?php endif; ?>

<?php if ($submitted || isset($terms)): ?>
  <div class="meta">
  <?php if ($submitted): ?>
    <div class="submitted"><?php print drupal_render( $submitted ); ?></div>
  <?php endif; ?>
    
  </div>
<?php endif; ?>

  <div class="content clear-block">
    <?php print drupal_render( $picture ); ?>
    <?php print drupal_render( $content ); ?>
  </div>

<?php
  if (isset($links)) {
    print '<div class="node-links">';
    print drupal_render($links);
    print '</div>';
  }
  if (isset($terms)) {
    print '<div class="terms">'.t('Tags').': '. $terms .'</div>';
  }
?>

</div>
