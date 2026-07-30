<?php

defined('ABSPATH') || exit;

?>
<div class="wrap">
    
    <div class="formnova-header">

        <h1 class="wp-heading-inline">
            <?php echo $form_id ? 'Edit Form' : 'Add New Form'; ?>
        </h1>
   
        <?php if ($form_id): ?>

            <a href="<?php echo esc_url(admin_url('admin.php?page=formnova-builder')); ?>" class="page-title-action">
                Add New
            </a>

        <?php endif; ?>

    </div>

    <hr class="wp-header-end">

    <div class="formnova-topbar">

        <h2>
            <input type="text" id="formnova-title" placeholder="Enter Form Title"
                value="<?php echo esc_attr($form->title ?? ''); ?>" />
        </h2>
        <?php
        $formnova_shortcode = $form_id
            ? sprintf('[formnova_form id="%d"]', (int) $form_id)
            : 'Save form to get shortcode';
        ?>
        <div class="formnova-shortcode-box">
            Shortcode:
            <input type="text" id="formnova-shortcode" readonly value="<?php echo esc_attr($formnova_shortcode); ?>" />

            <button type="button" class="button fn-add-option" onclick="FormNovaCopyShortcode()">Copy</button>
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

        <div id="formnova-builder" class="fn-builder-layout">

            <!-- LEFT PANEL -->
            <div id="formnova-fields" class="fn-builder-left">

                <?php foreach ($schemas as $type => $formnova_schema): ?>

                    <div class="formnova-draggable" draggable="true" data-type="<?php echo esc_attr($type); ?>">

                        <?php echo esc_html($formnova_schema['defaults']['label'] ?? ucfirst($type)); ?>

                    </div>

                <?php endforeach; ?>

            </div>

            <!-- CANVAS -->
            <div id="formnova-canvas" class="fn-builder-canvas">
            </div>

            <!-- RIGHT PANEL -->
            <div id="formnova-properties" class="fn-builder-right">
            </div>

        </div>

    </div>
    
    <div class="fn-tab-panel" data-panel="mail">

        <div id="formnova-mail-panel">

            Mail Settings

        </div>

    </div>

    <div class="fn-tab-panel" data-panel="advanced">

        <div id="formnova-advanced-panel">

            Advanced Settings

        </div>

    </div>

    <div class="fn-savebar">

        <button type="button" id="formnova-save" class="button button-primary">
            Save Form
        </button>

    </div>
</div>