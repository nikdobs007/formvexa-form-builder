<?php
/**
 * Field Service Provider.
 *
 * @package formvexa
 */

namespace formvexa\Providers;

defined('ABSPATH') || exit;

use formvexa\Fields\Registry;
use formvexa\Fields\Text\TextField;
use formvexa\Fields\Email\EmailField;
use formvexa\Fields\Phone\PhoneField;
use formvexa\Fields\Textarea\TextareaField;
use formvexa\Fields\Paragraph\ParagraphField;
use formvexa\Fields\Number\NumberField;
use formvexa\Fields\Date\DateField;
use formvexa\Fields\Url\UrlField;
use formvexa\Fields\File\FileField;
use formvexa\Fields\Select\SelectField;
use formvexa\Fields\Radio\RadioField;
use formvexa\Fields\Checkbox\CheckboxField;

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

        do_action('formvexa_register_fields');
    }
}