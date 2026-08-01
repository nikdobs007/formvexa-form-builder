class formvexaAdvanced {

    constructor(state) {

        this.state = state;

        this.el = document.getElementById('formvexa-advanced-panel');

    }

    init() {

        if (!this.el) {
            return;
        }

        this.render();

    }

    render() {

        const checked =
            this.state.settings.advanced.captcha_enabled
                ? 'checked'
                : '';

        this.el.innerHTML = `
            <p>
                <label>

                    <input
                        type="checkbox"
                        id="fn-captcha-enabled"
                        ${checked}
                    >

                    Enable Google reCAPTCHA

                </label>
            </p>
        `;

        document
            .getElementById('fn-captcha-enabled')
            .addEventListener('change', e => {

                this.state.updateSettings(
                    'advanced',
                    {
                        captcha_enabled: e.target.checked
                    }
                );

            });

    }

}