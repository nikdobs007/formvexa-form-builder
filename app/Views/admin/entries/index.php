<?php

defined('ABSPATH') || exit;

?>

<div class="wrap">

    <h1 class="wp-heading-inline">
        Form Entries
    </h1>

    <hr class="wp-header-end">
    <?php if (!empty(filter_input(INPUT_GET, 'deleted', FILTER_SANITIZE_NUMBER_INT))): ?>

        <div class="notice notice-success is-dismissible">
            <p>
                <?php
                echo esc_html(
                    sprintf(
                        /* translators: %d: Number of deleted entries. */
                        _n(
                            '%d entry deleted successfully.',
                            '%d entries deleted successfully.',
                            $deleted,
                            'formnova-form-builder'
                        ),
                        $deleted
                    )
                );
                ?>
            </p>

        </div>

    <?php endif; ?>
    <!-- Filter   Form -->
    <form method="get">

        <input type="hidden" name="page" value="formnova-entries">

        <select name="form_id">

            <option value="0">All Forms</option>

            <?php foreach ($forms as $formnova_form): ?>

                <option value="<?php echo absint($formnova_form->id); ?>" <?php selected(
                       absint(
                           filter_input(
                               INPUT_GET,
                               'form_id',
                               FILTER_SANITIZE_NUMBER_INT
                           )
                       ),
                       $formnova_form->id
                   ); ?>>

                    <?php echo esc_html($formnova_form->title); ?>

                </option>

            <?php endforeach; ?>

        </select>

        <?php
        submit_button(
            __('Filter', 'formnova-form-builder'),
            'secondary',
            '',
            false
        );
        ?>

        <?php
        $formnova_form_id = absint(
            filter_input(
                INPUT_GET,
                'form_id',
                FILTER_SANITIZE_NUMBER_INT
            )
        );

        if ($formnova_form_id > 0):

            $formnova_export_url = wp_nonce_url(
                add_query_arg(
                    [
                        'action' => 'formnova_export_csv',
                        'form_id' => $formnova_form_id,
                    ],
                    admin_url('admin-post.php')
                ),
                'formnova_export_csv_' . $formnova_form_id
            );

            ?>

            <a href="<?php echo esc_url($formnova_export_url); ?>" class="button button-primary">

                <?php
                esc_html_e(
                    'Export CSV',
                    'formnova-form-builder'
                );
                ?>

            </a>

        <?php endif; ?>

    </form>

    <br>

    <!-- List Table -->
    <form method="post">

        <?php wp_nonce_field('bulk-entries'); ?>

        <input type="hidden" name="page" value="formnova-entries">

        <input type="hidden" name="form_id"
            value="<?php echo absint(filter_input(INPUT_GET, 'form_id', FILTER_SANITIZE_NUMBER_INT)); ?>">

        <?php

        $table->search_box(
            __('Search Entries', 'formnova-form-builder'),
            'entry'
        );

        $table->display();

        ?>

    </form>

</div>