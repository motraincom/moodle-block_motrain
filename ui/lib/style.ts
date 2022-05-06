const prefix = 'block_motrain-';

export const genClassName = (names: string | string[]) => {
    if (typeof names === 'string') {
        names = [names];
    }
    return names.map((c) => `${prefix}${c}`).join(' ');
};
