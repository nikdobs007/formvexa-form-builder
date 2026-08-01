class formvexaCanvas {

    constructor(state) {

        this.state = state;

        this.el = document.getElementById('formvexa-canvas');

        document.addEventListener(
            'formvexa:state:updated',
            () => this.render()
        );
    }

    render() {

        if (!this.el) {
            return;
        }

        this.el.innerHTML = '';

        // First Drop Zone
        this.el.appendChild(this.createDropZone(0));

        this.state.fields.forEach((field, index) => {

            const registry = window.formvexaFieldRegistry.get(field.type);

            const wrapper = document.createElement('div');

            wrapper.className = 'formvexa-field';

            wrapper.dataset.id = field.id;

            wrapper.dataset.index = index;

            wrapper.innerHTML = `
                <div class="formvexa-field-header">

                    <span
                        class="fn-drag-handle"
                        draggable="true">
                        ☰
                    </span>

                    <button
                        type="button"
                        class="button fn-delete-field"
                        title="Delete Field">
                        ✕
                    </button>

                </div>

                <label>${field.label || ''}</label>
            `;

            wrapper.addEventListener(
                'dragover',
                (e) => {

                    e.stopPropagation();

                }
            );

            wrapper.addEventListener('click', () => {

                this.state.selectField(field.id);

            });

            const deleteButton =
                wrapper.querySelector('.fn-delete-field');


            deleteButton.addEventListener('click', (e) => {

                e.preventDefault();

                e.stopPropagation();


                const confirmDelete = confirm(
                    'Are you sure you want to delete this field?'
                );


                if (!confirmDelete) {

                    return;

                }


                this.state.removeField(
                    field.id
                );


                if (
                    this.state.selectedField &&
                    this.state.selectedField.id === field.id
                ) {

                    this.state.selectField(null);

                }

            });

            const handle = wrapper.querySelector('.fn-drag-handle');

            handle.addEventListener('dragstart', (e) => {

                e.stopPropagation();

                e.dataTransfer.clearData();

                e.dataTransfer.effectAllowed = 'move';

                e.dataTransfer.setData(
                    'drag-type',
                    'move'
                );

                e.dataTransfer.setData(
                    'field-index',
                    index
                );

                wrapper.classList.add('dragging');

            });

            handle.addEventListener('dragend', () => {

                wrapper.classList.remove('dragging');

            });

            this.el.appendChild(wrapper);

            // Drop zone after every field
            this.el.appendChild(
                this.createDropZone(index + 1)
            );

        });

    }

    createDropZone(index) {

        const zone = document.createElement('div');

        zone.className = 'formvexa-drop-zone';

        zone.dataset.index = index;

        zone.innerHTML = '<span>Drop Here</span>';

        zone.addEventListener(
            'dragover',
            (e) => {

                e.preventDefault();

                e.stopPropagation();

                zone.classList.add(
                    'active'
                );

            }
        );

        zone.addEventListener('dragleave', () => {

            zone.classList.remove('active');

        });

        zone.addEventListener('drop', (e) => {

            e.preventDefault();

            zone.classList.remove('active');

            const types = e.dataTransfer.types;

            if (!types.length) {

                return;

            }

            const dragType = e.dataTransfer.getData('drag-type');

            if (dragType === 'new') {

                const type = e.dataTransfer.getData(
                    'field-type'
                );

                if (!type) {

                    return;

                }

                const definition =
                    window.formvexaFieldRegistry.get(type);

                if (!definition) {

                    return;

                }

                const defaults =
                    definition.defaults || {};

                const field = {

                    id: 'field_' + Date.now(),

                    type,

                    ...defaults,

                    settings:
                        definition.settings || []

                };

                this.state.insertField(
                    index,
                    field
                );

                this.state.selectField(
                    field.id
                );

                return;

            }

            if (dragType === 'move') {

                const from = parseInt(
                    e.dataTransfer.getData('field-index')
                );

                if (from !== index) {

                    this.state.moveField(
                        from,
                        index
                    );

                }

            }

        });

        return zone;

    }

}