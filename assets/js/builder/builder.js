class formvexaBuilder {

    constructor() {

        this.state = new formvexaState();
        this.canvas = new formvexaCanvas(this.state);
        this.dragdrop = new formvexaDragDrop(this.state, this.canvas);
        this.properties = new formvexaProperties(this.state);
        this.tabs = null;
        this.mail =
            new formvexaMail(
                this.state
            );

        this.advanced =
            new formvexaAdvanced(
                this.state
            );
        this.init();
    }

    init() {

        // Register fields first
        this.registerFields();

        this.canvas.render();
        this.dragdrop.init();
        // this.properties.init();
        this.tabs = new formvexaTabs();
        this.tabs.init();
        if (this.mail) {

            this.mail.init();

        }

        if (this.advanced) {

            this.advanced.init();

        }
        this.initSaveButton();
    }

    registerFields() {

        const schemas =
            window.formvexaBuilderData?.schemas || {};

        Object.entries(schemas).forEach(
            ([type, schema]) => {

                const registry =
                    window.formvexaFieldRegistry.get(type);

                if (!registry) {
                    return;
                }

                registry.defaults =
                    schema.defaults || {};

                registry.settings =
                    schema.settings || [];

            }
        );

    }

    saveForm() {

        const title =
            document.getElementById('formvexa-title')?.value || 'Untitled';

        const payload = new URLSearchParams();

        const builderState = {
            builder: this.state.getState(),
            settings: this.state.getSettings()
        };

        payload.append('action', 'ndfb_save_form');
        payload.append('nonce', window.formvexa.nonce);
        payload.append('form_id', this.state.formId || 0);
        payload.append('title', title);
        payload.append(
            'builder',
            JSON.stringify(builderState)
        );

        fetch(window.formvexa.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: payload
        })
            .then(response => response.json())
            .then(res => {

                if (!res.success) {
                    alert(res.data?.message || 'Save failed');
                    return;
                }

                this.state.formId = Number(res.data.form_id);

                const shortcode =
                    document.getElementById('formvexa-shortcode');

                if (shortcode) {
                    shortcode.value =
                        `[formvexa_form id="${this.state.formId}"]`;
                }

                document
                    .getElementById('formvexa-save')
                    ?.classList.remove('fn-unsaved');

                if (res.data.redirect) {
                    window.location.href = res.data.redirect;
                    return;
                }

                window.location.reload();

            })
            .catch(error => {

                console.error('Form save error:', error);

                alert('Something went wrong while saving form.');

            });
    }

    initSaveButton() {

        const button =
            document.getElementById(
                'formvexa-save'
            );

        if (!button) {

            return;

        }

        button.addEventListener(
            'click',
            (e) => {

                e.preventDefault();
                e.stopPropagation();

                button.disabled = true;

                button.textContent =
                    'Saving...';

                this.saveForm();

                setTimeout(() => {

                    button.disabled = false;

                    button.textContent =
                        'Save Form';

                }, 1000);

            }

        );
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new formvexaBuilder();
});

window.formvexaCopyShortcode = function () {

    const input = document.getElementById('formvexa-shortcode');

    if (!input) return;

    input.select();
    document.execCommand('copy');

    alert('Shortcode copied!');
};