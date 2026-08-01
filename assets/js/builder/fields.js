const COMMON_SETTINGS = [

    {

        title: 'General',

        fields: [

            {
                key: 'label',
                label: 'Label',
                type: 'text'
            },

            {
                key: 'placeholder',
                label: 'Placeholder',
                type: 'text'
            },

            {
                key: 'css_class',
                label: 'CSS Class',
                type: 'text'
            },

            {
                key: 'required',
                label: 'Required',
                type: 'checkbox'
            }

        ]

    }

];

window.formvexaFieldRegistry.register('text', {

    defaults: {
        label: 'Text',
        placeholder: '',
        css_class: '',
        required: false
    },

    settings: COMMON_SETTINGS,

    render(field) {

        return `<input
        type="text"
        placeholder="${field.placeholder || field.label}"
        disabled>`;

    }

});

window.formvexaFieldRegistry.register('email', {

    defaults: {
        label: 'Email',
        placeholder: '',
        css_class: '',
        required: false
    },

    settings: COMMON_SETTINGS,

    render(field) {

        return `<input
        type="email"
        placeholder="${field.placeholder || field.label}"
        disabled>`;

    }

});

window.formvexaFieldRegistry.register('phone', {

    defaults: {
        label: 'Phone',
        placeholder: '',
        css_class: '',
        required: false
    },

    settings: COMMON_SETTINGS,

    render(field) {

        return `<input
type="tel"
placeholder="${field.placeholder || field.label}"
disabled>`;

    }

});

window.formvexaFieldRegistry.register('url', {

    defaults: {
        label: 'Website',
        placeholder: '',
        css_class: '',
        required: false
    },

    settings: COMMON_SETTINGS,

    render(field) {

        return `<input
type="url"
placeholder="${field.placeholder || field.label}"
disabled>`;

    }

});

window.formvexaFieldRegistry.register('date', {

    defaults: {
        label: 'Date',
        css_class: '',
        required: false
    },

    settings: [

        {

            title: 'General',

            fields: [

                {
                    key: 'label',
                    label: 'Label',
                    type: 'text'
                },

                {
                    key: 'css_class',
                    label: 'CSS Class',
                    type: 'text'
                },

                {
                    key: 'required',
                    label: 'Required',
                    type: 'checkbox'
                }

            ]

        }

    ],

    render() {

        return `<input type="date" disabled>`;

    }

});

window.formvexaFieldRegistry.register('textarea', {

    defaults: {
        label: 'Textarea',
        placeholder: '',
        css_class: '',
        required: false
    },

    settings: COMMON_SETTINGS,

    render(field) {

        return `<textarea
placeholder="${field.placeholder || field.label}"
disabled></textarea>`;

    }

});

window.formvexaFieldRegistry.register('number', {

    defaults: {
        label: 'Number',
        placeholder: '',
        css_class: '',
        required: false
    },

    settings: COMMON_SETTINGS,

    render(field) {

        return `<input
            type="number"
            placeholder="${field.placeholder || field.label}"
            disabled>`;

    }

});

window.formvexaFieldRegistry.register('paragraph', {

    defaults: {
        label: 'Text',
        content: 'Sample text'
    },

    settings: [

        {
            title: 'General',

            fields: [

                {
                    key: 'content',
                    label: 'Content',
                    type: 'textarea'
                }

            ]
        }

    ],

    render(field) {

        return `<p>${field.content || 'Sample text'}</p>`;

    }

});

window.formvexaFieldRegistry.register('select', {

    defaults: {
        label: 'Select',
        css_class: '',
        required: false,

        options: [
            {
                label: 'Option 1',
                value: 'option_1'
            },
            {
                label: 'Option 2',
                value: 'option_2'
            }
        ]

    },

    settings: [

        {

            title: 'General',

            fields: [

                {
                    key: 'label',
                    label: 'Label',
                    type: 'text'
                },

                {
                    key: 'css_class',
                    label: 'CSS Class',
                    type: 'text'
                },

                {
                    key: 'required',
                    label: 'Required',
                    type: 'checkbox'
                },

                {
                    key: 'options',
                    label: 'Options',
                    type: 'options'
                }

            ]

        }

    ],

    render(field) {

        const options = Array.isArray(field.options)
            ? field.options
            : [];

        return `
        <select disabled>
        ${options.map(option => `
        <option value="${option.value}">
        ${option.label}
        </option>
        `).join('')}
        </select>
        `;

    }

});


window.formvexaFieldRegistry.register('radio', {

    defaults: {

        label: 'Radio',
        css_class: '',
        required: false,

        options: [
            {
                label: 'Option 1',
                value: 'option_1'
            },
            {
                label: 'Option 2',
                value: 'option_2'
            }
        ]

    },

    settings: [

        {

            title: 'General',

            fields: [

                {
                    key: 'label',
                    label: 'Label',
                    type: 'text'
                },

                {
                    key: 'css_class',
                    label: 'CSS Class',
                    type: 'text'
                },

                {
                    key: 'required',
                    label: 'Required',
                    type: 'checkbox'
                },

                {
                    key: 'options',
                    label: 'Options',
                    type: 'options'
                }

            ]

        }

    ],

    render(field) {

        const options = Array.isArray(field.options)
            ? field.options
            : [];

        return options
            .map(option => `
        <label class="fn-radio-option">
            <input type="radio" disabled>
            ${option.label}
        </label>
    `)
            .join('');

    }

});

window.formvexaFieldRegistry.register('checkbox', {

    defaults: {

        label: 'Checkbox',
        css_class: '',
        required: false

    },

    settings: [

        {

            title: 'General',

            fields: [

                {
                    key: 'label',
                    label: 'Label',
                    type: 'text'
                },

                {
                    key: 'css_class',
                    label: 'CSS Class',
                    type: 'text'
                },

                {
                    key: 'required',
                    label: 'Required',
                    type: 'checkbox'
                }

            ]

        }

    ],

    render(field) {

        return `

            <label class="fn-checkbox-option">

                <input
                    type="checkbox"
                    disabled
                >

                ${field.label}

            </label>

        `;

    }

});

window.formvexaFieldRegistry.register('file', {

    defaults: {

        label: 'File Upload',
        css_class: '',
        required: false,
        filetypes: '',
        mimetypes: ''

    },

    settings: [

        {

            title: 'General',

            fields: [

                {
                    key: 'label',
                    label: 'Label',
                    type: 'text'
                },

                {
                    key: 'css_class',
                    label: 'CSS Class',
                    type: 'text'
                },

                {
                    key: 'required',
                    label: 'Required',
                    type: 'checkbox'
                },

                {
                    key: 'filetypes',
                    label: 'Allowed Extensions',
                    type: 'text'
                },

                {
                    key: 'mimetypes',
                    label: 'Allowed MIME Types',
                    type: 'text'
                }

            ]

        }

    ],

    render() {

        return `

            <input
                type="file"
                disabled
            >

        `;

    }

});