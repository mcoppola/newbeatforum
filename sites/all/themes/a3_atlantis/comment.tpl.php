<div class="clear-block comment<?php print ($comment->new) ? ' comment-new' : ''; print ($comment->status == COMMENT_NOT_PUBLISHED) ? ' comment-unpublished' : ''; print ($comment->uid == $node->uid) ? ' author' : ''; ?>">

<?php if ($comment->new) : ?>
  <a id="new"></a>
  <span class="new"><?php print drupal_render( $new )?></span>
<?php endif; ?>

  <?php print drupal_render( $picture )?>
  <h3><?php print drupal_render( $title )?></h3>

  <div class="submitted">
    <?php print drupal_render( $submitted )?>
  </div>

  <div class="content">
    <?php print drupal_render( $content )?>
    <?php if ($signature): ?>
      <div class="user-signature clear-block">
        <?php print drupal_render( $signature )?>
      </div>
    <?php endif; ?>
  </div>

  <?php if( isset($links) ) { print drupal_render( $links ); }?>
</div>
