window.SVGParser = {
    parse(text) {
        const parser = new DOMParser();
        return parser.parseFromString(text, 'image/svg+xml');
    }
};
