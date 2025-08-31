window.Utils = {
    toNumber(value, def) {
        const n = parseFloat(value);
        return isNaN(n) ? def : n;
    }
};
