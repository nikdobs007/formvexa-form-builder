class formvexaTabs {

    constructor() {

        this.tabs = document.querySelectorAll(
            '.fn-builder-tab'
        );

        this.panels = document.querySelectorAll(
            '.fn-tab-panel'
        );

    }

    init() {

        if (!this.tabs.length) {
            return;
        }

        this.tabs.forEach(tab => {

            tab.addEventListener(
                'click',
                (e) => {

                    e.preventDefault();

                    this.show(
                        tab.dataset.tab
                    );

                }
            );

        });

        /*
        |--------------------------------------------------------------------------
        | Open First Tab
        |--------------------------------------------------------------------------
        */

        const activeTab =
            document.querySelector(
                '.fn-builder-tab.active'
            );

        if (activeTab) {

            this.show(
                activeTab.dataset.tab
            );

        } else {

            this.show(
                this.tabs[0].dataset.tab
            );

        }

    }

    show(name) {

        this.tabs.forEach(tab => {

            if (tab.dataset.tab === name) {

                tab.classList.add('active');

            } else {

                tab.classList.remove('active');

            }

        });

        this.panels.forEach(panel => {

            if (panel.dataset.panel === name) {

                panel.classList.add('active');

                panel.style.display = 'block';

            } else {

                panel.classList.remove('active');

                panel.style.display = 'none';

            }

        });

    }

}