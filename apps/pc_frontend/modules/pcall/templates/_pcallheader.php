  <div class="navbar navbar-fixed-top navbar-inverse">
    <div class="navbar-inner">
      <div class="container">
        <h1 class="brand"><?php echo link_to($op_config['sns_name'], '@homepage') ?></h1>
        <ul class="nav pull-right">
          <li>
            <?php echo link_to('ログアウト', '@member_logout') ?>
          </li>
        </ul>
      </div>
    </div>
  </div>
