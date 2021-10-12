export default class Util {
    static get thumbnailDataUri() {
        return 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGNsYXNzPSJ0aHVtYi1wbGFjZWhvbGRlciIgdmlld0JveD0iMCAwIDI0IDI0Ij48cGF0aCBkPSJNMTIgM1YxMy41NUMxMS40MSAxMy4yMSAxMC43MyAxMyAxMCAxM0M3Ljc5IDEzIDYgMTQuNzkgNiAxN1M3Ljc5IDIxIDEwIDIxIDE0IDE5LjIxIDE0IDE3VjdIMThWM0gxMloiLz48L3N2Zz4=';
    }

    static getRouteParam(param) {
        const expr = '\\/' + param + '\\/(.+?)(?=\\/|$|#|\\?|&)';
        const regex = new RegExp(expr,'g');
        const matches = regex.exec(window.location.toString());

        return matches && matches.length > 1
            ? matches[1]
            : null
        ;
    }

    static isTouch() {
        return window.matchMedia('(pointer: coarse)').matches;
    }

    static isSmartphone() {
        return window.matchMedia('(max-width: 700px)').matches;
    }

    static isInView(element) {
        const rect = element.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }

    static formatTime(seconds) {
        let date = new Date(null);
        date.setSeconds(seconds);

        if (date.getUTCHours()) {
            String(date.getUTCHours()).padStart(2, '0');

            return `${String(date.getUTCHours()).padStart(2, '0')}:${String(date.getUTCMinutes()).padStart(2, '0')}:${String(date.getUTCSeconds()).padStart(2, '0')}`;
        } else {
            return `${String(date.getUTCMinutes()).padStart(2, '0')}:${String(date.getUTCSeconds()).padStart(2, '0')}`;
        }
    }
}