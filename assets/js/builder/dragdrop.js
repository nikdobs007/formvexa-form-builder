class FormNovaDragDrop {

    constructor(state, canvas) {

        this.state = state;
        this.canvas = canvas;

        this.fieldsPanel = document.getElementById('formnova-fields');
    }

    init() {

        this.renderSidebar();

        this.initSidebar();

        this.initCanvas();
    }

    renderSidebar() {

        if (!this.fieldsPanel) {
            return;
        }

        this.fieldsPanel.innerHTML = '';

        const fields = window.FormNovaFieldRegistry.all();

        Object.keys(fields).forEach(type => {

            const config = fields[type];

            const item = document.createElement('div');

            item.className = 'formnova-draggable';

            item.dataset.type = type;

            item.draggable = true;

            item.innerHTML = `
            <span>${config.defaults?.label || type}</span>
        `;

            this.fieldsPanel.appendChild(item);

        });

    }

    /**
     * Left sidebar fields
     */
    initSidebar() {

        if (!this.fieldsPanel) {
            return;
        }

        this.fieldsPanel
            .querySelectorAll('.formnova-draggable')
            .forEach(field => {

                field.draggable = true;

                field.addEventListener(
                    'dragstart',
                    (e) => {

                        e.stopPropagation();

                        e.dataTransfer.clearData();

                        e.dataTransfer.effectAllowed =
                            'copy';

                        e.dataTransfer.setData(
                            'drag-type',
                            'new'
                        );

                        e.dataTransfer.setData(
                            'field-type',
                            field.dataset.type
                        );

                    }
                );

            });

    }

    /**
     * Canvas events
     */
    initCanvas() {

        if (!this.canvas.el) {
            return;
        }

        this.canvas.el.addEventListener('dragover', (e) => {

            e.preventDefault();

        });

        this.canvas.el.addEventListener('drop', (e) => {

            // Drop handling is already managed
            // inside canvas drop zones.

            e.preventDefault();

        });

    }

}