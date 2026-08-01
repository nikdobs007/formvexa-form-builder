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
                            'formvexa-form-builder'
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

        <input type="hidden" name="page" value="formvexa-entries">

        <select name="form_id">

            <option value="0">All Forms</option>

            <?php foreach ($forms as $formvexa_form): ?>

                <option value="<?php echo absint($formvexa_form->id); ?>" <?php selected(
                       absint(
                           filter_input(
                               INPUT_GET,
                               'form_id',
                               FILTER_SANITIZE_NUMBER_INT
                           )
                       ),
                       $formvexa_form->id
                   ); ?>>

                    <?php echo esc_html($formvexa_form->title); ?>

                </option>

            <?php endforeach; ?>

        </select>

        <?php
        submit_button(
            __('Filter', 'formvexa-form-builder'),
            'secondary',
            '',
            false
        );
        ?>

        <?php
        $formvexa_form_id = absint(
            filter_input(
                INPUT_GET,
                'form_id',
                FILTER_SANITIZE_NUMBER_INT
            )
        );

        if ($formvexa_form_id > 0):

            $formvexa_export_url = wp_nonce_url(
                add_query_arg(
                    [
                        'action' => 'formvexa_export_csv',
                        'form_id' => $formvexa_form_id,
                    ],
                    admin_url('admin-post.php')
                ),
                'formvexa_export_csv_' . $formvexa_form_id
            );

            ?>

            <a href="<?php echo esc_url($formvexa_export_url); ?>" class="button button-primary">

                <?php
                esc_html_e(
                    'Export CSV',
                    'formvexa-form-builder'
                );
                ?>

            </a>

        <?php endif; ?>

    </form>

    <br>

    <!-- List Table -->
    <form method="post">

        <?php wp_nonce_field('bulk-entries'); ?>

        <input type="hidden" name="page" value="formvexa-entries">

        <input type="hidden" name="form_id"
            value="<?php echo absint(filter_input(INPUT_GET, 'form_id', FILTER_SANITIZE_NUMBER_INT)); ?>">

        <?php

        $table->search_box(
            __('Search Entries', 'formvexa-form-builder'),
            'entry'
        );

        $table->display();

        ?>

    </form>

</div>