import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['busy'];

    connect() {
        this.busyTarget.hidden = true;
        this.busyTarget.ariaBusy = false;
    }

    show() {
        this.busyTarget.hidden = false;
        this.busyTarget.ariaBusy = true;
    }
}
