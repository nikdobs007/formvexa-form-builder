<?php
/**
 * Field Service Provider.
 *
 * @package FormNova
 */

namespace FormNova\Providers;

defined('ABSPATH') || exit;

use FormNova\Fields\Registry;
use FormNova\Fields\Text\TextField;
use FormNova\Fields\Email\EmailField;
use FormNova\Fields\Phone\PhoneField;
use FormNova\Fields\Textarea\TextareaField;
use FormNova\Fields\Paragraph\ParagraphField;
use FormNova\Fields\Number\NumberField;
use FormNova\Fields\Date\DateField;
use FormNova\Fields\Url\UrlField;
use FormNova\Fields\File\FileField;
use FormNova\Fields\Select\SelectField;
use FormNova\Fields\Radio\RadioField;
use FormNova\Fields\Checkbox\CheckboxField;

final class FieldServiceProvider
{
    /**
     * Boot provider.
     *
     * @return void
     */
    public function boot(): void
    {
        Registry::clear();

        /*
        |--------------------------------------------------------------------------
        | Basic Fields
        |--------------------------------------------------------------------------
        */

        Registry::register(new TextField());
        Registry::register(new EmailField());
        Registry::register(new PhoneField());
        Registry::register(new TextareaField());
        Registry::register(new ParagraphField());

        /*
        |--------------------------------------------------------------------------
        | Advanced Fields
        |--------------------------------------------------------------------------
        */

        Registry::register(new NumberField());
        Registry::register(new DateField());
        Registry::register(new UrlField());
        Registry::register(new FileField());

        /*
        |--------------------------------------------------------------------------
        | Choice Fields
        |--------------------------------------------------------------------------
        */

        Registry::register(new SelectField());
        Registry::register(new RadioField());
        Registry::register(new CheckboxField());

        /*
        |--------------------------------------------------------------------------
        | Third Party Fields
        |--------------------------------------------------------------------------
        */

        do_action('formnova_register_fields');
    }
}