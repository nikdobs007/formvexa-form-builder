<?php

defined('ABSPATH') || exit;

?>
<div class="wrap">
    <h1 class="wp-heading-inline">Forms</h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=formnova-builder')); ?>" class="page-title-action">
        Add New
    </a>

    <hr class="wp-header-end" />

    <?php settings_errors('formnova'); ?>
    
    <!-- Search -->
    <form method="get">

        <input type="hidden" name="page" value="formnova">

        <?php
        wp_nonce_field(
            'formnova_search',
            'formnova_search_nonce'
        );

        $table->search_box(
            __('Search Forms', 'formnova-form-builder'),
            'form-search'
        );
        ?>

    </form>

    <!-- List + Bulk Action -->
    <form method="post">

        <?php
        $table->display();
        ?>

    </form>
</div>