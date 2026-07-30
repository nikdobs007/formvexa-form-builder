class FormNovaBuilder {

    constructor() {

        this.state = new FormNovaState();
        this.canvas = new FormNovaCanvas(this.state);
        this.dragdrop = new FormNovaDragDrop(this.state, this.canvas);
        this.properties = new FormNovaProperties(this.state);
        this.tabs = null;
        this.mail =
            new FormNovaMail(
                this.state
            );

        this.advanced =
            new FormNovaAdvanced(
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
        this.tabs = new FormNovaTabs();
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
            window.FormNovaBuilderData?.schemas || {};

        Object.entries(schemas).forEach(
            ([type, schema]) => {

                const registry =
                    window.FormNovaFieldRegistry.get(type);

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
            document.getElementById('formnova-title')?.value || 'Untitled';

        const payload = new URLSearchParams();

        const builderState = {
            builder: this.state.getState(),
            settings: this.state.getSettings()
        };

        payload.append('action', 'ndfb_save_form');
        payload.append('nonce', window.FormNova.nonce);
        payload.append('form_id', this.state.formId || 0);
        payload.append('title', title);
        payload.append(
            'builder',
            JSON.stringify(builderState)
        );

        fetch(window.FormNova.ajax_url, {
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

                const isNew = !this.state.formId;

                this.state.formId = res.data.form_id;

                const shortcode =
                    document.getElementById('formnova-shortcode');

                if (shortcode) {
                    shortcode.value =
                        `[formnova_form id="${res.data.form_id}"]`;
                }

                if (isNew && res.data.redirect) {

                    document
                        .getElementById('formnova-save')
                        ?.classList.remove('fn-unsaved');

                    window.location.replace(res.data.redirect);

                    return;
                }

                document
                    .getElementById('formnova-save')
                    ?.classList.remove('fn-unsaved');

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
                'formnova-save'
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
    new FormNovaBuilder();
});

window.FormNovaCopyShortcode = function () {

    const input = document.getElementById('formnova-shortcode');

    if (!input) return;

    input.select();
    document.execCommand('copy');

    alert('Shortcode copied!');
};