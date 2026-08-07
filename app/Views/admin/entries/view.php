<?php

defined('ABSPATH') || exit;

?>

<div class="wrap">

    <h1 class="wp-heading-inline">
        Entry #<?php echo (int) $entry->id; ?>
    </h1>

    <p>

        <a href="<?php echo esc_url(admin_url('admin.php?page=formvexa-entries')); ?>" class="button">
            ← Back
        </a>

    </p>

    <table class="widefat striped">

        <tbody>

            <tr>
                <th>Form Name</th>
                <td><?php echo esc_html($form_name); ?></td>
            </tr>

            <tr>
                <th>Submitted</th>
                <td><?php echo esc_html($entry->submitted_at); ?></td>
            </tr>

        </tbody>

    </table>

    <br><br>

    <h2>Submitted Entry Details</h2>

    <table class="widefat striped">

        <thead>

            <tr>

                <th width="250">Field</th>

                <th>Value</th>

            </tr>

        </thead>

        <tbody>

            <?php if (!empty($entry_data)): ?>

                <?php foreach ($entry_data as $formvexa_field_key  => $formvexa_value): ?>

                    <tr>

                        <td width="250">
                            <strong>
                                <?php
                                echo esc_html(
                                    $fields[$formvexa_field_key ] ?? $formvexa_field_key 
                                );
                                ?>
                            </strong>
                        </td>

                        <td>

                            <?php

                            if (is_array($formvexa_value)) {

                                echo esc_html(
                                    implode(', ', $formvexa_value)
                                );

                            } elseif (
                                is_string($formvexa_value)
                                && filter_var($formvexa_value, FILTER_VALIDATE_URL)
                            ) {

                                $formvexa_ext = strtolower(
                                    pathinfo($formvexa_value, PATHINFO_EXTENSION)
                                );

                                if (
                                    in_array(
                                        $formvexa_ext,
                                        ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                                        true
                                    )
                                ) {

                                    echo '<img src="' . esc_url($formvexa_value) . '" style="max-width:200px;height:auto;border:1px solid #ddd;"><br><br>';

                                }

                                echo '<a href="' . esc_url($formvexa_value) . '" target="_blank">'
                                    . esc_html(basename($formvexa_value))
                                    . '</a>';

                            } else {

                                echo nl2br(
                                    esc_html((string) $formvexa_value)
                                );

                            }

                            ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php else: ?>

                <tr>
                    <td colspan="2">
                        No submitted fields found.
                    </td>
                </tr>

            <?php endif; ?>

        </tbody>

    </table>

</div>