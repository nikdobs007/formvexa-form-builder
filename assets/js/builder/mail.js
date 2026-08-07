class formvexaMail {

    constructor(state) {

        this.state = state;

        this.el = document.getElementById(
            'formvexa-mail-panel'
        );

        this.activeTextarea = null;
        document.addEventListener(

            'formvexa:state:updated',

            (event) => {

                if (
                    event.detail &&
                    event.detail.fields
                ) {

                    this.renderTags();

                    this.renderToolbar(
                        'fn-admin-toolbar'
                    );

                    this.renderToolbar(
                        'fn-user-toolbar'
                    );

                    this.renderEmailFields();

                }

            }

        );

    }

    init() {

        if (!this.el) {
            return;
        }

        this.render();

    }

    render() {

        this.el.innerHTML = `

        <div class="fn-mail-wrap">

            <div class="fn-card">

                <div class="fn-card-header">

                    Admin Email

                </div>

                <div class="fn-card-body">

                    <p>

                        <label>To</label>

                        <div id="fn-admin-to-list">

                        </div>

                        <button
                            type="button"
                            class="button"
                            id="fn-add-admin-to">

                            Add Email

                        </button>

                    </p>

                    <p>

                        <label>

                            <input
                                type="checkbox"
                                id="fn-admin-html">

                            Send HTML Email

                        </label>

                    </p>
                    <p>

                        <label>

                            Subject

                        </label>

                        <input
                            type="text"
                            id="fn-admin-subject">

                    </p>

                    <p>

                       <label>Message</label>

                        <div
                            class="fn-mail-toolbar"
                            id="fn-admin-toolbar">
                        </div>

                        <textarea
                            rows="10"
                            id="fn-admin-message">
                        </textarea>

                    </p>

                </div>

            </div>

            <div class="fn-card">

                <div class="fn-card-header">

                    User Email

                </div>

                <div class="fn-card-body">

                    <p>

                        <label>

                            <input
                                type="checkbox"
                                id="fn-user-enable">

                            Send User Email

                        </label>

                    </p>

                    <p>

                        <label>

                            Email Field

                        </label>

                        <select
                            id="fn-user-email-field">

                        </select>

                    </p>

                    <p>

                        <label>

                            Subject

                        </label>

                        <input
                            type="text"
                            id="fn-user-subject">

                    </p>

                    <p>

                       <label>Message</label>

                        <div
                            class="fn-mail-toolbar"
                            id="fn-user-toolbar">
                        </div>

                        <textarea
                            rows="8"
                            id="fn-user-message">
                        </textarea>

                    </p>

                    <p>

                        <label>

                            <input
                                type="checkbox"
                                id="fn-html-email">

                            Send HTML Email

                        </label>

                    </p>

                    <p>

                        <label>

                            <input
                                type="checkbox"
                                id="fn-attach-files"
                                checked>

                            Attach Uploaded Files

                        </label>

                    </p>

                </div>

            </div>

            <div class="fn-card">

                <div class="fn-card-header">

                    Available Merge Tags

                </div>

                <div
                    class="fn-card-body"
                    id="fn-mail-tags">

                </div>

            </div>

        </div>

        `;

        this.loadState();

        this.bind();

    }

    loadState() {

        const mail = this.state.getSettings().mail;

        document.getElementById(
            'fn-admin-subject'
        ).value = mail.admin_subject;

        document.getElementById(
            'fn-admin-message'
        ).value = mail.admin_message;

        document.getElementById(
            'fn-user-enable'
        ).checked = mail.user_enabled;

        document.getElementById(
            'fn-user-subject'
        ).value = mail.user_subject;

        document.getElementById(
            'fn-user-message'
        ).value = mail.user_message;

        document.getElementById(
            'fn-user-email-field'
        ).value = mail.user_email_field;

        document.getElementById(
            'fn-admin-html'
        ).checked =
            mail.html || false;
        
        // Admin To
        (mail.admin_to || []).forEach(email => {

            this.addAdminInput(
                'fn-admin-to-list',
                email
            );

        });

        document.getElementById(
            'fn-html-email'
        ).checked = mail.is_html;

        document.getElementById(
            'fn-attach-files'
        ).checked = mail.attach_files;

        this.renderEmailFields();
    }

    bind() {

        document
            .getElementById(
                'fn-add-admin-to'
            )
            .onclick = () => {

                this.addAdminInput(
                    'fn-admin-to-list'
                );

            };

        this.renderTags();

        this.bindInputs();

        this.bindTextareas();

        this.renderEmailFields();

        this.renderToolbar(
            'fn-admin-toolbar'
        );

        this.renderToolbar(
            'fn-user-toolbar'
        );

        document.getElementById(
            'fn-user-email-field'
        ).dispatchEvent(
            new Event('change')
        );

    }

    bindInputs() {

        document
            .getElementById(
                'fn-admin-subject'
            )
            .addEventListener(

                'input',

                e => {

                    this.state.updateSettings(

                        'mail',

                        {

                            admin_subject: e.target.value

                        }

                    );

                }

            );

        document
            .getElementById(
                'fn-admin-message'
            )
            .addEventListener(

                'input',

                e => {

                    this.state.updateSettings(

                        'mail',

                        {

                            admin_message: e.target.value

                        }

                    );

                }

            );

        document
            .getElementById(
                'fn-user-enable'
            )
            .addEventListener(

                'change',

                e => {

                    this.state.updateSettings(

                        'mail',

                        {

                            user_enabled: e.target.checked

                        }

                    );

                }

            );

        document
            .getElementById(
                'fn-user-subject'
            )
            .addEventListener(

                'input',

                e => {

                    this.state.updateSettings(

                        'mail',

                        {

                            user_subject: e.target.value

                        }

                    );

                }

            );

        document
            .getElementById(
                'fn-user-message'
            )
            .addEventListener(

                'input',

                e => {

                    this.state.updateSettings(

                        'mail',

                        {

                            user_message: e.target.value

                        }

                    );

                }

            );

        document
            .getElementById(
                'fn-user-email-field'
            )
            .addEventListener(

                'change',

                e => {

                    this.state.updateSettings(

                        'mail',

                        {

                            user_email_field:

                                e.target.value

                        }

                    );

                }

            );

        document
            .getElementById(
                'fn-admin-html'
            )
            .addEventListener(

                'change',

                e => {

                    this.state.updateSettings(

                        'mail',

                        {

                            html: e.target.checked

                        }

                    );

                }

            );

        document
            .getElementById(
                'fn-html-email'
            )
            .addEventListener(
                'change',
                e => {

                    this.state.updateSettings(
                        'mail',
                        {
                            is_html: e.target.checked
                        }
                    );

                }
            );

        document
            .getElementById(
                'fn-attach-files'
            )
            .addEventListener(
                'change',
                e => {

                    this.state.updateSettings(
                        'mail',
                        {
                            attach_files: e.target.checked
                        }
                    );

                }
            );

    }

    bindTextareas() {

        [
            'fn-admin-message',
            'fn-user-message'
        ].forEach(id => {

            const el =
                document.getElementById(id);

            if (!el) {
                return;
            }

            const updateActive = () => {

                this.activeTextarea = el;

            };

            el.addEventListener(
                'focus',
                updateActive
            );

            el.addEventListener(
                'click',
                updateActive
            );

            el.addEventListener(
                'keyup',
                updateActive
            );

            el.addEventListener(
                'mouseup',
                updateActive
            );

        });

    }

    renderEmailFields() {

        const select = document.getElementById(
            'fn-user-email-field'
        );

        if (!select) {
            return;
        }

        // Current mail settings
        const mail = this.state.settings?.mail || {};

        let html =
            '<option value="">Select Email Field</option>';

        this.state.fields.forEach(field => {

            // Only email fields
            if (field.type !== 'email') {
                return;
            }

            const value =
                field.name ||
                field.slug ||
                '';

            html += `
            <option
                value="${value}"
                ${value === mail.user_email_field ? 'selected' : ''}
            >
                ${field.label}
            </option>
        `;

        });

        select.innerHTML = html;

        // NEW CODE (Phase 2.8 Step 5)
        if (
            mail.user_email_field &&
            select.querySelector(
                `option[value="${mail.user_email_field}"]`
            )
        ) {
            select.value = mail.user_email_field;
        }

    }

    addAdminInput(
        id,
        value = ''
    ) {

        const row =
            document.createElement('p');

        row.innerHTML = `

            <input
                type="email"
                value="${value}"
                class="regular-text fn-mail-email">

        `;

        document
            .getElementById(id)
            .appendChild(row);

        // ↓↓↓ Ye code yahin add karna hai ↓↓↓

        row.querySelector('input')
            .addEventListener(
                'input',
                () => {

                    let key = 'admin_to';

                    switch (id) {

                        case 'fn-admin-to-list':

                            key = 'admin_to';

                            break;

                    }

                    const values = [];

                    document
                        .querySelectorAll(
                            '#' + id + ' .fn-mail-email'
                        )
                        .forEach(input => {

                            if (input.value.trim()) {

                                values.push(
                                    input.value.trim()
                                );

                            }

                        });

                    this.state.updateSettings(
                        'mail',
                        {
                            [key]: values
                        }
                    );

                }
            );

    }


    renderTags() {

        const tagBox =

            document.getElementById(

                'fn-mail-tags'

            );

        if (!tagBox) {

            return;

        }

        let html =

            '<code data-tag="{all_fields}">{all_fields}</code>';

        this.state.fields.forEach(field => {

            const tag =

                field.name ||

                field.label;

            const attachment =

                field.type === 'file';

            html += `

                <code
                data-tag="{${tag}}">

                {${tag}}

                </code>

                ${attachment

                    ?

                    `<code
                data-tag="{attachment:${tag}}">

                {attachment:${tag}}

                </code>`

                    : ''

                }

                `;

        });

        tagBox.innerHTML = html;

        tagBox
            .querySelectorAll('code')
            .forEach(code => {

                code.onclick = () => {

                    this.insertMergeTag(

                        code.dataset.tag

                    );

                };

            });

    }

    renderToolbar(id) {

        const toolbar = document.getElementById(id);

        if (!toolbar) {
            return;
        }

        // HTML sabse pehle declare karo
        let html = '';

        const systemTags = [

            {
                label: 'Site Name',
                tag: '{site_name}'
            },
            {
                label: 'Form Title',
                tag: '{form_title}'
            },
            {
                label: 'Date',
                tag: '{date}'
            },
            {
                label: 'Time',
                tag: '{time}'
            },
            {
                label: 'IP',
                tag: '{ip}'
            }

        ];

        systemTags.forEach(item => {

            html += `
            <button
                type="button"
                class="button fn-tag-btn"
                data-tag="${item.tag}">
                ${item.label}
            </button>
        `;

        });

        html += `
        <button
            type="button"
            class="button fn-tag-btn"
            data-tag="{all_fields}">
            All Fields
        </button>
    `;

        this.state.fields.forEach(field => {

            const tag = field.name || field.slug || field.id;

            html += `
            <button
                type="button"
                class="button fn-tag-btn"
                data-tag="{${tag}}">
                ${field.label}
            </button>
        `;

        });

        toolbar.innerHTML = html;

        toolbar
            .querySelectorAll('.fn-tag-btn')
            .forEach(btn => {

                btn.addEventListener(

                    'mousedown',

                    (e) => {

                        e.preventDefault();

                    }

                );

                btn.addEventListener(

                    'click',

                    () => {

                        this.insertMergeTag(

                            btn.dataset.tag

                        );

                    }

                );

            });

    }

    insertMergeTag(tag) {

        /*
        |--------------------------------------------------------------------------
        | Always use currently focused textarea
        |--------------------------------------------------------------------------
        */

        if (
            document.activeElement &&
            document.activeElement.tagName === 'TEXTAREA'
        ) {

            this.activeTextarea =
                document.activeElement;

        }

        if (!this.activeTextarea) {

            return;

        }

        const textarea =
            this.activeTextarea;

        const start =
            textarea.selectionStart;

        const end =
            textarea.selectionEnd;

        const value =
            textarea.value;

        const newValue =
            value.substring(0, start)
            +
            tag
            +
            value.substring(end);

        textarea.value =
            newValue;

        textarea.focus();

        textarea.selectionStart =
            start + tag.length;

        textarea.selectionEnd =
            start + tag.length;

        textarea.dispatchEvent(

            new Event(
                'input',
                {
                    bubbles: true
                }
            )

        );

    }

}