const LENGTH = 40;

export function sformat(value, format): string {
    if (typeof value !== 'string')
        value = String(value);
    if (!format) {
        format = 'truncate';
    }
    switch (format) {
        case 'truncate':
            if (value.length > LENGTH) {
                return value.slice(0, LENGTH) + '...'
            }
    }
    return value;
}