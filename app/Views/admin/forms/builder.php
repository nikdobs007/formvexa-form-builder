<?php

defined('ABSPATH') || exit;

?>
<div class="wrap">
    
    <div class="formvexa-header">

        <h1 class="wp-heading-inline">
            <?php echo $form_id ? 'Edit Form' : 'Add New Form'; ?>
        </h1>
   
        <?php if ($form_id): ?>

            <a href="<?php echo esc_url(admin_url('admin.php?page=formvexa-builder')); ?>" class="page-title-action">
                Add New
            </a>

        <?php endif; ?>

    </div>

    <hr class="wp-header-end">

    <div class="formvexa-topbar">

        <h2>
            <input type="text" id="formvexa-title" placeholder="Enter Form Title"
                value="<?php echo esc_attr($form->title ?? ''); ?>" />
        </h2>
        <?php
        $formvexa_shortcode = $form_id
            ? sprintf('[formvexa_form id="%d"]', (int) $form_id)
            : 'Save form to get shortcode';
        ?>
        <div class="formvexa-shortcode-box">
            Shortcode:
            <input type="text" id="formvexa-shortcode" readonly value="<?php echo esc_attr($formvexa_shortcode); ?>" />

            <button type="button" class="button fn-add-option" onclick="formvexaCopyShortcode()">Copy</button>
        </div>

    </div>

    <div class="fn-builder-tabs">

        <button type="button" class="button fn-builder-tab active" data-tab="builder">

            Builder

        </button>

        <button type="button" class="button fn-builder-tab" data-tab="mail">

            Mail

        </button>

        <button type="button" class="button fn-builder-tab" data-tab="advanced">

            Advanced

        </button>

    </div>

    <div class="fn-tab-panel active" data-panel="builder">

        <div id="formvexa-builder" class="fn-builder-layout">

            <!-- LEFT PANEL -->
            <div id="formvexa-fields" class="fn-builder-left">

                <?php foreach ($schemas as $type => $formvexa_schema): ?>

                    <div class="formvexa-draggable" draggable="true" data-type="<?php echo esc_attr($type); ?>">

                        <?php echo esc_html($formvexa_schema['defaults']['label'] ?? ucfirst($type)); ?>

                    </div>

                <?php endforeach; ?>

            </div>

            <!-- CANVAS -->
            <div id="formvexa-canvas" class="fn-builder-canvas">
            </div>

            <!-- RIGHT PANEL -->
            <div id="formvexa-properties" class="fn-builder-right">
            </div>

        </div>

    </div>
    
    <div class="fn-tab-panel" data-panel="mail">

        <div id="formvexa-mail-panel">

            Mail Settings

        </div>

    </div>

    <div class="fn-tab-panel" data-panel="advanced">

        <div id="formvexa-advanced-panel">

            Advanced Settings

        </div>

    </div>

    <div class="fn-savebar">

        <button type="button" id="formvexa-save" class="button button-primary">
            Save Form
        </button>

    </div>
</div>