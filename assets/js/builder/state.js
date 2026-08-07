class formvexaState {

    constructor() {

        this.fields = [];

        this.settings = {

            mail: {

                admin_to: [''],

                reply_to: '',

                attach_files: true,

                admin_subject: '',

                admin_message: '',

                user_enabled: false,

                user_email_field: '',

                user_subject: '',

                user_message: ''

            },

            advanced: {
                captcha_enabled: false
            }

        };

        this.selectedField = null;

        this.formId = window.formvexaBuilderData?.form_id || 0;

        this.loadExisting();
    }

    updateSettings(section, values) {

        if (!this.settings[section]) {

            this.settings[section] = {};

        }

        this.settings[section] = {

            ...this.settings[section],

            ...values

        };

        this.sync();

    }

    getSettings() {

        return this.settings;

    }

    getStateData() {

        return {

            fields: this.fields,

            settings: this.settings

        };

    }

    /**
     * Load saved builder
     */
    loadExisting() {

        const builder =
            window.formvexaBuilderData?.builder || [];

        if (Array.isArray(builder)) {

            this.fields = [...builder];

        } else {

            this.fields = [];

        }

        /*
        |--------------------------------------------------------------------------
        | Load Saved Mail Settings
        |--------------------------------------------------------------------------
        */

        const settings =
            window.formvexaBuilderData?.settings || {};

        if (settings.mail) {

            this.settings.mail = {

                ...this.settings.mail,

                ...settings.mail

            };

        }

        /*
        |--------------------------------------------------------------------------
        | Load Advanced Settings
        |--------------------------------------------------------------------------
        */

        if (settings.advanced) {

            this.settings.advanced = {

                ...this.settings.advanced,

                ...settings.advanced

            };

        }

        this.sync();

    }

    /**
     * Get builder state
     */
    getState() {
        return this.fields;
    }

    /**
     * Add field at end
     */
    addField(field) {

        this.fields.push(field);

        this.sync();
    }

    /**
     * Insert field at position
     */
    insertField(index, field) {

        if (index < 0) {
            index = 0;
        }

        if (index > this.fields.length) {
            index = this.fields.length;
        }

        this.fields.splice(index, 0, field);

        this.sync();
    }

    /**
     * Move field
     */
    moveField(fromIndex, toIndex) {

        if (
            fromIndex === toIndex ||
            fromIndex < 0 ||
            toIndex < 0 ||
            fromIndex >= this.fields.length ||
            toIndex > this.fields.length
        ) {
            return;
        }

        const field = this.fields.splice(fromIndex, 1)[0];

        if (fromIndex < toIndex) {
            toIndex--;
        }

        this.fields.splice(toIndex, 0, field);

        this.sync();
    }

    /**
     * Delete field
     */
    removeField(id) {

        this.fields = this.fields.filter(field => field.id !== id);

        if (this.selectedField && this.selectedField.id === id) {
            this.selectedField = null;
        }

        this.sync();
    }

    /**
     * Update field
     */
    updateField(id, data) {

        this.fields = this.fields.map(field => {

            if (field.id === id) {
                return {
                    ...field,
                    ...data
                };
            }

            return field;
        });

        if (this.selectedField?.id === id) {

            this.selectedField = this.fields.find(
                f => f.id === id
            );

        }

        this.sync();
    }

    /**
     * Select field
     */
    selectField(id) {

        this.selectedField = this.fields.find(
            field => field.id === id
        ) || null;

        document.dispatchEvent(
            new CustomEvent(
                'formvexa:field:selected',
                {
                    detail: this.selectedField
                }
            )
        );
    }

    /**
     * Get field index
     */
    getFieldIndex(id) {

        return this.fields.findIndex(
            field => field.id === id
        );
    }

    /**
     * Get field
     */
    getField(id) {

        return this.fields.find(
            field => field.id === id
        ) || null;
    }

    /**
     * Fire update event
     */
    sync() {

        document.dispatchEvent(

            new CustomEvent(

                'formvexa:state:updated',

                {

                    detail: {

                        fields: this.fields,

                        settings: this.settings

                    }

                }

            )

        );

        const saveButton =
            document.getElementById(
                'formvexa-save'
            );

        if (saveButton) {

            saveButton.classList.add(
                'fn-unsaved'
            );

        }

    }
}