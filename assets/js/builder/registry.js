class FormNovaFieldRegistry {

    constructor() {
        this.fields = {};
    }

    register(type, config) {
        this.fields[type] = config;
    }

    get(type) {
        return this.fields[type] || null;
    }

    all() {
        return this.fields;
    }
}

window.FormNovaFieldRegistry = new FormNovaFieldRegistry();