class FormNovaProperties {

    constructor(state) {

        this.state = state;
        this.el = document.getElementById('formnova-properties');

        document.addEventListener(
            'formnova:field:selected',
            (e) => this.render(e.detail)
        );
    }

    init() { }

    render(field) {

        if (!this.el) {
            return;
        }

        if (!field) {

            this.el.innerHTML = '<p>Select a field</p>';
            return;
        }

        const registry =
            window.FormNovaFieldRegistry.get(field.type);

        const rawSchema = registry?.settings || {};

        const schema = Array.isArray(rawSchema)
            ? rawSchema
            : Object.keys(rawSchema).map(title => ({
                title: title,
                fields: rawSchema[title]
            }));

        let html = '<div class="fn-props">';

        schema.forEach(section => {

            html += this.renderSection(section, field);

        });

        html += '</div>';

        this.el.innerHTML = html;

        this.bind(field.id);

    }

    renderSection(section, field) {

        let body = '';

        (section.fields || []).forEach(setting => {

            body += this.renderField(setting, field);

        });

        return this.accordion(
            section.title || '',
            body
        );

    }

    renderField(setting, field) {

        const value = field[setting.key];

        switch (setting.type) {

            case 'text':
            case 'email':
            case 'phone':
            case 'url':
            case 'date':
            case 'filetypes':
            case 'mimetypes':

                return this.text(
                    setting.label,
                    setting.key,
                    value
                );

            case 'textarea':

                return this.textarea(
                    setting.label,
                    setting.key,
                    value
                );

            case 'checkbox':

                return this.checkbox(
                    setting.label,
                    setting.key,
                    value
                );

            case 'number':

                return this.number(
                    setting.label,
                    setting.key,
                    value
                );

            case 'select':

                return this.select(
                    setting,
                    value
                );

            case 'radio':

                return this.select(
                    setting,
                    value
                );

            case 'options':

                return this.optionsEditor(
                    setting,
                    field
                );

            case 'file':

                return this.file(
                    setting,
                    field
                );

            default:

                return '';

        }

    }

    number(label, key, value = '') {

        return `
            <p>

                <label>${label}</label>

                <input
                    type="number"
                    data-key="${key}"
                    value="${value ?? ''}"
                >

            </p>
            `;

    }

    select(setting, value = '') {

        let html = `

            <p>

                <label>${setting.label}</label>

                <select data-key="${setting.key}">

            `;

        (setting.options || []).forEach(option => {

            const optionValue =
                option.value ?? option;

            const optionLabel =
                option.label ?? option;

            html += `

                    <option
                        value="${optionValue}"
                        ${optionValue == value ? 'selected' : ''}
                    >

                        ${optionLabel}

                    </option>

                `;

        });

        html += `

                </select>

            </p>

            `;

        return html;

    }

    optionsEditor(setting, field) {

        const values = Array.isArray(field[setting.key])
            ? field[setting.key]
            : [];

        let html = `
        <div class="fn-options-wrapper">
    `;

        values.forEach((option, index) => {

            html += `
                <div class="fn-option-row">

                    <input
                        type="text"
                        class="fn-option-label"
                        data-index="${index}"
                        data-key="${setting.key}"
                        placeholder="Label"
                        value="${option.label || ''}"
                    >

                    <input
                        type="text"
                        class="fn-option-value"
                        data-index="${index}"
                        data-key="${setting.key}"
                        placeholder="Value"
                        value="${option.value || ''}"
                    >

                    <button
                        type="button"
                        class="button fn-delete-option"
                        data-index="${index}"
                        data-key="${setting.key}"
                    >
                        Delete
                    </button>

                </div>
                `;
        });

        html += `
        <button
            type="button"
            class="button fn-add-option"
            data-key="${setting.key}"
        >
            Add Option
        </button>

        </div>
    `;

        return html;
    }

    file(setting, field) {

        return `

            <p>

                <label>${setting.label}</label>

                <input
                    type="text"
                    data-key="${setting.key}"
                    value="${field[setting.key] || ''}"
                >

            </p>

        `;

    }

    accordion(title, body) {

        return `
        <div class="fn-accordion">

            <div class="fn-head">
                ${title}
            </div>

            <div class="fn-body">
                ${body}
            </div>

        </div>
        `;
    }

    text(label, key, value = '') {

        return `
        <p>

            <label>${label}</label>

            <input
                type="text"
                data-key="${key}"
                value="${value || ''}"
            >

        </p>
        `;
    }

    textarea(label, key, value = '') {

        return `
        <p>

            <label>${label}</label>

            <textarea
                data-key="${key}"
            >${value || ''}</textarea>

        </p>
        `;
    }

    checkbox(label, key, value) {

        return `
        <p>

            <label>

                <input
                    type="checkbox"
                    data-key="${key}"
                    ${value ? 'checked' : ''}
                >

                ${label}

            </label>

        </p>
        `;
    }


    bind(id) {

        this.el.querySelectorAll('[data-key]').forEach(input => {

            // Skip option editor inputs
            if (
                input.classList.contains('fn-option-label') ||
                input.classList.contains('fn-option-value')
            ) {
                return;
            }

            const eventName =
                input.tagName === 'SELECT'
                    ? 'change'
                    : 'input';

            input.addEventListener(eventName, () => {

                const value =
                    input.type === 'checkbox'
                        ? input.checked
                        : input.value;

                this.state.updateField(id, {

                    [input.dataset.key]: value

                });

            });

        });

        this.el.querySelectorAll('.fn-add-option').forEach(button => {

            button.addEventListener('click', () => {

                const key = button.dataset.key;

                const field = this.state.selectedField;

                const values = Array.isArray(field[key])

                    ? [...field[key]]

                    : [];

                values.push({
                    label: 'New Option',
                    value: 'new_option'
                });

                this.state.updateField(id, {
                    [key]: values
                });

                this.render(this.state.selectedField);

            });

        });

        this.el.querySelectorAll('.fn-delete-option').forEach(button => {

            button.addEventListener('click', () => {

                const key = button.dataset.key;

                const index = parseInt(
                    button.dataset.index,
                    10
                );


                const field = this.state.selectedField;


                let values = Array.isArray(field[key])
                    ? [...field[key]]
                    : [];


                values.splice(index, 1);


                this.state.updateField(id, {

                    [key]: values

                });


                this.render(
                    this.state.selectedField
                );

            });

        });

        this.el.querySelectorAll('.fn-option-label').forEach(input => {

            input.addEventListener('input', () => {

                const field = this.state.selectedField;
                const index = parseInt(input.dataset.index, 10);
                const key = input.dataset.key;

                const values = Array.isArray(field[key])

                    ? [...field[key]]

                    : [];

                if (typeof values[index] !== 'object') {
                    values[index] = {
                        label: values[index] || '',
                        value: values[index] || ''
                    };
                }

                values[index].label = input.value;

                this.state.updateField(id, {
                    [key]: values
                });

            });

        });

        this.el.querySelectorAll('.fn-option-value').forEach(input => {

            input.addEventListener('input', () => {

                const field = this.state.selectedField;
                const index = parseInt(input.dataset.index, 10);
                const key = input.dataset.key;

                const values = Array.isArray(field[key])

                    ? [...field[key]]

                    : [];

                if (typeof values[index] !== 'object') {
                    values[index] = {
                        label: values[index] || '',
                        value: values[index] || ''
                    };
                }

                values[index].value = input.value;

                this.state.updateField(id, {
                    [key]: values
                });
            });

        });

        this.el.querySelectorAll('.fn-head').forEach(head => {

            head.onclick = () => {

                head.parentElement.classList.toggle('open');

            };

        });

    }

}